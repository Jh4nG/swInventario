<?php
include '../head.php';
require_once('../model/Clientes.php');

class ClienteController extends Response{
    private $db;
    private $idClienteController = false;

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
        if(array_key_exists("idCliente", $_GET)){
            $this->idClienteController = $_GET['idCliente'];            
            if($this->idClienteController == '' || !is_numeric($this->idClienteController)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Cliente no válido");
                $this->send();
                exit;
            }
        }
        $this->executeProcessCliente();
    }

    private function executeProcessCliente(){
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
            case 'PUT':
                $this->getUpdate();
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
        if(is_numeric($this->idClienteController)){
            try {
                $query = $this->db->prepare('SELECT * FROM cliente WHERE id = :idCliente');
                $query->bindParam(':idCliente', $this->idClienteController);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Cliente no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Cliente = new Clientes($row['id'], $row['nombre_cliente'], $row['nit_cliente'], $row['contacto_cliente']);
                    $ClienteArray[] = $Cliente->returnClienteAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['clientes'] = $ClienteArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (ClientesException $ex) {
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
                $query = $this->db->prepare('SELECT * FROM cliente');
                $query->execute();
                $rowCount = $query->rowCount();
                $ClientesArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Cliente = new Clientes($row['id'], $row['nombre_cliente'], $row['nit_cliente'], $row['contacto_cliente']);
                    $ClientesArray[] = $Cliente->returnClienteAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['clientes'] = $ClientesArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(ClientesException $ex){
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
            //Validar que el departamento no tenga Clientes relacionadas
            $query = $this->db->prepare('SELECT count(*) as conteo from cliente where id = :idCliente');
            $query->bindParam(':idCliente', $this->idClienteController);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar cliente');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('DELETE FROM cliente where id = :idCliente');
            $query->bindParam(':idCliente', $this->idClienteController);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Cliente no encontrado'); 
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Cliente eliminado');
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
            if(!isset($jsonData->nomCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Cliente es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->nitCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nit Cliente es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->contactoCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Contacto es obligatorio');
                $this->send();
                exit(); 
            }
            
            $query = $this->db->prepare('INSERT INTO cliente (nombre_cliente,nit_cliente,contacto_cliente) values (:nomCliente,:nitCliente,:contactoCliente)');
            $query->bindParam(':nomCliente', $jsonData->nomCliente, PDO::PARAM_STR);
            $query->bindParam(':nitCliente', $jsonData->nitCliente, PDO::PARAM_INT);
            $query->bindParam(':contactoCliente', $jsonData->contactoCliente, PDO::PARAM_STR);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Cliente');
                $this->send();
                exit();
            }
            $lastIdCliente = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Cliente creado');
            $this->setData($lastIdCliente);
            $this->send();
            exit();
        }catch(ClientesException $ex){
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

    private function getUpdate()
    {
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
            if(!isset($jsonData->idCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Cliente es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->nomCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Cliente es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->nitCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nit Cliente es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->contactoCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Contacto es obligatorio');
                $this->send();
                exit(); 
            }
            
            $query = $this->db->prepare('UPDATE cliente SET nombre_cliente = :nomCliente,
                                                            nit_cliente = :nitCliente,
                                                            contacto_cliente = :contactoCliente
                                                    WHERE   id = :idCliente');
            $query->bindParam(':nomCliente', $jsonData->nomCliente, PDO::PARAM_STR);
            $query->bindParam(':nitCliente', $jsonData->nitCliente, PDO::PARAM_INT);
            $query->bindParam(':contactoCliente', $jsonData->contactoCliente, PDO::PARAM_STR);
            $query->bindParam(':idCliente', $jsonData->idCliente, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló actualización de Cliente');
                $this->send();
                exit();
            }
            $lastIdCliente = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Cliente Actualizado');
            $this->setData($lastIdCliente);
            $this->send();
            exit();
        }catch(ClientesException $ex){
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

$Cliente = new ClienteController();