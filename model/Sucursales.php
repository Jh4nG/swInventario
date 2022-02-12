<?php 
class SucursalesException extends Exception {}

class Sucursal {
    private $idSucursal;
    private $nomSucursal;
    private $dirSucursal;
    private $telSucursal;

    public function __construct($idSucursal, $nomSucursal, $dirSucursal, $telSucursal){
        $this->idSucursal = $idSucursal;
        $this->nomSucursal = $nomSucursal;
        $this->dirSucursal = $dirSucursal;
        $this->telSucursal = $telSucursal;
    }

    public function getIdSucursal(){
        return $this->idSucursal;
    }

    public function setIdSucursal($idSucursal){
        if($idSucursal !== null && !is_numeric($idSucursal)){
            throw new SucursalesException("Error en Id de la Sucursal");
        }
        $this->idSucursal = $idSucursal;
    }

    public function getNomSucursal(){
        return $this->nomSucursal;
    }

    public function setNomSucursal($nomSucursal){
        if($nomSucursal !== null){
            throw new SucursalesException("Error en nombre de la Sucursal");
        }
        $this->nomSucursal = $nomSucursal;
    }

    public function getDirSucursal(){
        return $this->dirSucursal;
    }

    public function setDirSucursal($dirSucursal){
        if($dirSucursal !== null){
            throw new SucursalesException("Error en direccion de la Sucursal");
        }
        $this->dirSucursal = $dirSucursal;
    }

    public function getTelSucursal(){
        return $this->telSucursal;
    }

    public function setTelSucursal($telSucursal){
        if($telSucursal !== null){
            throw new SucursalesException("Error en telefono de la Sucursal");
        }
        $this->telSucursal = $telSucursal;
    }

    public function returnSucursalAsArray(){
        $Sucursal = array();
        $Sucursal['idSucursal'] = $this->getIdSucursal();
        $Sucursal['nomSucursal'] = $this->getNomSucursal();
        $Sucursal['dirSucursal'] = $this->getDirSucursal();
        $Sucursal['telSucursal'] = $this->getTelSucursal();
        return $Sucursal;
    }
}
?>