<?php 

class ProductoException extends Exception {}

class Producto {
    private $idProducto;
    private $nomProducto;
    private $descripcion;
    private $precio;
    private $idCategoria;
    private $nomCategoria;
    private $idCliente;
    private $nomCliente;

    public function __construct($idProducto, $nomProducto, $descripcion, $precio, $idCategoria, $nomCategoria, $idCliente, $nomCliente){
        $this->idProducto = $idProducto;
        $this->nomProducto = $nomProducto;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->idCategoria = $idCategoria;
        $this->nomCategoria = $nomCategoria;
        $this->idCliente = $idCliente;
        $this->nomCliente = $nomCliente;
    }

    public function getIdProducto(){
        return $this->idProducto;
    }

    public function setIdProducto($idProducto){
        if($idProducto !== null && !is_numeric($idProducto)){
            throw new ProductoException("Error en Id de Producto");
        }
        $this->idProducto = $idProducto;
    }

    public function getNomProducto(){
        return $this->nomProducto;
    }

    public function setNomProducto($nomProducto){
        if($nomProducto !== null){
            throw new ProductoException("Error en nombre de Producto");
        }
        $this->nomProducto = $nomProducto;
    }

    public function getDescProducto(){
        return $this->descripcion;
    }

    public function setDescProducto($descProducto){
        if($descProducto !== null){
            throw new ProductoException("Error en descripción de Producto");
        }
        $this->descripcion = $descProducto;
    }

    public function getPrecioProducto(){
        return $this->precio;
    }

    public function setPrecioProducto($precio){
        if($precio !== null){
            throw new ProductoException("Error en precio de Producto");
        }
        $this->precio = $precio;
    }

    public function getIdCategoria(){
        return $this->idCategoria;
    }

    public function setIdCategoria($idCategoria){
        if($idCategoria !== null){
            throw new ProductoException("Error en id de Categoria");
        }
        $this->idCategoria = $idCategoria;
    }

    public function getNomCategoria(){
        return $this->nomCategoria;
    }

    public function setNomCategoria($nomCategoria){
        if($nomCategoria !== null){
            throw new ProductoException("Error en nombre de Categoria");
        }
        $this->nomCategoria = $nomCategoria;
    }

    public function getIdCliente(){
        return $this->idCliente;
    }

    public function setidCliente($idCliente){
        if($idCliente !== null){
            throw new ProductoException("Error en id de Cliente");
        }
        $this->idCliente = $idCliente;
    }

    public function getNomCliente(){
        return $this->nomCliente;
    }

    public function setNomCliente($nomCliente){
        if($nomCliente !== null){
            throw new ProductoException("Error en nombre de Cliente");
        }
        $this->nomCliente = $nomCliente;
    }

    public function returnProductoAsArray(){
        $Producto = array();
        $Producto['idProducto'] = $this->getIdProducto();
        $Producto['nomProducto'] = $this->getNomProducto();
        $Producto['descProducto'] = $this->getDescProducto();
        $Producto['precioProducto'] = $this->getPrecioProducto();
        $Producto['idCategoria'] = $this->getIdCategoria();
        $Producto['nomCategoria'] = $this->getNomCategoria();
        $Producto['idCliente'] = $this->getIdCliente();
        $Producto['nomCliente'] = $this->getNomCliente();
        return $Producto;
    }

}
?>