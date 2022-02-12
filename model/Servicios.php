<?php 
class ServiciosException extends Exception {}

class Servicios {
    private $idServicios;
    private $nomServicios;
    private $valorServicios;
    private $tiempoServicios;

    public function __construct($idServicios, $nomServicios, $valorServicios, $tiempoServicios){
        $this->idServicios = $idServicios;
        $this->nomServicios = $nomServicios;
        $this->valorServicios = $valorServicios;
        $this->tiempoServicios = $tiempoServicios;
    }

    public function getIdServicios(){
        return $this->idServicios;
    }

    public function setIdServicios($idServicios){
        if($idServicios !== null && !is_numeric($idServicios)){
            throw new ServiciosException("Error en Id de la Servicios");
        }
        $this->idServicios = $idServicios;
    }

    public function getNomServicios(){
        return $this->nomServicios;
    }

    public function setNomServicios($nomServicios){
        if($nomServicios !== null){
            throw new ServiciosException("Error en nombre de la Servicios");
        }
        $this->nomServicios = $nomServicios;
    }

    public function getValorServicios(){
        return $this->valorServicios;
    }

    public function setValorServicios($valorServicios){
        if($valorServicios !== null){
            throw new ServiciosException("Error en direccion de la Servicios");
        }
        $this->valorServicios = $valorServicios;
    }

    public function getTiempoServicios(){
        return $this->tiempoServicios;
    }

    public function setTiempoServicios($tiempoServicios){
        if($tiempoServicios !== null){
            throw new ServiciosException("Error en telefono de la Servicios");
        }
        $this->tiempoServicios = $tiempoServicios;
    }

    public function returnServicioAsArray(){
        $Servicios = array();
        $Servicios['idServicios'] = $this->getIdServicios();
        $Servicios['nomServicios'] = $this->getNomServicios();
        $Servicios['valorServicios'] = $this->getValorServicios();
        $Servicios['tiempoServicios'] = $this->getTiempoServicios();
        return $Servicios;
    }
}
?>