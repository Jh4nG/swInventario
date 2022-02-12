<?php
include '../head.php';
require_once('../model/Horarios.php');

class HorariosController extends Response{
    private $db;
    private $idHorariosController = false;
    private $idSucursalController = false;

    public function __construct(){
        try{
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

    private function init (){
        if(array_key_exists("idHorarios", $_GET)){
            $this->idHorariosController = $_GET['idHorarios'];            
            if($this->idHorariosController == '' || !is_numeric($this->idHorariosController)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Horarios no válido");
                $this->send();
                exit;
            }
        }
        $this->executeProcessHorarios();
    }

    private function executeProcessHorarios(){
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
        if(is_numeric($this->idHorariosController)){
            try {
                $query = $this->db->prepare('select id_horario, horario from horarios where id_horario = :idHorarios');
                $query->bindParam(':idHorarios', $this->idHorariosController);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Horarios no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Horarios = new Horarios($row['id_horario'], $row['horario']);
                    $HorariosArray[] = $Horarios->returnHorariosAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['horarios'] = $HorariosArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (HorariosesException $ex) {
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
                $query = $this->db->prepare('select id_horario, horario from horarios');
                $query->execute();
                $rowCount = $query->rowCount();
                $HorariosesArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Horarios = new Horarios($row['id_horario'], $row['horario']);
                    $HorariosesArray[] = $Horarios->returnHorariosAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['horarios'] = $HorariosesArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(HorariosesException $ex){
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
            //Validar que el departamento no tenga Horarioses relacionadas
            $query = $this->db->prepare('select count(*) as conteo from horarios where id_horario = :idHorarios');
            $query->bindParam(':idHorarios', $this->idHorariosController);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar Horarios. Horarios asociadas');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('delete from horarios where id_horario = :idHorarios');
            $query->bindParam(':idHorarios', $this->idHorariosController);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Horarios no encontrado'); 
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Horarios eliminado');
            $this->send();
            exit();
        }catch(PDOException $ex){
            $this->setSuccess(false);
            $this->setHttpStatusCode(500);
            $this->addMessage('Error eliminando departamento');
            $this->send();
            exit();
        }
    }

    private function getPost(){
        try{
            if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
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
            if(!isset($jsonData->horarios)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Horarios es obligatorio');
                $this->send();
                exit(); 
            }
            // $newHorarios = new Horarios(null, $jsonData->nomHorarios);
            $query = $this->db->prepare('insert into horarios (horario) values (:horarios)');
            $query->bindParam(':horarios', $jsonData->horarios, PDO::PARAM_STR);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Horarios');
                $this->send();
                exit();
            }
            $lastIdHorarios = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Horarios creada');
            $this->setData($lastIdHorarios);
            $this->send();
            exit();
        }catch(HorariosesException $ex){
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

$Horarios = new HorariosController();