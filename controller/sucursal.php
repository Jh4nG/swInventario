<?php
include '../head.php';
require_once('../model/Sucursales.php');

class SucursalController extends Response{
    private $db;
    private $idSucursalController = false;
    private $idCiudadController = false;

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
        if(array_key_exists("idSucursal", $_GET)){
            $this->idSucursalController = $_GET['idSucursal'];            
            if($this->idSucursalController == '' || !is_numeric($this->idSucursalController)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Sucursal no válido");
                $this->send();
                exit;
            }
        }elseif(array_key_exists("idCiudad", $_GET)){
            $this->idCiudadController = $_GET['idCiudad'];
            if($this->idCiudadController == '' || !is_numeric($this->idCiudadController)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de ciudad no válido");
                $this->send();
                exit;
            }
            $this->executeProcessCiudad();
            exit;
        }
        $this->executeProcessSucursal();
    }

    private function executeProcessSucursal(){
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


    private function executeProcessCiudad(){
        switch($_SERVER['REQUEST_METHOD']){
            case 'GET':
                $this->getRequestCiudad();
                break;
            default :
                $this->setSuccess(false);
                $this->setHttpStatusCode(500); 
                $this->addMessage("Request Method no encontrado.");
                $this->send();
                break;
        }
    }

    private function getRequestCiudad(){
        if(is_numeric($this->idCiudadController)){
            try {
                $query = $this->db->prepare('select id_sucursal, nom_sucursal, dir_sucursal, tel_sucursal from sucursales where id_ciudad = :idCiudad');
                $query->bindParam(':idCiudad', $this->idCiudadController);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Sucursal no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Sucursal = new Sucursal($row['id_sucursal'], $row['nom_sucursal'], $row['dir_sucursal'], $row['tel_sucursal']);
                    $SucursalArray[] = $Sucursal->returnSucursalAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['sucursales'] = $SucursalArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (SucursalesException $ex) {
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
        if(is_numeric($this->idSucursalController)){
            try {
                $query = $this->db->prepare('select id_sucursal, nom_sucursal, dir_sucursal, tel_sucursal from sucursales where id_sucursal = :idSucursal');
                $query->bindParam(':idSucursal', $this->idSucursalController);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Sucursal no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Sucursal = new Sucursal($row['id_sucursal'], $row['nom_sucursal'], $row['dir_sucursal'], $row['tel_sucursal']);
                    $SucursalArray[] = $Sucursal->returnSucursalAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['sucursales'] = $SucursalArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (SucursalesException $ex) {
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
                $query = $this->db->prepare('select id_sucursal, nom_sucursal, dir_sucursal, tel_sucursal from sucursales');
                $query->execute();
                $rowCount = $query->rowCount();
                $SucursalesArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Sucursal = new Sucursal($row['id_sucursal'], $row['nom_sucursal'], $row['dir_sucursal'], $row['tel_sucursal']);
                    $SucursalesArray[] = $Sucursal->returnSucursalAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['sucursales'] = $SucursalesArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(SucursalesException $ex){
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
            //Validar que el departamento no tenga Sucursales relacionadas
            $query = $this->db->prepare('select count(*) as conteo from sucursales where id_sucursal = :idSucursal');
            $query->bindParam(':idSucursal', $this->idSucursalController);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar sucursal. Sucursales asociadas');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('delete from sucursales where id_sucursal = :idSucursal');
            $query->bindParam(':idSucursal', $this->idSucursalController);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Sucursal no encontrado'); 
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Sucursal eliminado');
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
            if(!isset($jsonData->nomSucursal)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Sucursal es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->dirSucursal)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Direccion de Sucursal es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idCiudad)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id ciudad es obligatorio');
                $this->send();
                exit(); 
            }
            // $newSucursal = new Sucursal(null, $jsonData->nomSucursal);
            $query = $this->db->prepare('insert into sucursales (nom_sucursal,dir_sucursal,id_siudad) values (:nomSucursal,:dirSucursal,:telSucursal,:idCiudad)');
            $query->bindParam(':nomSucursal', $jsonData->nomSucursal, PDO::PARAM_STR);
            $query->bindParam(':dirSucursal', $jsonData->dirSucursal, PDO::PARAM_STR);
            $query->bindParam(':telSucursal', $jsonData->telSucursal, PDO::PARAM_INT);
            $query->bindParam(':idCiudad', $jsonData->idCiudad, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Sucursal');
                $this->send();
                exit();
            }
            $lastIdSucursal = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Sucursal creada');
            $this->setData($lastIdSucursal);
            $this->send();
            exit();
        }catch(SucursalesException $ex){
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

$Sucursal = new SucursalController();