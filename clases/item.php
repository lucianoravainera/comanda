<?php

class item
{
    public $id;
    public $nombre;
    public $precio;
    public $sector;
    public $estado;
    public $tiempo_estimado_preparacion;
    public $foto;

    public function InsertarElItemParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta = $objetoAccesoDato->RetornarConsulta("INSERT into items (nombre, precio, sector, estado, tiempo_estimado_preparacion, foto) values(:nombre,:precio,:sector,:estado,:tiempo_estimado_preparacion,:foto)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado_preparacion', $this->tiempo_estimado_preparacion, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);

        $consulta->execute();
        return $objetoAccesoDato->RetornarUltimoIdInsertado();
    }

    public static function TraerTodosLosItems()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("select * from items");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "item");
    }

    public static function TraerTodosLosItemsActivos()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM items WHERE estado = 'activo'");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, "item");
    }

    public static function TraerUnItem($id)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT * 
													    FROM items 
													    WHERE id = '$id'");
        $consulta->execute();
        $itemBuscado= $consulta->fetchObject('item');
        return $itemBuscado;
    }

    //MODIFICAR
    public function ModificarItemParametros()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();

        $consulta =$objetoAccesoDato->RetornarConsulta("UPDATE items 
                                                        SET nombre=:nombre,
                                                        precio=:precio,
                                                        sector=:sector,
                                                        estado=:estado,
                                                        tiempo_estimado_preparacion=:tiempo_estimado_preparacion,
                                                        foto=:foto
                                                        WHERE id=:id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':sector', $this->sector, PDO::PARAM_STR);
        $consulta->bindValue(':estado', $this->estado, PDO::PARAM_STR);
        $consulta->bindValue(':tiempo_estimado_preparacion', $this->tiempo_estimado_preparacion, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        return $consulta->execute();
    }

    //BORRAR
    public function BorrarItem()
    {
        $item = new item();
        $item = item::TraerUnItem($this->id);
        
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE 
                                                        FROM items 				
                                                        WHERE id=:id");
        $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->rowCount();
    }
}
