<?php

class reporte
{
    public static function GetDiasHorariosEmpleados()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT ie.Id_Empleado, ie.Fecha_Hora_Ingreso, e.nombre, e.apellido, e.perfil 
                                                         FROM ingresosempleados ie, empleados e
                                                         WHERE ie.Id_Empleado = e.id");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function GetOperacionesPorSector()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT COUNT(*) AS CANTIDAD, i.sector as SECTOR FROM pedidoitems pi, items i
                                                         WHERE pi.id_item = i.id
                                                         AND pi.estado = 'listo para servir'
                                                         GROUP BY i.sector
                                                         ORDER BY i.sector ASC");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function GetOperacionesPorSectorYEmpleado()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT COUNT(*) AS CANTIDAD, i.sector as SECTOR, e.nombre, e.apellido FROM pedidoitems pi, items i, empleados e
                                                         WHERE pi.id_item = i.id
                                                         AND pi.estado = 'listo para servir'
                                                         AND pi.empleado_preparador_id = e.id
                                                         GROUP BY e.nombre, e.apellido
                                                         ORDER BY i.sector ASC");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function GetOperacionesPorEmpleado($id)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT COUNT(*) AS CANTIDAD, i.sector as SECTOR, e.nombre, e.apellido FROM pedidoitems pi, items i, empleados e
                                                         WHERE pi.id_item = i.id
                                                         AND pi.estado = 'listo para servir'
                                                         AND pi.empleado_preparador_id = e.id
                                                         AND e.id = '$id'
                                                         GROUP BY e.nombre, e.apellido
                                                         ORDER BY i.sector ASC");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function GetMasVendido()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT CANTIDAD, nombre, precio, tiempo_estimado_preparacion, sector FROM (SELECT SUM(cantidad) AS CANTIDAD, i.nombre, i.precio, i.tiempo_estimado_preparacion, i.sector
                                                         FROM pedidoitems pi, items i
                                                         WHERE pi.id_item = i.id
                                                         AND pi.estado = 'listo para servir'
                                                         GROUP BY id_item) resultado
                                                         WHERE CANTIDAD = (SELECT MAX(cantidad)
                                                                           FROM ((SELECT SUM(cantidad) AS CANTIDAD
                                                                                  FROM pedidoitems
                                                                                  WHERE estado = 'listo para servir'
                                                                                  GROUP BY id_item) subresultado))");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function GetMenosVendido()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT CANTIDAD, nombre, precio, tiempo_estimado_preparacion, sector FROM (SELECT SUM(cantidad) AS CANTIDAD, i.nombre, i.precio, i.tiempo_estimado_preparacion, i.sector
                                                         FROM pedidoitems pi, items i
                                                         WHERE pi.id_item = i.id
                                                         AND pi.estado = 'listo para servir'
                                                         GROUP BY id_item) resultado
                                                         WHERE CANTIDAD = (SELECT MIN(cantidad)
                                                                           FROM ((SELECT SUM(cantidad) AS CANTIDAD
                                                                                  FROM pedidoitems
                                                                                  WHERE estado = 'listo para servir'
                                                                                  GROUP BY id_item) subresultado))");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function GetPedidosTerminados()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos WHERE estado = 'listo para servir'");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getEncuestas()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT en.*, pe.id_comanda FROM encuestas en, pedidos pe WHERE en.id_pedido = pe.id");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }
}
