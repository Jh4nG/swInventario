<?php 
class TurnosException extends Exception {}

class Turnos {
    private $idTurno;
	private $fechaTurno;
	private $horario;
	private $estadoTurno;
	private $obsTurno;
	private $codReserva;
	private $idServicio;
	private $idDocCliente;

    public function __construct($idTurno,$fechaTurno,$horario,$estadoTurno,$obsTurno,$codReserva,$idServicio,$idDocCliente){
        $this->idTurno      = $idTurno;
        $this->fechaTurno   = $fechaTurno;
        $this->horario      = $horario;
        $this->estadoTurno  = $estadoTurno;
        $this->obsTurno     = $obsTurno;
        $this->codReserva   = $codReserva;
        $this->idServicio   = $idServicio;
        $this->idDocCliente = $idDocCliente;
    }

    public function getIdTurno(){
        return $this->idTurno;
    }

    public function setIdTurno($idTurno){
        if($idTurno !== null){
            throw new TurnosException("Error en el id del Turno");
        }
        $this->idTurno = $idTurno;
    }    

    public function getFechaTurno(){
        return $this->fechaTurno;
    }

    public function setFechaTurno($fechaTurno){
        if($fechaTurno !== null){
            throw new TurnosException("Error en la fecha del Turno");
        }
        $this->fechaTurno = $fechaTurno;
    }

    public function getHorario(){
        return $this->horario;
    }

    public function setHorario($horario){
        if($horario !== null){
            throw new TurnosException("Error en el horario del Turno");
        }
        $this->horario = $horario;
    }

    public function getEstadoTurno(){
        return $this->estadoTurno;
    }

    public function setEstadoTurno($estadoTurno){
        if($estadoTurno !== null){
            throw new TurnosException("Error en el Estado del Turno");
        }
        $this->estadoTurno = $estadoTurno;
    }

    public function getObsTurno(){
        return $this->obsTurno;
    }

    public function setObsTurno($obsTurno){
        if($obsTurno !== null){
            throw new TurnosException("Error en la Obseravión del Turno");
        }
        $this->obsTurno = $obsTurno;
    }

    public function getCodReserva(){
        return $this->codReserva;
    }

    public function setCodReserva($codReserva){
        if($codReserva !== null){
            throw new TurnosException("Error en el Código Reserva del Turno");
        }
        $this->codReserva = $codReserva;
    }

    public function getIdServicio(){
        return $this->idServicio;
    }

    public function setIdServicio($idServicio){
        if($idServicio !== null){
            throw new TurnosException("Error en el Id Servicio del Turno");
        }
        $this->idServicio = $idServicio;
    }

    public function getIdDocCliente(){
        return $this->idDocCliente;
    }

    public function setIdDocCliente($idDocCliente){
        if($idDocCliente !== null){
            throw new TurnosException("Error en el Id Servicio del Turno");
        }
        $this->idDocCliente = $idDocCliente;
    }

    public function returnTurnosAsArray(){
        $turno = array();
        $turno['idTurno']      = $this->getIdTurno();
        $turno['fechaTurno']   = $this->getFechaTurno();
        $turno['horario']      = $this->getHorario();
        $turno['estadoTurno']  = $this->getEstadoTurno();
        $turno['obsTurno']     = $this->getObsTurno();
        $turno['codReserva']   = $this->getCodReserva();
        $turno['idServicio']   = $this->getIdServicio();
        $turno['idDocCliente'] = $this->getIdDocCliente();
        return $turno;
    }
}
?>