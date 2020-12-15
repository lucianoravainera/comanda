<?php
class empleado
{
    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $clave;
    public $turno;
    public $perfil;
    public $fechaCreacion;
    public $foto;
    public $estado;


//TRAER empleadoS
    public static function TraerTodosLosEmpleados()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from empleados");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "empleado");
    }

    public static function TraerTodosLosLogins()
    {
        try {
            $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
            $consulta =$objetoAccesoDato->RetornarConsulta("SELECT e.id, e.nombre, e.apellido, ie.Fecha_Hora_Ingreso 
													        FROM ingresosempleados ie 
													        INNER JOIN empleados e ON ie.Id_Empleado = e.id
													        ORDER BY ie.Fecha_Hora_Ingreso DESC");
			$consulta->execute();
			return $consulta->fetchAll(PDO::FETCH_ASSOC);
			
        } catch (PDOException $e) {
              $e->getMessage();
        }
        
    }

    public static function TraerUnEmpleado($id)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT id, nombre, apellido, email, clave, turno, perfil, fechaCreacion, foto, estado 
													    FROM empleados 
													    WHERE id = '$id'");
        $consulta->execute();
        $empleadoBuscado= $consulta->fetchObject('empleado');
        return $empleadoBuscado;
    }

    public static function TraerUnEmpleadoPorMail($email)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT id, nombre, apellido, email, clave, turno, perfil, fechaCreacion, foto, estado 
                                                        FROM empleados 
                                                        WHERE email = '$email'");
        $consulta->execute();
        $empleadoBuscado= $consulta->fetchObject('empleado');
        return $empleadoBuscado;
    }

    public static function TraerCantOperaciones()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT id, nombre, apellido, cantEntrada, cantSalida, (cantEntrada + cantSalida) AS total 
                                                        FROM (SELECT em.id, em.nombre, em.apellido, (select Count(es.idEmpleadoEntrada) 
                                                              FROM estacionamiento es 
                                                              WHERE es.idEmpleadoEntrada = em.id) AS cantEntrada, (select Count(es.idEmpleadoSalida) from estacionamiento es where es.idEmpleadoSalida = em.id) AS cantSalida from empleados em) t 
                                                        ORDER BY nombre");
        $consulta->execute();
		return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


//INSERTAR MODIFICAR
    public function GuardarEmpleado()
    {
        if ($this->id>0) {
            $this->ModificarempleadoParametros();
        } else {
            $this->InsertarElempleadoParametros();
        }
    }

    public function InsertarLoginDeEmpleado($fechaLogin)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into ingresosempleados (fecha_Hora_Ingreso, id_Empleado) values(:fechaHoraIngreso, :idEmpleado)");
        $consulta->bindValue(':fechaHoraIngreso', $fechaLogin, PDO::PARAM_STR);
        $consulta->bindValue(':idEmpleado', $this->id, PDO::PARAM_STR);
        
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }



//INSERTAR
    public function InsertarElEmpleadoParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into empleados  (nombre, apellido, email, clave, turno, perfil, fechaCreacion, foto, estado) values(:nombre,:apellido,:email,:clave,:turno,:perfil,:fechaCreacion,:foto,:estado)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':turno', $this->turno, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $this->perfil, PDO::PARAM_STR);
        $consulta->bindValue(':fechaCreacion', $this->fechaCreacion, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }



//MODIFICAR
    public function ModificarEmpleadoParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

        $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE empleados 
                                                        SET nombre=:nombre,
                                                        apellido=:apellido,
                                                        clave=:clave,
                                                        turno=:turno,
                                                        perfil=:perfil,
                                                        estado=:estado,
                                                        email=:email
                                                        WHERE id=:id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':turno', $this->turno, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $this->perfil, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        return $consulta->execute();
    }


//BORRAR
    public function BorrarEmpleado()
    {
        $empleado = new empleado();
        $empleado = empleado::TraerUnEmpleado($this->id);
        // if (!empty($empleado) && (!is_null($empleado->foto))) {
        //        archivo::moverFotoABackup($empleado->foto, $empleado->email, 'backupFotos/');
        // }
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE 
                                                        FROM empleados 				
                                                        WHERE id=:id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }

//VERIFICAR CLAVE MAIL ESTADO DE EMPLEADO
    public static function esValido($email, $clave)
    {
        $resp = new stdClass();
        $empleado= empleado::TraerUnEmpleadoPorMail($email);
        if ($empleado != null) {
            $verify = password_verify($clave, $empleado->clave);
            if ($verify) {
                if(strtolower($empleado->estado) == "activo")
                {
                    $resp->empleado = $empleado;
                    $resp->msj = "Bienvenida/o ". $empleado->nombre . "!";
                    $resp->perfil = $empleado->perfil;
                    $resp->estado = 1;
                }
                else
                {
                    $resp->msj = "Acceso rechazado. Empleado inactivo o suspendido";
                    $resp->estado = 0;
                }
            } 
            else 
            {
                $resp->msj = "Compruebe su email o clave";
                $resp->estado = 0;
            }
        } 
        else 
        {
            $resp->msj = "Compruebe su email o clave";
            $resp->estado = 0;
        }
        return $resp;
    }

}
