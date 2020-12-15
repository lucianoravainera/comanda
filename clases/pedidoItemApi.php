<?php
require_once 'pedidoItem.php';
require_once 'MWparaAutentificar.php';
require_once 'pedido.php';
//require_once 'archivo.php';
//require_once 'empleado.php';
//require_once 'IApiUsable.php';

class pedidoItemApi extends PedidoItem
{
    public function traerMisPedidos($request, $response, $args)
    {
        $variablePedido = new PedidoItem();
        $sectorYUsuario = $this->retornarSectorYUsuario($request);
        $variablePedido->sector = $sectorYUsuario->sector;

        $objDelaRespuesta = new stdclass();

        $objDelaRespuesta->mensaje = 'Los pedidos para su rol de: [' . $sectorYUsuario->tipoUsuarioLogueado . '] con estado (Derivado) o (En Preparación) son los siguientes: ';
        
        $pedidosSector = $variablePedido->TraerMisPedidosParametros();

        $objDelaRespuesta->pedidos = $pedidosSector;

        // return $response->withJson($pedidosSector, 200);
        return $response->withJson($objDelaRespuesta, 200);
    }

    public function tomarUnPedido($request, $response, $args)
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
            $variablePedido->empleado_preparador_id = $this->retornarEmpleadoId($request);

            $resultadoPedidoIndividual = $variablePedido->TraerMiPedidoParametros("derivado");

            if(!$resultadoPedidoIndividual)
            {
                $objDelaRespuesta->mensaje = "Usted eligió un item del pedido que no existe, ya se está preparando o no corresponde a su sector.";
            }
            else
            {
                if($variablePedido->TomarMiPedidoParametros())
                {
                    $objDelaRespuesta->mensaje = "El item del pedido se marcó como [en preparación].";
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
            $variablePedido->empleado_preparador_id = $this->retornarEmpleadoId($request);

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

                        if(Pedido::AlistarPedido($variablePedido->id_pedido, date("d/m/Y H:i")))
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

    private function retornarSectorYUsuario($request)
    {
        $sectorYTipoUsuario = new stdclass();

        $arrayConToken = $request->getHeader('token');
        $token=$arrayConToken[0];
        $payload=AutentificadorJWT::ObtenerData($token);
        $tipoUsuarioLogueado = strtolower($payload->perfil);
        $sectorYTipoUsuario->tipoUsuarioLogueado = $tipoUsuarioLogueado;

        switch ($tipoUsuarioLogueado) {
            case 'bartender':
            $sectorYTipoUsuario->sector = 'bar';    
                break;

            case 'cervecero':
            $sectorYTipoUsuario->sector = 'cerveceria';
                break;

            case 'cocinero':
            $sectorYTipoUsuario->sector = 'cocina';
                break;
            
            default:
            $sectorYTipoUsuario->sector = '';
                break;
        }

        return $sectorYTipoUsuario;
    }

    private function retornarEmpleadoId($request)
    {
        $empleadoId = new stdclass();

        $arrayConToken = $request->getHeader('token');
        $token=$arrayConToken[0];
        $payload=AutentificadorJWT::ObtenerData($token);
        $empleadoId = $payload->id;

        return $empleadoId;
    }

}