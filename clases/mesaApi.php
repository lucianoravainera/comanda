<?php
require_once 'mesa.php';
//require_once 'archivo.php';
//require_once 'empleado.php';
//require_once 'IApiUsable.php';

class mesaApi extends mesa
{
    // public function CargarUno($request, $response, $args)
    // {
    //     $ArrayDeParametros = $request->getParsedBody();
    //     $objDelaRespuesta= new stdclass();
    //     if (isset($ArrayDeParametros['nombre']) && isset($ArrayDeParametros['precio']) && isset($ArrayDeParametros['sector']) && isset($ArrayDeParametros['estado']) && isset($ArrayDeParametros['tiempo_estimado_preparacion'])) {
    //         $nombre = $ArrayDeParametros['nombre'];
    //         $precio = $ArrayDeParametros['precio'];
    //         $sector = $ArrayDeParametros['sector'];
    //         $estado = $ArrayDeParametros['estado'];
    //         $tiempo_estimado_preparacion = $ArrayDeParametros['tiempo_estimado_preparacion'];

    //         $miItem = new item();
    //         $miItem->nombre=$nombre;
    //         $miItem->precio=$precio;
    //         $miItem->sector=$sector;
    //         $miItem->estado=$estado;
    //         $miItem->tiempo_estimado_preparacion = $tiempo_estimado_preparacion;

    //     } else {
    //         $objDelaRespuesta->error = "Completar campos obligatorios";
    //         return $response->withJson($objDelaRespuesta, 409);
    //     }
        
    //     $miItem->InsertarElItemParametros();
    //     $objDelaRespuesta->respuesta = "El item fué cargado con éxito.";
    //     return $response->withJson($objDelaRespuesta, 200);
    // }

    public function traerTodas($request, $response, $args)
    {
            $todasLasMesas = mesa::TraerTodasLasMesas();
            return $response->withJson($todasLasMesas, 200);
    }

    // public function traerTodosActivos($request, $response, $args)
    // {
    //         $todosLosItems = item::TraerTodosLosItemsActivos();
    //         return $response->withJson($todosLosItems, 200);
    // }

    public function cambiarEstadoMozo()
    {
        $objDelaRespuesta = new stdclass();
        $ArrayDeParametros = $request->getParsedBody();

        if (isset($ArrayDeParametros['mesa_id']) && isset($ArrayDeParametros['id_pedido_item']))
        {

        }
        
    }

    public function terminarUnPedido($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();
        $ArrayDeParametros = $request->getParsedBody();

        $variablePedido = new PedidoItem();

        if (isset($ArrayDeParametros['id_pedido_item']))
        {
            $id_pedido_item = $ArrayDeParametros['id_pedido_item'];

            $sectorYUsuario = $this->retornarSectorYUsuario($request);
            $variablePedido->sector = $sectorYUsuario->sector;
            $variablePedido->id = $id_pedido_item;

            $resultadoPedidoIndividual = $variablePedido->TraerMiPedidoParametros("en preparacion");

            if(!$resultadoPedidoIndividual)
            {
                $objDelaRespuesta->mensaje = "Usted eligió un item del pedido que no existe, ya se preparó o no corresponde a su sector.";
            }
            else
            {
                if($variablePedido->CerrarMiPedidoParametros())
                {
                    $objDelaRespuesta->mensaje = "El item del pedido se marcó como [listo para servir].";

                    $valorTransformadoAObjeto = (object) $resultadoPedidoIndividual[0];

                    $variablePedido->id_pedido = $valorTransformadoAObjeto->id_pedido;

                    $pedidosRestantes = $variablePedido->DetectarPedidoGeneralCompletadoParametros();
                    
                    if(!$pedidosRestantes)
                    {
                        $objIdComanda = Pedido::GetIdComanda($variablePedido->id_pedido);

                        if(Pedido::AlistarPedido($variablePedido->id_pedido))
                        {
                            $objDelaRespuesta->informacion = 'Con éste último item del pedido listo para servir, se completaron todos los items del pedido con código: [' . $objIdComanda->id_comanda . '], por lo cual el pedido se encuentra listo para ser servido.';
                        }
                        else
                        {
                            $objDelaRespuesta->mensaje = "Ocurrió un error en el sistema al cerrar el pedido general.";
                        }
                        
                    }
                }
                else
                {
                    $objDelaRespuesta->mensaje = "Ocurrió un error en el sistema.";
                }
            }
        }
        else {
            $objDelaRespuesta->error = "Complete los campos obligatorios";
        }

        return $response->withJson($objDelaRespuesta, 200);
    }

    public function actualizarEstadoMesa($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();
        $ArrayDeParametros = $request->getParsedBody();

        $estado = $ArrayDeParametros['estado'];
        $id_mesa = $ArrayDeParametros['mesa_id'];
        if($id_mesa != null && ($estado == "con cliente esperando pedido" || $estado == "con clientes comiendo" || $estado == "con clientes pagando" || $estado == "cerrada") )
        {
        mesa::ActualizarMesa($estado,$id_mesa);

        $objDelaRespuesta->mensaje = "El estado de la mesa se ha cambiado correctamente.";
        }else{
            $objDelaRespuesta->mensaje = "El estado debe ser: con cliente esperando pedido, con clientes comiendo, con clientes pagando o cerrada";
        }
        return $response->withJson($objDelaRespuesta, 200);
    }

    public function cerrarMesa($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();
        $ArrayDeParametros = $request->getParsedBody();

        $estado = $ArrayDeParametros['estado'];
        $id_mesa = $ArrayDeParametros['mesa_id'];

        mesa::cerrarMesaCliente($estado,$id_mesa);

        $objDelaRespuesta->mensaje = "El estado de la mesa se ha cambiado correctamente.";

        return $response->withJson($objDelaRespuesta, 200);
    }
}