<?php

class mesa
{
    public $mesa_id;
    public $codigo_mesa;
    public $estado;

    public function InsertarLaMesaParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into mesas (mesa_id, codigo_mesa, estado) values(:mesa_id,:codigo_mesa,:estado)");
        $consulta->bindValue(':mesa_id', $this->mesa_id, PDO::PARAM_STR);
        $consulta->bindValue(':codigo_mesa', $this->codigo_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);

        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function TraerTodasLasMesas()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select me.*, em.nombre, em.apellido from mesas me LEFT JOIN empleados em ON me.id_cliente = em.id");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "mesa");
    }

    public static function TraerTodosLasMesasHabilitadas()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM mesas WHERE habilitada = 'si'");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "mesa");
    }

    public static function TraerMesaPorId($mesa_id)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM mesas WHERE mesa_id = '$mesa_id'");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "mesa");
    }

    public static function AsignarMesa($id_mesa,$id_cliente)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE mesas SET id_cliente = '$id_cliente' WHERE mesa_id = '$id_mesa'");
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function ActualizarMesa($estado,$id_mesa)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE mesas SET estado = '$estado' WHERE mesa_id = '$id_mesa'");
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function cerrarMesaCliente($estado,$id_mesa)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE mesas SET estado = '$estado', id_cliente = NULL WHERE mesa_id = '$id_mesa'");
        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }
}