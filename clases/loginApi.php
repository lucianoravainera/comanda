<?php
require_once 'empleado.php';
require_once 'archivo.php';

class loginApi
{
    public function login($request, $response, $args) 
    {
        $token="";
        $ArrayDeParametros = $request->getParsedBody();
        $objDelaRespuesta= new stdclass();
        $email=$ArrayDeParametros['email'];
        $clave= $ArrayDeParametros['clave'];
        if(isset($email) && isset($clave) && $email !="" && $clave !="")
        {
                $rtaEsValido = empleado::esValido($email,$clave);

                if($rtaEsValido->estado == 1)
                {
                    $empleado= $rtaEsValido->empleado;
                    $objDelaRespuesta->perfil = $empleado->perfil;
                    $objDelaRespuesta->token = AutentificadorJWT::CrearToken($empleado);
                    $objDelaRespuesta->mensaje = $rtaEsValido->msj;
                    $empleado->InsertarLoginDeEmpleado(date("d/m/Y H:i:s"));
                    return $response->withJson($objDelaRespuesta ,200);
                }
                else
                {
                    $objDelaRespuesta->acceso = $rtaEsValido->msj;
                    return $response->withJson($objDelaRespuesta, 409);
                }
        }
        else
        {
            $objDelaRespuesta->error = "Debe completar los campos email y clave";
            return $response->withJson($objDelaRespuesta, 409);
        }
    } 
}