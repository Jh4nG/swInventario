<?php 
class HorariosException extends Exception {}

class Horarios {
    private $idHorarios;
    private $horarios;

    public function __construct($idHorarios, $horarios){
        $this->idHorarios = $idHorarios;
        $this->horarios = $horarios;
    }

    public function getIdHorarios(){
        return $this->idHorarios;
    }

    public function setIdHorarios($idHorarios){
        if($idHorarios !== null && !is_numeric($idHorarios)){
            throw new HorariosException("Error en Id de la Horarios");
        }
        $this->idHorarios = $idHorarios;
    }

    public function getHorarios(){
        return $this->horarios;
    }

    public function setHorarios($horarios){
        if($horarios !== null){
            throw new HorariosException("Error en nombre de la Horarios");
        }
        $this->horarios = $horarios;
    }

    public function returnHorariosAsArray(){
        $Horarios = array();
        $Horarios['idHorarios'] = $this->getIdHorarios();
        $Horarios['horarios'] = $this->getHorarios();
        return $Horarios;
    }
}
?>