<?php
include '../head.php';
require_once('../model/Servicios.php');

class ServicioController extends Response{
    private $db;
    private $idServicioController = false;
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
        if(array_key_exists("idServicio", $_GET)){
            $this->idServicioController = $_GET['idServicio'];            
            if($this->idServicioController == '' || !is_numeric($this->idServicioController)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Servicio no válido");
                $this->send();
                exit;
            }
        }elseif(array_key_exists("idSucursal", $_GET)){
            $this->idSucursalController = $_GET['idSucursal'];
            if($this->idSucursalController == '' || !is_numeric($this->idSucursalController)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Sucursal no válido");
                $this->send();
                exit;
            }
            $this->executeProcessSucursal();
            exit;
        }
        $this->executeProcessServicio();
    }

    private function executeProcessServicio(){
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


    private function executeProcessSucursal(){
        switch($_SERVER['REQUEST_METHOD']){
            case 'GET':
                $this->getRequestSucursal();
                break;
            default :
                $this->setSuccess(false);
                $this->setHttpStatusCode(500); 
                $this->addMessage("Request Method no encontrado.");
                $this->send();
                break;
        }
    }

    private function getRequestSucursal(){
        if(is_numeric($this->idSucursalController)){
            try {
                $query = $this->db->prepare('select id_servicio, nom_servicio, valor_servicio, tiempo_servicio from servicios where id_sucursal = :idSucursal');
                $query->bindParam(':idSucursal', $this->idSucursalController);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Servicio no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Servicio = new Servicios($row['id_servicio'], $row['nom_servicio'], $row['valor_servicio'], $row['tiempo_servicio']);
                    $ServicioArray[] = $Servicio->returnServicioAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['servicios'] = $ServicioArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (ServicioesException $ex) {
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
            $this->setSuccess(false);
            $this->setHttpStatusCode(500); 
            $this->addMessage("No existe en id de Departamento.");
            $this->send();
            exit;
        }
    }

    private function getRequest(){
        if(is_numeric($this->idServicioController)){
            try {
                $query = $this->db->prepare('select id_servicio, nom_servicio, valor_servicio, tiempo_servicio from servicios where id_servicio = :idServicio');
                $query->bindParam(':idServicio', $this->idServicioController);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Servicio no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Servicio = new Servicios($row['id_servicio'], $row['nom_servicio'], $row['valor_servicio'], $row['tiempo_servicio']);
                    $ServicioArray[] = $Servicio->returnServicioAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['servicios'] = $ServicioArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (ServicioesException $ex) {
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
                $query = $this->db->prepare('select id_servicio, nom_servicio, valor_servicio, tiempo_servicio from servicios');
                $query->execute();
                $rowCount = $query->rowCount();
                $ServicioesArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Servicio = new Servicios($row['id_servicio'], $row['nom_servicio'], $row['valor_servicio'], $row['tiempo_servicio']);
                    $ServicioesArray[] = $Servicio->returnServicioAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['servicios'] = $ServicioesArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(ServicioesException $ex){
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
            //Validar que el departamento no tenga Servicioes relacionadas
            $query = $this->db->prepare('select count(*) as conteo from servicios where id_servicio = :idServicio');
            $query->bindParam(':idServicio', $this->idServicioController);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar Servicio. Servicioes asociadas');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('delete from servicios where id_servicio = :idServicio');
            $query->bindParam(':idServicio', $this->idServicioController);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Servicio no encontrado'); 
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Servicio eliminado');
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
            if(!isset($jsonData->nomServicio)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Servicio es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->valorServicio)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Valor de Servicio es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->tiempoServicio)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Tiempo de Servicio es obligatorio');
                $this->send();
                exit();
            }
            if(!isset($jsonData->idSucursal)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id Sucursal es obligatorio');
                $this->send();
                exit(); 
            }
            // $newServicio = new Servicios(null, $jsonData->nomServicio);
            $query = $this->db->prepare('insert into servicios (nom_servicio,valor_servicio,id_sucursal) values (:nomServicio,:valorServicio,:tiempoServicio,:idSucursal)');
            $query->bindParam(':nomServicio', $jsonData->nomServicio, PDO::PARAM_STR);
            $query->bindParam(':valorServicio', $jsonData->valorServicio, PDO::PARAM_INT);
            $query->bindParam(':tiempoServicio', $jsonData->tiempoServicio, PDO::PARAM_INT);
            $query->bindParam(':idSucursal', $jsonData->idSucursal, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Servicio');
                $this->send();
                exit();
            }
            $lastIdServicio = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Servicio creada');
            $this->setData($lastIdServicio);
            $this->send();
            exit();
        }catch(ServicioesException $ex){
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

$Servicio = new ServicioController();