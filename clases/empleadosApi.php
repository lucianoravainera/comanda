<?php
require_once 'empleado.php';
require_once 'archivo.php';


class empleadosApi //extends empleado //implements IApiUsable
{
    public function TraerUno($request, $response, $args)
    {
            $id = $args['id'];
            $elempleado = empleado::TraerUnEmpleado($id);
            return $response->withJson($elempleado, 200);
    }

    
    public function traerTodos($request, $response, $args)
    {
            $todosLosempleados = empleado::TraerTodosLosempleados();
            return $response->withJson($todosLosempleados, 200);
    }


    public function traerFechasLogins($request, $response, $args)
    {
            $todosLosLogins = empleado::TraerTodosLosLogins();
            return $response->withJson($todosLosLogins, 200);
    }


    public function traerCantidadOperaciones($request, $response, $args)
    {
            $todosLosLogins = empleado::TraerCantOperaciones();
            return $response->withJson($todosLosLogins, 200);
    }


    public function CargarUno($request, $response, $args)
    {
        $ArrayDeParametros = $request->getParsedBody();
        $objDelaRespuesta= new stdclass();
        if (isset($ArrayDeParametros['nombre']) && isset($ArrayDeParametros['apellido']) && isset($ArrayDeParametros['email']) && isset($ArrayDeParametros['clave']) && isset($ArrayDeParametros['turno'])) {
            $nombre = $ArrayDeParametros['nombre'];
            $apellido = $ArrayDeParametros['apellido'];
            $email = $ArrayDeParametros['email'];
            $clave = password_hash($ArrayDeParametros['clave'], PASSWORD_BCRYPT);
            $turno = $ArrayDeParametros['turno'];

            $perfil = !empty($ArrayDeParametros['perfil']) ? $ArrayDeParametros['perfil'] : "User";
            $estado = !empty($ArrayDeParametros['estado']) ? $ArrayDeParametros['estado'] : "Activo";
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $fechaCreacion = date("Y/m/d H:i:s");

            $miEmpleado = new empleado();
            $miEmpleado->nombre=$nombre;
            $miEmpleado->apellido=$apellido;
            $miEmpleado->email=$email;
            $miEmpleado->clave=$clave;
            $miEmpleado->turno=$turno;
            $miEmpleado->perfil=$perfil;
            $miEmpleado->fechaCreacion=$fechaCreacion;
            $miEmpleado->estado=$estado;
            
            $ruta = $this->obtenerArchivo($request, $response, $email);
            if (!is_null($ruta)) {
                if ($ruta != false) {
                    $miEmpleado->foto = $ruta;
                }
            } else {
                $objDelaRespuesta->error = "Error al intentar guardar archivo. ";
                return $response->withJson($objDelaRespuesta, 409);
            }
        } else {
            $objDelaRespuesta->error = "Completar campos obligatorios";
            return $response->withJson($objDelaRespuesta, 409);
        }
        try{
        $miEmpleado->InsertarElempleadoParametros();
        $objDelaRespuesta->respuesta = "El empleado fue cargado con éxito.";
        return $response->withJson($objDelaRespuesta, 200);
    }catch(\Throwable $sh)
    {
        $objDelaRespuesta->respuesta = "Error: Datos inválidos o usuario ya registrado.";
    }
    return $response->withJson($objDelaRespuesta, 200);
    }

    public function obtenerArchivo($request, $response, $email)
    {
        $uploadedFiles = $request->getUploadedFiles();
        
        if (isset($uploadedFiles['archivo'])) {
            $uploadedFile = $uploadedFiles['archivo'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $ruta = archivo::moveUploadedFile($uploadedFile, $email, 'fotosEmpleados/');
                return $ruta;
            } else {
                return null;
            }
        } else {
            return false;
        }
    }



