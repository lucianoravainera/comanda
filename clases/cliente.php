<?php
class Cliente
{
    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $clave;
    public $perfil;
    public $fechaCreacion;
    public $foto;
    public $estado;

    public function InsertarElClienteParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into empleados (nombre, apellido, email, clave, perfil, fechaCreacion, foto, estado) values(:nombre,:apellido,:email,:clave,:perfil,:fechaCreacion,:foto,:estado)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':email', $this->email, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $this->perfil, PDO::PARAM_STR);
        $consulta->bindValue(':fechaCreacion', $this->fechaCreacion, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function TraerClientes()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from empleados WHERE perfil = 'Cliente'");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Cliente");
    }

    public static function InsertarEncuesta($mesa, $restaurante, $mozo, $cocinero, $bartender, $cervecero, $precio_calidad, $descripcion, $pedido_id)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO encuestas VALUES ('$mesa', '$restaurante', '$mozo', '$cocinero', '$bartender', '$cervecero', '$precio_calidad', '$descripcion','$pedido_id')");
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }
}