<?php
require_once 'pedido.php';
require_once 'archivo.php';
require_once 'empleado.php';
require_once 'item.php';
require_once 'pedidoItem.php';
require_once 'mesa.php';
require_once 'cliente.php';
//require_once 'IApiUsable.php';

class pedidoApi extends Pedido
{
    public function CargarUno($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();
        $ArrayDeParametros = $request->getParsedBody();

        //print_r($ArrayDeParametros);

        // exit; // Para detener la ejecución del script.

        if (isset($ArrayDeParametros['id_mesa']) && isset($ArrayDeParametros['arrayItems']) && isset($ArrayDeParametros['id_cliente'])) {

            $id_mesa = $ArrayDeParametros['id_mesa'];
            //$itemsSolicitados = json_decode($ArrayDeParametros['arrayItems']);
            $itemsSolicitados = $ArrayDeParametros['arrayItems'];
            $id_cliente = $ArrayDeParametros['id_cliente'];
            $itemsDeLaBase = item::TraerTodosLosItemsActivos();

            $mesaBuscada = mesa::TraerMesaPorId($id_mesa);

            if($mesaBuscada)
            {

                if (!is_null($itemsDeLaBase)) {

                    $arrayAuxiliar1 = array();
                    $arrayAuxiliar2 = array();
                    $itemConMayorTiempoPreparacion = 0;
    
                    foreach ($itemsSolicitados as $valor) //
                    {
                        $valorTransformadoAObjeto = (object) $valor;
                        //print_r($valorTransformadoAObjeto);
                        // array_push ( $arrayAuxiliar1 , $valor->nombre );
                        array_push ( $arrayAuxiliar1 , $valorTransformadoAObjeto->nombre );
                    }
    
                    // exit;
    
                    foreach ($itemsDeLaBase as $valor) 
                    {
                        array_push ( $arrayAuxiliar2 , $valor->nombre );
                    }
                    
                    if(!array_diff($arrayAuxiliar1, $arrayAuxiliar2))
                    {
                        // foreach para calcular el item que tiene el mayor tiempo de preparacion esimado,
                        // a fin de ser utilizado dicho tiempo para calcular el tiempo del pedido general.
                        foreach ($itemsSolicitados as $valorSolicitado)//
                        {
                            $valorSolicitadoTransformadoAObjeto = (object) $valorSolicitado;
    
                            foreach ($itemsDeLaBase as $valorExistente)
                            {
                                if($valorSolicitadoTransformadoAObjeto->nombre == $valorExistente->nombre)
                                {
                                    if($valorExistente->tiempo_estimado_preparacion > $itemConMayorTiempoPreparacion)
                                    {
                                        $itemConMayorTiempoPreparacion = $valorExistente->tiempo_estimado_preparacion;
                                    }   
                                }
                            }
                        }

                        $mipedido = new Pedido();
                        $mipedido->id_mesa = $id_mesa;
                        $mipedido->id_comanda = $this->GenerarCodigoRandom();
                        $mipedido->estado = "en preparacion";
                        $mipedido->fecha_hora_inicio = date("d/m/Y H:i");
                        // El tiempo estimado del pedido general se obtiene revisando el item que tiene el mayor
                        // tiempo de preparación dentro del pedido y sumándole 15 minutos.
                        $mipedido->tiempo_estimado_preparacion_minutos = ($itemConMayorTiempoPreparacion + 15);
                        $mipedido->id_cliente = $id_cliente;
                        $ultimoId = $mipedido->InsertarElPedidoParametros();
                        
                        // $miPedidoItem->
                        foreach ($itemsSolicitados as $valorSolicitado)//
                        {
                            $valorSolicitadoTransformadoAObjeto = (object) $valorSolicitado;
    
                            foreach ($itemsDeLaBase as $valorExistente)
                            {
                                if($valorSolicitadoTransformadoAObjeto->nombre == $valorExistente->nombre)
                                {
                                    $miPedidoItem = new PedidoItem();
                                    $miPedidoItem->id_pedido = $ultimoId;
                                    $miPedidoItem->id_item = $valorExistente->id;
                                    $miPedidoItem->cantidad = $valorSolicitadoTransformadoAObjeto->cantidad;
                                    $miPedidoItem->estado = 'derivado';
                                    $miPedidoItem->InsertarElPedidoItemParametros();
                                    mesa::ActualizarMesa('esperando pedido', $id_mesa);
                                }
                            }
                        }
    
                        $objDelaRespuesta->mensaje = "El pedido se ha registrado con éxito.";
                        $objDelaRespuesta->codigoPedido = $mipedido->id_comanda;
                        $objDelaRespuesta->codigoMesa = $mesaBuscada[0]->codigo_mesa;
                    }
                    else
                    {
                        $objDelaRespuesta->error = "Solicitó items que no existen, ellos son: ";
                        $objDelaRespuesta->inexistentes = array_diff($arrayAuxiliar1, $arrayAuxiliar2);
                    }
                } 
                else 
                {
                    $objDelaRespuesta->error = "No hay items disponibles";
                }
            }
            else
            {
                $objDelaRespuesta->error = 'La mesa solicitada no existe.';
            }

            
        } else {
            $objDelaRespuesta->error = "Complete los campos obligatorios";
        }

        return $response->withJson($objDelaRespuesta, 200);
    }

    function GenerarCodigoRandom($length = 5) { 
        return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length); 
    }

    public function traerTodos($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $pedidos = Pedido::TraerTodosLosPedidos();

        foreach ($pedidos as $pedido)
        {
            $pedido->pedidoItems = PedidoItem::TraerPedidosItemsDeUnPedido($pedido->id);
        }

        return $response->withJson($pedidos, 200);
    }

    public function traerClientes($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $clientes = Cliente::TraerClientes();

        return $response->withJson($clientes, 200);
    }

    public function AsignarMesa($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();
        $ArrayDeParametros = $request->getParsedBody();

        if (isset($ArrayDeParametros['id_mesa']) && isset($ArrayDeParametros['id_cliente']))
        {
            $id_mesa = $ArrayDeParametros['id_mesa'];
            $id_cliente = $ArrayDeParametros['id_cliente'];

            $mesaDeseada = mesa::TraerMesaPorId($id_mesa);

            if($mesaDeseada[0]->id_cliente != null)
            {
                $objDelaRespuesta->error = "La mesa está ocupada en este momento.";
                return $response->withJson($objDelaRespuesta, 409);
            }

            mesa::AsignarMesa($id_mesa,$id_cliente);

            $objDelaRespuesta->mensaje = "Mesa asignada exitosamente.";
        }
        else {
            $objDelaRespuesta->error = "Complete los campos obligatorios";
            return $response->withJson($objDelaRespuesta, 400);
        }

        return $response->withJson($objDelaRespuesta, 200);
    }

    public function traerPedidosCliente($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $id_cliente = $this->retornarClienteId($request);

        $pedidos = Pedido::TraerPedidosPorCliente($id_cliente);

        foreach ($pedidos as $pedido)
        {
            $pedido->pedidoItems = PedidoItem::TraerPedidosItemsDeUnPedido($pedido->id);
        }

        return $response->withJson($pedidos, 200);
    }

    private function retornarClienteId($request)
    {
        $clienteId = new stdclass();

        $arrayConToken = $request->getHeader('token');
        $token=$arrayConToken[0];
        $payload=AutentificadorJWT::ObtenerData($token);
        $clienteId = $payload->id;

        return $clienteId;
    }
}
