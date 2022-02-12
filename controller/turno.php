<?php
include '../head.php';
require_once('../model/Turnos.php');

class TurnoController extends Response{
    private $db;
    private $idTurno = false;

    public function __construct($idTurno = false){
        try{
            $this->idTurno = $idTurno;
            $this->db = DB::conectarDB();
        }catch(PDOException $ex){
            $this->setSuccess(false);
            $this->setHttpStatusCode(500);
            $this->addMessage("Error de conexión a la BD");
            $this->send();
            exit;
        }
        $this->init();
    }

    private function init(){
        if(array_key_exists("idTurno", $_GET)){
            $this->idTurno = $_GET['idTurno'];
            if($this->idTurno == '' || !is_numeric($this->idTurno)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Turno no válido");
                $this->send();
                exit;
            }
        }
        $this->executeProcess();
    }

    private function executeProcess(){
        switch($_SERVER['REQUEST_METHOD']){
            case 'GET':
                $this->getRequest();
                break;
            case 'DELETE':
                $this->getDelete();
                break;
            case 'POST':
                $this->getPost();
                break;
            default :
                $this->setSuccess(false);
                $this->setHttpStatusCode(500); 
                $this->addMessage("Request Method no encontrado.");
                $this->send();
                break;
        }
    }

    private function getRequest(){
        if(is_numeric($this->idTurno)){
            try {
                $query = $this->db->prepare('select * from turnos where id_turno = :idTurno');
                $query->bindParam(':idTurno', $this->idTurno);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Turno no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Turno = new Turnos($row['id_turno'], 
                                        $row['fecha_turno'], 
                                        $row['horario '], 
                                        $row['estado_turno'], 
                                        $row['obs_turno'], 
                                        $row['cod_reserva'], 
                                        $row['id_servicio'], 
                                        $row['nro_doc_cliente']);
                    $TurnoArray[] = $Turno->returnTurnosAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['turnos'] = $TurnoArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (TurnosException $ex) {
                $this->setSuccess(false); $this->setHttpStatusCode(500);
                $this->addMessage($ex->getMessage());
                $this->send();
                exit;
            }catch(PDOException $ex){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage("Error conectando a Base de Datos");
                $this->send();
                exit;
            }
        }else{
            try{
                $query = $this->db->prepare('select * from turnos');
                $query->execute();
                $rowCount = $query->rowCount();
                $TurnoArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Turno = new Turnos($row['id_turno'], 
                                        $row['fecha_turno'], 
                                        $row['horario'], 
                                        $row['estado_turno'], 
                                        $row['obs_turno'], 
                                        $row['cod_reserva'], 
                                        $row['id_servicio'], 
                                        $row['nro_doc_cliente']);
                    $TurnoArray[] = $Turno->returnTurnosAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['turnos'] = $TurnoArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(TurnosException $ex){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage($ex->getMessage());
                $this->send(); 
                exit;
            }catch(PDOException $ex){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage("Error conectando a Base de Datos");
                $this->send();
                exit;
            }
        }
    }

    private function getDelete(){
        try{
            //Validar que el Turno no tenga turnos relacionadas
            $query = $this->db->prepare('select count(*) as conteo from turnos where id_turno = :idTurno');
            $query->bindParam(':idTurno', $this->idTurno);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar Turno.');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('delete from turnos where id_turno = :idTurno');
            $query->bindParam(':idTurno', $this->idTurno);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Turno no encontrado');
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Turno eliminado');
            $this->send();
            exit();
        }catch(PDOException $ex){
            $this->setSuccess(false);
            $this->setHttpStatusCode(500);
            $this->addMessage('Error eliminando Turno');
            $this->send();
            exit();
        }
    }

    private function getPost(){
        try{
            if($_SERVER['CONTENT_TYPE'] !== 'application/json'){ // Recibir JSON
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Content Type no corresponde a formato JSON');
                $this->send();
                exit();
            } 
            $rawPOSTData = file_get_contents('php://input');
            if(!$jsonData = json_decode($rawPOSTData)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Request Body no corresponde a formato JSON');
                $this->send();
                exit();
            }

            if(isset($jsonData->getUser) && $jsonData->getUser == true){ // Si viene getUser y se valida Turno con contraseña
                $this->contrasena = $jsonData->contrasena;
                $this->idTurno = $jsonData->idTurno;
                $this->getRequestParams();
                exit;
            }

            if(!isset($jsonData->fechaTurno)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Fecha de Turno es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->horario)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Horario de Turno es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->estadoTurno)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Estado de Turno es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->obsTurno)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Observacion de Turno es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->codReserva)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Código Reserva de Turno es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idServicio)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id Servicio de Turno es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idDocCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id Doc Cliente de Turno es obligatorio');
                $this->send();
                exit(); 
            }
            // $idTurno = new Turnos(null, $jsonData->idTurno);
            $query = $this->db->prepare('insert into turnos (fecha_turno, 
                                                            horario, 
                                                            estado_turno, 
                                                            obs_turno, 
                                                            cod_reserva, 
                                                            id_servicio, 
                                                            nro_doc_cliente) 
                                                    values (:fechaTurno
                                                            ,:horario
                                                            ,:estadoTurno
                                                            ,:obsTurno
                                                            ,:codReserva
                                                            ,:idServicio
                                                            ,:idDocCliente);');
            $query->bindParam(':fechaTurno',   $jsonData->fechaTurno,  PDO::PARAM_STR);
            $query->bindParam(':horario',      $jsonData->horario,     PDO::PARAM_INT);
            $query->bindParam(':estadoTurno',  $jsonData->estadoTurno, PDO::PARAM_INT);
            $query->bindParam(':obsTurno',     $jsonData->obsTurno,    PDO::PARAM_STR);
            $query->bindParam(':codReserva',   $jsonData->codReserva,  PDO::PARAM_STR);
            $query->bindParam(':idServicio',   $jsonData->idServicio,  PDO::PARAM_INT);
            $query->bindParam(':idDocCliente', $jsonData->idDocCliente,PDO::PARAM_INT);

            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Turno');
                $this->send();
                exit();
            }
            $lastIdTurno = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Turno creado');
            $this->setData($lastIdTurno);
            $this->send();
            exit();
        }catch(TurnosException $ex){
            $this->setSuccess(false);
            $this->setHttpStatusCode(400);
            $this->addMessage($ex->getMessage());
            $this->send();
            exit();
        }catch(PDOException $ex){
            $this->setSuccess(false);
            $this->setHttpStatusCode(500);
            $this->addMessage('Falló conexión a BD');
            $this->send();
            exit();
        }
    }
}

$deptoController = new TurnoController();