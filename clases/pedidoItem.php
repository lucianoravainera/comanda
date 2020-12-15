<?php
class PedidoItem
{
    public $id;
    public $id_pedido;
    public $id_item;
    public $cantidad;
    public $estado;
    public $sector;
    public $nombre;
    public $empleado_preparador_id;
    public $id_comanda;
    public $foto;
    public $empleado;

    public function InsertarElPedidoItemParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO pedidoitems (id_pedido, id_item, cantidad, estado) values(:id_pedido,:id_item,:cantidad,:estado)");
        $consulta->bindValue(':id_pedido', $this->id_pedido, PDO::PARAM_STR);
        $consulta->bindValue(':id_item', $this->id_item, PDO::PARAM_STR);
        $consulta->bindValue(':cantidad', $this->cantidad, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);

        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public function TraerMisPedidosParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT pi.*, it.sector, it.nombre, it.foto, pe.id_comanda FROM pedidoitems pi, items it, pedidos pe WHERE (pi.estado = 'en preparacion' OR pi.estado = 'derivado') AND pi.id_item IN (SELECT id FROM items WHERE sector = :sector) AND pi.id_item = it.id AND pi.id_pedido = pe.id");
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);

        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "PedidoItem");
    }

    public function TraerMiPedidoParametros($estado)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT pi.*, it.sector, it.nombre FROM pedidoitems pi, items it WHERE pi.estado = '$estado' AND pi.id_item IN (SELECT id FROM items WHERE sector = :sector) AND pi.id_item = it.id AND pi.id = :id_pedido_item");
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':id_pedido_item', $this->id, PDO::PARAM_STR);

        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "PedidoItem");
    }

    public function TomarMiPedidoParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidoitems SET estado = 'en preparacion', empleado_preparador_id = :empleado_preparador_id WHERE id = :id_pedido_item");
        $consulta->bindValue(':id_pedido_item', $this->id, PDO::PARAM_STR);
        $consulta->bindValue(':empleado_preparador_id', $this->empleado_preparador_id, PDO::PARAM_STR);

        return $consulta->execute();
    }

    public function CerrarMiPedidoParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

        $consulta = $objetoAccesoDato->RetornarConsulta("UPDATE pedidoitems SET estado = 'listo para servir', empleado_preparador_id = :empleado_preparador_id  WHERE id = :id_pedido_item");
        $consulta->bindValue(':id_pedido_item', $this->id, PDO::PARAM_STR);
        $consulta->bindValue(':empleado_preparador_id', $this->empleado_preparador_id, PDO::PARAM_STR);

        return $consulta->execute();
    }

    public function DetectarPedidoGeneralCompletadoParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

        $consulta = $objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidoitems WHERE id_pedido = :id_pedido AND estado <> 'listo para servir'");
        $consulta->bindValue(':id_pedido', $this->id_pedido, PDO::PARAM_STR);

        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "PedidoItem");
    }

    public static function TraerTodosLosPedidosItems()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT pi.*, it.sector, it.nombre, it.foto, pe.id_comanda FROM pedidoitems pi, items it, pedidos pe WHERE pi.id_item = it.id AND pi.id_pedido = pe.id");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "PedidoItem");
    }

    public static function TraerPedidosItemsDeUnPedido($pedidoId)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        // $consulta =$objetoAccesoDato->RetornarConsulta("SELECT pi.*, it.sector, it.nombre, it.foto, pe.id_comanda FROM pedidoitems pi, items it, pedidos pe WHERE pi.id_item = it.id AND pi.id_pedido = pe.id AND pe.id = '$pedidoId'");
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT pi.*, it.sector, it.nombre, it.precio, it.foto, pe.id_comanda, em.apellido as empleado FROM items it, pedidos pe, pedidoitems pi LEFT JOIN empleados em ON pi.empleado_preparador_id = em.id WHERE pi.id_item = it.id AND pi.id_pedido = pe.id AND pe.id = '$pedidoId'");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "PedidoItem");
    }
}