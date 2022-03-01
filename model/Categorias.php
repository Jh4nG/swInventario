<?php 

class CategoriaException extends Exception {}

class Categoria {
    private $idCategoria;
    private $nomCategoria;
    private $idCliente;
    private $nomCliente;

    public function __construct($idDepto, $nomCategoria, $idCliente, $nomCliente){
        $this->idCategoria = $idDepto;
        $this->nomCategoria = $nomCategoria;
        $this->idCliente = $idCliente;
        $this->nomCliente = $nomCliente;
    }

    public function getIdCategoria(){
        return $this->idCategoria;
    }

    public function setIdCategoria($idCategoria){
        if($idCategoria !== null && !is_numeric($idCategoria)){
            throw new CategoriaException("Error en Id de Categoria");
        }
        $this->idCategoria = $idCategoria;
    }

    public function getNomCategoria(){
        return $this->nomCategoria;
    }

    public function setNomCategoria($nomCategoria){
        if($nomCategoria !== null){
            throw new CategoriaException("Error en nombre de Categoria");
        }
        $this->nomCategoria = $nomCategoria;
    }

    public function getIdCliente(){
        return $this->idCliente;
    }

    public function setidCliente($idCliente){
        if($idCliente !== null){
            throw new CategoriaException("Error en id de Cliente");
        }
        $this->idCliente = $idCliente;
    }

    public function getNomCliente(){
        return $this->nomCliente;
    }

    public function setNomCliente($nomCliente){
        if($nomCliente !== null){
            throw new CategoriaException("Error en nombre de Cliente");
        }
        $this->nomCliente = $nomCliente;
    }

    public function returnCategoriaAsArray(){
        $Categoria = array();
        $Categoria['idCategoria'] = $this->getIdCategoria();
        $Categoria['nomCategoria'] = $this->getNomCategoria();
        $Categoria['idCliente'] = $this->getIdCliente();
        $Categoria['nomCliente'] = $this->getNomCliente();
        return $Categoria;
    }

}
?>