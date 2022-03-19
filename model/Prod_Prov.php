<?php 
class ProdProvException extends Exception {}

class ProdProv {
    private $idProdProv;
    private $idProducto;
    private $nomProducto;
    private $nitProv;
    private $nomProv;
    private $cantidad_actual;
    private $cliente;

    public function __construct($idProdProv, $idProducto, $nomProducto, $nitProv, $nomProv, $cantidad_actual, $cliente){
        $this->idProdProv = $idProdProv;
        $this->idProducto = $idProducto;
        $this->nomProducto = $nomProducto;
        $this->nitProv = $nitProv;
        $this->nomProv = $nomProv;
        $this->cantidad_actual = $cantidad_actual;
        $this->cliente = $cliente;
    }

    public function getidProdProv(){
        return $this->idProdProv;
    }

    public function setidProdProv($idProdProv){
        if($idProdProv !== null && !is_numeric($idProdProv)){
            throw new ProdProvException("Error en Id de la ProdProv");
        }
        $this->idProdProv = $idProdProv;
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

    public function getNitProv(){
        return $this->nitProv;
    }

    public function setNitProv($nitProv){
        if($nitProv !== null && !is_numeric($nitProv)){
            throw new ProdProvException("Error en Id de la Prov");
        }
        $this->nitProv = $nitProv;
    }

    public function getNomProv(){
        return $this->nomProv;
    }

    public function setNomProv($nomProv){
        if($nomProv !== null){
            throw new ProdProvException("Error en nombre de la Prov");
        }
        $this->nomProv = $nomProv;
    }

    public function getCliente(){
        return $this->cliente;
    }

    public function setCliente($cliente){
        if($cliente !== null){
            throw new ProdProvException("Error en el id cliente");
        }
        $this->cliente = $cliente;
    } 

    public function getCantidadActual(){
        return $this->cantidad_actual;
    }

    public function setCantidadActual($cantidad_actual){
        if($cantidad_actual !== null){
            throw new ProdProvException("Error en Cantidad Actual");
        }
        $this->$cantidad_actual = $cantidad_actual;
    } 

    public function returnProdProvAsArray(){
        $ProdProv = array();
        $ProdProv['idProdProv'] = $this->getidProdProv();
        $ProdProv['idProducto'] = $this->getIdProducto();
        $ProdProv['nomProducto'] = $this->getNomProducto();
        $ProdProv['nitProv'] = $this->getNitProv();
        $ProdProv['nomProv'] = $this->getNomProv();
        $ProdProv['cantidad_actual'] = $this->getCantidadActual();
        $ProdProv['cliente'] = $this->getCliente();
        return $ProdProv;
    }
}
?>