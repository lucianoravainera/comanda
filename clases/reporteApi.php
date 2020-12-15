<?php
require_once 'reporte.php';
require_once 'empleado.php';
date_default_timezone_set('America/Argentina/Buenos_Aires');

class reporteApi extends reporte
{
    public function traerDiasHorarios($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $objDelaRespuesta->resultado = reporte::GetDiasHorariosEmpleados();

        return $response->withJson($objDelaRespuesta, 200);
    }

    public function traerOperacionesPorSector($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $objDelaRespuesta->resultado = reporte::GetOperacionesPorSector();

        return $response->withJson($objDelaRespuesta, 200);
    }

    public function traerOperacionesPorSectorListadoEmpleados($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $objDelaRespuesta->resultado = reporte::GetOperacionesPorSectorYEmpleado();

        return $response->withJson($objDelaRespuesta, 200);
    }

    public function traerOperacionesPorEmpleado($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $ArrayDeParametros = $request->getParsedBody();

        if(isset($ArrayDeParametros['id_empleado']))
        {
            $id_empleado_solicitado = $ArrayDeParametros['id_empleado'];

            $empleadoBuscado = empleado::TraerUnEmpleado($id_empleado_solicitado);

            if($empleadoBuscado)
            {
                $respuestaBusqueda = reporte::GetOperacionesPorEmpleado($id_empleado_solicitado);

                if($respuestaBusqueda)
                {
                    $objDelaRespuesta->resultado = $respuestaBusqueda;
                }
                else
                {
                    $objDelaRespuesta->error = 'El empleado con el ID indicado, no posee operaciones registradas.';    
                }
            }
            else
            {
                $objDelaRespuesta->error = 'El empleado con el ID indicado, no existe.';
            }
        }
        else
        {
            $objDelaRespuesta->error = 'Complete los campos obligatorios.';
        }

        return $response->withJson($objDelaRespuesta, 200);
    }
 
    public function traerMasVendido($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $objDelaRespuesta->resultado = reporte::GetMasVendido();

        return $response->withJson($objDelaRespuesta, 200);
    }

    public function traerMenosVendido($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $objDelaRespuesta->resultado = reporte::GetMenosVendido();

        return $response->withJson($objDelaRespuesta, 200);
    }

    public function traerPedidosRetrasados($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $pedidosTerminados = reporte::GetPedidosTerminados();

        $arrayAuxiliar = array();

        foreach($pedidosTerminados as $element) {

            setlocale(LC_ALL,"es_ES");
            $datetime1 = DateTime::createFromFormat("d/m/Y H:i", $element->fecha_hora_inicio);
            $datetime2 = DateTime::createFromFormat("d/m/Y H:i", $element->fecha_hora_fin);
            
            $interval = $datetime1->diff($datetime2);

            // Obteniendo la diferencia en minutos.
            $minutos = $interval->days * 24 * 60;
            $minutos += $interval->h * 60;
            $minutos += $interval->i;

            // echo "minutos: $minutos";
 
            if ($minutos > $element->tiempo_estimado_preparacion_minutos) {

                $diferencia_en_minutos = $minutos - $element->tiempo_estimado_preparacion_minutos;

                $pedidoDemorado = new stdclass();
                $pedidoDemorado->comanda = $element->id_comanda;
                $pedidoDemorado->demora = "El pedido se estimó en $element->tiempo_estimado_preparacion_minutos minutos pero se finalizó en: $minutos minutos, derivando en una demora de: $diferencia_en_minutos minutos.";

                array_push ( $arrayAuxiliar, $pedidoDemorado );
            }
        }

        $objDelaRespuesta->resultado = $arrayAuxiliar;

        return $response->withJson($objDelaRespuesta, 200);
    }

    public function traerEncuestas($request, $response, $args)
    {
        $objDelaRespuesta = new stdclass();

        $objDelaRespuesta->resultado = reporte::getEncuestas();

        return $response->withJson($objDelaRespuesta, 200);
    }
}