<?php
class Pedido
{
    public $id_comanda;
    public $id_mesa;
    public $estado;
    public $fecha_hora_inicio;
    public $tiempo_estimado_preparacion_minutos;
    public $id_cliente;

    public function InsertarElPedidoParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into pedidos (id_comanda, id_mesa, estado, fecha_hora_inicio, tiempo_estimado_preparacion_minutos, id_cliente) values(:id_comanda,:id_mesa,:estado,:fecha_hora_inicio,:tiempo_estimado_preparacion_minutos,:id_cliente)");
        $consulta->bindValue(':id_comanda', $this->id_comanda, PDO::PARAM_STR);
        $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_hora_inicio', $this->fecha_hora_inicio, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado_preparacion_minutos', $this->tiempo_estimado_preparacion_minutos, PDO::PARAM_STR);
        $consulta->bindValue(':id_cliente', $this->id_cliente, PDO::PARAM_STR);

        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function AlistarPedido($id, $fechaFinalizado)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidos set estado = 'listo para servir', fecha_hora_fin = '$fechaFinalizado' WHERE id = '$id'");
        return $consulta->execute();
    }

    public static function GetIdComanda($id)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT id_comanda FROM pedidos WHERE id = '$id'");
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_OBJ);
    }

    public static function GetComboPedidoYMesa($codigo_pedido, $codigo_mesa)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos WHERE id_comanda = '$codigo_pedido' AND id_mesa = (SELECT mesa_id FROM mesas WHERE codigo_mesa = '$codigo_mesa')");
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_OBJ);
    }

    public static function TraerTodosLosPedidos()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        // $consulta =$objetoAccesoDato->RetornarConsulta("select * from pedidos");
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT pe.*, me.codigo_mesa , em.nombre as cliente_nombre, em.apellido as cliente_apellido, em.foto as cliente_foto from  mesas me, pedidos pe LEFT JOIN empleados em ON pe.id_cliente = em.id WHERE pe.id_mesa = me.mesa_id");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Pedido");
    }

    public static function TraerPedidosPorCliente($id_cliente)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        // $consulta =$objetoAccesoDato->RetornarConsulta("select * from pedidos");
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT pe.*, me.codigo_mesa , em.nombre as cliente_nombre, em.apellido as cliente_apellido, en.id_pedido as pedido_encuesta from mesas me, pedidos pe LEFT JOIN empleados em ON pe.id_cliente = em.id LEFT JOIN encuestas en ON pe.id = en.id_pedido WHERE pe.id_mesa = me.mesa_id AND pe.id_cliente = '$id_cliente'");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Pedido");
    }
}