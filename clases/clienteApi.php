<?php
require_once 'pedido.php';
require_once 'cliente.php';

class clienteApi extends Cliente
{
    public function traerEstadoPedido($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();
        $ArrayDeParametros = $request->getParsedBody();

        if (isset($ArrayDeParametros['codigo_pedido']) && isset($ArrayDeParametros['codigo_mesa'])) 
        {
            $codigo_pedido = $ArrayDeParametros['codigo_pedido'];
            $codigo_mesa = $ArrayDeParametros['codigo_mesa'];

            $pedidoBuscado = Pedido::GetComboPedidoYMesa($codigo_pedido,$codigo_mesa);

            if($pedidoBuscado)
            {
                $objDelaRespuesta->informacionPedido = $pedidoBuscado;
            }
            else
            {
                $objDelaRespuesta->error = "No existe un pedido con el id ingresado asociado a una mesa con el id ingresado.";
            }

        }
        else 
        {
            $objDelaRespuesta->error = "Complete los campos obligatorios";
        }

        return $response->withJson($objDelaRespuesta, 200);

    }

    public function completarEncuesta($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();
        $ArrayDeParametros = $request->getParsedBody();

        if (isset($ArrayDeParametros['mesa']) && isset($ArrayDeParametros['restaurante']) && isset($ArrayDeParametros['mozo']) && isset($ArrayDeParametros['cocinero']) && isset($ArrayDeParametros['bartender']) && isset($ArrayDeParametros['cervecero']) && isset($ArrayDeParametros['precio_calidad']))
        {
            $mesa = $ArrayDeParametros['mesa'];
            $restaurante = $ArrayDeParametros['restaurante'];
            $mozo = $ArrayDeParametros['mozo'];
            $cocinero = $ArrayDeParametros['cocinero'];
            $bartender = $ArrayDeParametros['bartender'];
            $cervecero = $ArrayDeParametros['cervecero'];
            $precio_calidad = $ArrayDeParametros['precio_calidad'];
            $descripcion = $ArrayDeParametros['descripcion'];
            $pedido_id = $ArrayDeParametros['pedido_id'];

            $array_puntuacion = array($mesa, $restaurante, $mozo, $cocinero, $bartender, $cervecero, $precio_calidad);

            foreach($array_puntuacion as $element) {
                if(is_numeric($element)) 
                {
                    if($element < 1 || $element > 10)
                    {
                        $objDelaRespuesta->errorRango = "Las puntuaciones deben estar en el rango de 1 a 10.";
                        return $response->withJson($objDelaRespuesta, 409);
                    }
                } 
                else 
                {
                    $objDelaRespuesta->errorNumerico = "Las puntuaciones deben ser numéricas.";
                    return $response->withJson($objDelaRespuesta, 409);
                }
            }

            Cliente::InsertarEncuesta($mesa, $restaurante, $mozo, $cocinero, $bartender, $cervecero, $precio_calidad, $descripcion, $pedido_id);
            $objDelaRespuesta->mensaje = "Encuesta registrada exitosamente";
        }
        else 
        {
            $objDelaRespuesta->error = "Complete los campos obligatorios";
        }

        return $response->withJson($objDelaRespuesta, 200);
    }

    public function CargarUno($request, $response, $args)
    {
        $ArrayDeParametros = $request->getParsedBody();
        $objDelaRespuesta= new stdclass();
        if (isset($ArrayDeParametros['nombre']) && isset($ArrayDeParametros['apellido']) && isset($ArrayDeParametros['email']) && isset($ArrayDeParametros['clave'])) {
            $nombre = $ArrayDeParametros['nombre'];
            $apellido = $ArrayDeParametros['apellido'];
            $email = $ArrayDeParametros['email'];
            $clave = password_hash($ArrayDeParametros['clave'], PASSWORD_BCRYPT);

            $estado = !empty($ArrayDeParametros['estado']) ? $ArrayDeParametros['estado'] : "Activo";
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $fechaCreacion = date("d/m/Y H:i");

            $miCliente = new Cliente();
            $miCliente->nombre=$nombre;
            $miCliente->apellido=$apellido;
            $miCliente->email=$email;
            $miCliente->clave=$clave;
            $miCliente->perfil='Cliente';
            $miCliente->fechaCreacion=$fechaCreacion;
            $miCliente->estado=$estado;
            
            $ruta = $this->obtenerArchivo($request, $response, $email);
            if (!is_null($ruta)) {
                if ($ruta != false) {
                    $miCliente->foto = $ruta;
                }
            } else {
                $objDelaRespuesta->error = "Error al intentar guardar archivo. ";
                return $response->withJson($objDelaRespuesta, 409);
            }
        } else {
            $objDelaRespuesta->error = "Completar campos obligatorios";
            return $response->withJson($objDelaRespuesta, 409);
        }
        
        $miCliente->InsertarElClienteParametros();
        $objDelaRespuesta->respuesta = "El cliente fue cargado con éxito.";
        return $response->withJson($objDelaRespuesta, 200);
    }

    public function obtenerArchivo($request, $response, $email)
    {
        $uploadedFiles = $request->getUploadedFiles();
        
        if (isset($uploadedFiles['archivo'])) {
            $uploadedFile = $uploadedFiles['archivo'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $ruta = archivo::moveUploadedFile($uploadedFile, $email, 'fotosClientes/');
                return $ruta;
            } else {
                return null;
            }
        } else {
            return false;
        }
    }
}
