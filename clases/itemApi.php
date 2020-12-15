<?php
require_once 'item.php';
//require_once 'archivo.php';
//require_once 'empleado.php';
//require_once 'IApiUsable.php';

class itemApi extends item
{
    public function CargarUno($request, $response, $args)
    {
        $ArrayDeParametros = $request->getParsedBody();
        $objDelaRespuesta= new stdclass();
        if (isset($ArrayDeParametros['nombre']) && isset($ArrayDeParametros['precio']) && isset($ArrayDeParametros['sector']) && isset($ArrayDeParametros['estado']) && isset($ArrayDeParametros['tiempo_estimado_preparacion'])) {
            $nombre = $ArrayDeParametros['nombre'];
            $precio = $ArrayDeParametros['precio'];
            $sector = $ArrayDeParametros['sector'];
            $estado = $ArrayDeParametros['estado'];
            $tiempo_estimado_preparacion = $ArrayDeParametros['tiempo_estimado_preparacion'];

            $miItem = new item();
            $miItem->nombre=$nombre;
            $miItem->precio=$precio;
            $miItem->sector=$sector;
            $miItem->estado=$estado;
            $miItem->tiempo_estimado_preparacion = $tiempo_estimado_preparacion;

            $ruta = $this->obtenerArchivo($request, $response, $nombre);
            if (!is_null($ruta)) {
                if ($ruta != false) {
                    $miItem->foto = $ruta;
                }
            } else {
                $objDelaRespuesta->error = "Error al intentar guardar archivo. ";
                return $response->withJson($objDelaRespuesta, 409);
            }

        } else {
            $objDelaRespuesta->error = "Completar campos obligatorios";
            return $response->withJson($objDelaRespuesta, 409);
        }
        
        $miItem->InsertarElItemParametros();
        $objDelaRespuesta->respuesta = "El item fué cargado con éxito.";
        return $response->withJson($objDelaRespuesta, 200);
    }

    public function obtenerArchivo($request, $response, $nombre)
    {
        $uploadedFiles = $request->getUploadedFiles();
        
        if (isset($uploadedFiles['archivo'])) {
            $uploadedFile = $uploadedFiles['archivo'];
            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $ruta = archivo::moveUploadedFile($uploadedFile, $nombre, 'fotosItems/');
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
        $objDelaRespuesta = new stdclass();
        $itemAModificar = new item();
        $ArrayDeParametros = $request->getParsedBody();

        if (isset($ArrayDeParametros['id'])) {
            $itemAModificar = $itemAModificar->TraerUnItem($ArrayDeParametros['id']);
            if ($itemAModificar != null) {
                if (isset($ArrayDeParametros['nombre'])) {
                    $itemAModificar->nombre = $ArrayDeParametros['nombre'];
                }
                if (isset($ArrayDeParametros['precio'])) {
                    $itemAModificar->precio = $ArrayDeParametros['precio'];
                }
                if (isset($ArrayDeParametros['sector'])) {
                    $itemAModificar->sector = $ArrayDeParametros['sector'];
                }
                if (isset($ArrayDeParametros['estado'])) {
                    $itemAModificar->estado = $ArrayDeParametros['estado'];
                }
                if (isset($ArrayDeParametros['tiempo_estimado_preparacion'])) {
                    $itemAModificar->tiempo_estimado_preparacion = $ArrayDeParametros['tiempo_estimado_preparacion'];
                }
                
                $ruta = $this->obtenerArchivo($request, $response, $ArrayDeParametros['nombre']);
                if (!is_null($ruta)) {
                    if ($ruta != false) {
                        $itemAModificar->foto = $ruta;
                    }
                } else {
                    $objDelaRespuesta->error = "Error al intentar guardar archivo. ";
                    return $response->withJson($objDelaRespuesta, 409);
                }

                try {
                    $resultado = $itemAModificar->ModificarItemParametros();
                    $objDelaRespuesta->resultado=$resultado;
                    $objDelaRespuesta->msj= "Item modificado con exito";
                } catch (PDOException $e) {
                    $objDelaRespuesta->error = "Item no fue modificado. Error: " . $e->getMessage();
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

    public function traerTodos($request, $response, $args)
    {
            $todosLosItems = item::TraerTodosLosItems();
            return $response->withJson($todosLosItems, 200);
    }

    public function traerTodosActivos($request, $response, $args)
    {
            $todosLosItems = item::TraerTodosLosItemsActivos();
            return $response->withJson($todosLosItems, 200);
    }

    public function BorrarUno($request, $response, $args)
    {
        $objDelaRespuesta= new stdclass();
        // $ArrayDeParametros = $request->getBody(); //form-urlencoded

        if (isset($args['id'])) 
        {
            // $id=$ArrayDeParametros['id'];
            $id=$args['id'];
            $item= new item();
            $item->id=$id;
            $cantidadDeBorrados=$item->BorrarItem();

            $objDelaRespuesta= new stdclass();
            if ($cantidadDeBorrados>0) {
                $objDelaRespuesta->resultado="El item con id: ".$id." fue eliminado exitosamente";
            } else {
                $objDelaRespuesta->resultado="El item con id: ".$id." no existe.";
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