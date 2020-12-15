<?php

require_once "AutentificadorJWT.php";
class MWparaAutentificar
{
    private $tipoRequerido = '';
    private $mensaje = '';
    public function VerificarEmpleado($request, $response, $next)
    {   
        $objDelaRespuesta= new stdclass();
        if(isset($request->getHeader('token')[0]))
        {
            $arrayConToken = $request->getHeader('token');
            $token=$arrayConToken[0];
        } 
        else 
        {
            $objDelaRespuesta->acceso = "Acceso denegado a este sitio.";
            return $response->withJson($objDelaRespuesta, 403);
        }
                
        try 
        {
            AutentificadorJWT::VerificarToken($token);
            $objDelaRespuesta->esValido=true;
        } 
        catch (Exception $e) 
        {
            $objDelaRespuesta->esValido=false;
            $objDelaRespuesta->error = $e->getMessage();
            return $response->withJson($objDelaRespuesta, 403);
        }

		if ($objDelaRespuesta->esValido) 
		{
            $payload=AutentificadorJWT::ObtenerData($token);

            if($this->tipoRequerido == '')
            {
                $response = $next($request, $response);
            }
            else if (strtolower($payload->perfil)==$this->tipoRequerido)
			{
                $response = $next($request, $response);
			} 
			else 
			{
                $objDelaRespuesta->respuesta=$this->mensaje;
                return $response->withJson($objDelaRespuesta, 403);
            }
		} 
		
        return $response;
    }

    public function VerificarAdmin($request, $response, $next)
    {
        $this->tipoRequerido = "admin";
        $this->mensaje = "Acceso permitido solo a Administradores.";

        return $this->VerificarEmpleado($request, $response, $next);
    }

    public function VerificarMozo($request, $response, $next)
    {
        $this->tipoRequerido = "mozo";
        $this->mensaje = "Acceso permitido solo a Mozos.";

        return $this->VerificarEmpleado($request, $response, $next);
    }

    public function VerificarCliente($request, $response, $next)
    {
        $this->tipoRequerido = "cliente";
        $this->mensaje = "Acceso permitido solo a Clientes.";

        return $this->VerificarEmpleado($request, $response, $next);
    }
}