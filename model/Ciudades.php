<?php 
class CiudadesException extends Exception {}

class Ciudades {
    private $_idCiudad;
    private $_nomCiudad;

    public function __construct($idCiudad, $nomCiudad){
        $this->_idCiudad = $idCiudad;
        $this->_nomCiudad = $nomCiudad;
    }

    public function getIdCiudad(){
        return $this->_idCiudad;
    }

    public function setIdCiudad($idCiudad){
        if($idCiudad !== null && !is_numeric($idCiudad)){
            throw new CiudadesException("Error en Id de la Ciudad");
        }
        $this->_idCiudad = $idCiudad;
    }

    public function getNomCiudad(){
        return $this->_nomCiudad;
    }

    public function setNomCiudad($nomCiudad){
        if($nomCiudad !== null && strlen($idCiudad)>50){
            throw new CiudadesException("Error en nombre de la ciudad");
        }
        $this->_nomCiudad = $nomCiudad;
    }

    public function returnCiudadAsArray(){
        $ciudad = array();
        $ciudad['idCiudad'] = $this->getIdCiudad();
        $ciudad['nomCiudad'] = $this->getNomCiudad();
        return $ciudad;
    }
}
?>