<?php
include '../head.php';
require_once('../model/Ciudades.php');

class CiudadController extends Response{
    private $db;
    private $idCiudadController = false;
    private $idDeptoController = false;

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
        if(array_key_exists("idCiudad", $_GET)){
            $this->idCiudadController = $_GET['idCiudad'];            
            if($this->idCiudadController == '' || !is_numeric($this->idCiudadController)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de ciudad no válido");
                $this->send();
                exit;
            }
        }elseif(array_key_exists("idDeptoC", $_GET)){
            $this->idDeptoController = $_GET['idDeptoC'];
            if($this->idDeptoController == '' || !is_numeric($this->idDeptoController)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de departamento no válido");
                $this->send();
                exit;
            }
            $this->executeProcessDepto();
            exit;
        }
        $this->executeProcessCiudad();
    }

    private function executeProcessCiudad(){
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


    private function executeProcessDepto(){
        switch($_SERVER['REQUEST_METHOD']){
            case 'GET':
                $this->getRequestDepto();
                break;
            default :
                $this->setSuccess(false);
                $this->setHttpStatusCode(500); 
                $this->addMessage("Request Method no encontrado.");
                $this->send();
                break;
        }
    }

    private function getRequestDepto(){
        if(is_numeric($this->idDeptoController)){
            try {
                $query = $this->db->prepare('select id_ciudad, nom_ciudad from ciudades where id_depto = :idDepto');
                $query->bindParam(':idDepto', $this->idDeptoController);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Ningún dato asociado al departamento.");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $ciudad = new Ciudades($row['id_ciudad'], $row['nom_ciudad']);
                    $ciudadArray[] = $ciudad->returnCiudadAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['ciudades'] = $ciudadArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (CiudadesException $ex) {
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
        if(is_numeric($this->idCiudadController)){
            try {
                $query = $this->db->prepare('select id_ciudad, nom_ciudad from ciudades where id_ciudad = :idCiudad');
                $query->bindParam(':idCiudad', $this->idCiudadController);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Ciudad no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $ciudad = new Ciudades($row['id_ciudad'], $row['nom_ciudad']);
                    $ciudadArray[] = $ciudad->returnCiudadAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['ciudades'] = $ciudadArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (CiudadesException $ex) {
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
                $query = $this->db->prepare('select id_ciudad, nom_ciudad from ciudades');
                $query->execute();
                $rowCount = $query->rowCount();
                $ciudadesArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $ciudad = new Ciudades($row['id_ciudad'], $row['nom_ciudad']);
                    $ciudadesArray[] = $ciudad->returnCiudadAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['ciudades'] = $ciudadesArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(CiudadesException $ex){
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
            //Validar que el departamento no tenga ciudades relacionadas
            $query = $this->db->prepare('select count(*) as conteo from ciudades where id_ciudad = :idCiudad');
            $query->bindParam(':idCiudad', $this->idCiudadController);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar departamento. Ciudades asociadas');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('delete from ciudades where id_ciudad = :idCiudad');
            $query->bindParam(':idCiudad', $this->idCiudadController);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Ciudad no encontrado'); 
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Ciudad eliminado');
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
            if(!isset($jsonData->nomCiudad)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de ciudad es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idDepto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id departamento es obligatorio');
                $this->send();
                exit(); 
            }
            // $newCiudad = new Ciudades(null, $jsonData->nomCiudad);
            $query = $this->db->prepare('insert into ciudades (nom_ciudad,id_depto) values (:nomCiudad,:idDepto)');
            $query->bindParam(':nomCiudad', $jsonData->nomCiudad, PDO::PARAM_STR);
            $query->bindParam(':idDepto', $jsonData->idDepto, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de ciudad');
                $this->send();
                exit();
            }
            $lastIdCiudad = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Ciudad creada');
            $this->setData($lastIdCiudad);
            $this->send();
            exit();
        }catch(CiudadesException $ex){
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

$ciudad = new CiudadController();