    public function ModificarUno($request, $response, $args)
    {
        $objDelaRespuesta= new stdclass();
        $empleadoAModificar = new empleado();
        $ArrayDeParametros = $request->getParsedBody();

        if (isset($ArrayDeParametros['id'])) {
            $empleadoAModificar = $empleadoAModificar->TraerUnEmpleado($ArrayDeParametros['id']);
            if ($empleadoAModificar != null) {
                if (isset($ArrayDeParametros['nombre'])) {
                    $empleadoAModificar->nombre = $ArrayDeParametros['nombre'];
                }
                if (isset($ArrayDeParametros['apellido'])) {
                    $empleadoAModificar->apellido = $ArrayDeParametros['apellido'];
                }
                if (isset($ArrayDeParametros['email'])) {
                    $empleadoAModificar->email = $ArrayDeParametros['email'];
                }
                if (!empty($ArrayDeParametros['clave'])) {
                    $empleadoAModificar->clave = password_hash($ArrayDeParametros['clave'], PASSWORD_BCRYPT);
                }
                if (isset($ArrayDeParametros['turno'])) {
                    $empleadoAModificar->turno = $ArrayDeParametros['turno'];
                }
                if (isset($ArrayDeParametros['perfil'])) {
                    $empleadoAModificar->perfil = $ArrayDeParametros['perfil'];
                }
                if (isset($ArrayDeParametros['estado'])) {
                    $empleadoAModificar->estado = $ArrayDeParametros['estado'];
                }

                try {
                    $resultado = $empleadoAModificar->ModificarEmpleadoParametros();
                    $objDelaRespuesta->resultado=$resultado;
                    $objDelaRespuesta->msj= "Empleado modificado con exito";
                } catch (PDOException $e) {
                    $objDelaRespuesta->error = "Empleado no fue modificado. Error: " . $e->getMessage();
                    return $response->withJson($objDelaRespuesta, 409);
                }
            } 
            else 
            {
                $objDelaRespuesta->error= "El id ingresado no existe.";
                return $response->withJson($objDelaRespuesta, 409);
            }
        } 
        else 
        {
            $objDelaRespuesta->error= "Debe ingresar un id.";
            return $response->withJson($objDelaRespuesta, 409);
        }

            return $response->withJson($objDelaRespuesta, 200);
    }

    public function ModificarFoto($request, $response, $args)
    {
        if (!($putData = fopen("php://input", "r"))) {
            throw new \Exception("Can't get PUT data.");
        }

        $path = 'fotosEmpleados/';
    //$path = $this->fileHandler->createImagePath($recipeID, $fileName);
        $destination = fopen($path, 'w');
        stream_copy_to_stream($putData, $destination);
        $imageType = exif_imagetype($path);

        if (!($imageType == 3 || $imageType == 2 ||$imageType == 1)) { //png or jpeg or gif?
            unlink($path);
            echo $imageType;
            return $response->withStatus(400);
        }
        fclose($putData);
        fclose($destination);
        return $response->withStatus(201);
    }




    public function BorrarUno($request, $response, $args)
    {
        $objDelaRespuesta= new stdclass();
        $ArrayDeParametros = $request->getParsedBody(); //form-urlencoded

        if (isset($ArrayDeParametros['id'])) 
        {
            $id=$ArrayDeParametros['id'];
            $empleado= new empleado();
            $empleado->id=$id;
            $cantidadDeBorrados=$empleado->BorrarEmpleado();

            $objDelaRespuesta= new stdclass();
            if ($cantidadDeBorrados>0) {
                $objDelaRespuesta->resultado="El empleado con id: ".$id." fue eliminado exitosamente";
            } else {
                $objDelaRespuesta->resultado="El empleado con id: ".$id." no existe.";
                return $response->withJson($objDelaRespuesta, 409);
            }
        }
        else 
        {
            $objDelaRespuesta->error = "Debe ingresar un id.";
        }

        return $response->withJson($objDelaRespuesta, 200);
    }
}
