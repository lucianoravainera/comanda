<?php
    require_once 'archivo.php';
    //require_once 'IApiUsable.php';

class archivoApi extends archivo //implements IApiUsable
{
    // public function TraerUno($request, $response, $args) {
    //  	$id=$args['id'];
    // 	$elUsuario=usuario::TraerUnUsuario($id);
    //      $newResponse = $response->withJson($elUsuario, 200);
    // 	return $newResponse;
    // }
    //  public function traerTodos($request, $response, $args) {
    //   	$todosLosUsuarios=usuario::TraerTodosLosUsuarios();
    //      $response = $response->withJson($todosLosUsuarios, 200);
    // 	return $response;
    // }

    public function cargarArchivo(Request $request, Response $response)
    {
        $arr = $request->getParsedBody();
        $titulo = $arr['titulo'];
        $container = $app->getContainer();
        $container['upload_directory'] = __DIR__ . '/uploads';
        $directory = $this->get('upload_directory');
        
        $uploadedFiles = $request->getUploadedFiles();
        
        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['archivo'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = archivo::moveUploadedFile($directory, $uploadedFile, $titulo);
            $response->write('uploaded ' . $filename . '<br/>');
        }
    }

    //REPORTES ESTACIONAMIENTO

    public static function exportarExcelEstacionados()
    {
        $array = estacionamiento::TraerDatosEstacionamiento();
        Archivo::exportarExcel($array, "registroEstacionados");
    }

    public static function exportarPDFEstacionados()
    {
        $array = estacionamiento::TraerDatosEstacionamiento();
        Archivo::exportarPDF($array, "registroEstacionados");
    }


    public static function exportarExcelMasUsada()
    {
        $array = estacionamiento::TraerCocheraMasUtilizada();
        Archivo::exportarExcel($array, "cocherasMasUsadas");
    }

    public static function exportarPDFMasUsada()
    {
        $array = estacionamiento::TraerCocheraMasUtilizada();
        Archivo::exportarPDF($array, "CocheraMasUsada");
    }

    public static function exportarExcelMenosUsada()
    {
        $array = estacionamiento::TraerCocheraMenosUtilizada();
        Archivo::exportarExcel($array, "CocheraMenosUsada");
    }

    public static function exportarPDFMenosUsada()
    {
        $array = estacionamiento::TraerCocheraMenosUtilizada();
        Archivo::exportarPDF($array, "CocheraMenosUsada");
    }

    public static function exportarExcelNoUsadas()
    {
        $array = estacionamiento::TraerCocherasNoUtilizadas();
        Archivo::exportarExcel($array, "cocherasNoUsadas");
    }

    public static function exportarPDFNoUsadas()
    {
        $array = estacionamiento::TraerCocherasNoUtilizadas();
        Archivo::exportarPDF($array, "CocheraNoUsadas");
    }


    //REPORTES EMPLEADOS

    public static function exportarExcelCantOp()
    {
        $array = empleado::TraerCantOperaciones();
        Archivo::exportarExcel($array, "cantidadOperaciones");
    }

    public static function exportarPDFCantOp()
    {
        $array = empleado::TraerCantOperaciones();
        Archivo::exportarPDF($array, "cantidadOperaciones");
    }


    public static function exportarExcelFechasLogins()
    {
        $array = empleado::TraerTodosLosLogins();
        Archivo::exportarExcel($array, "FechasLogins");
    }

    public static function exportarPDFFechasLogins()
    {
        $array = empleado::TraerTodosLosLogins();
        Archivo::exportarPDF($array, "FechasLogins");
    }
}
