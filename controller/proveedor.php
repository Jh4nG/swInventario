<?php
include '../head.php';
require_once('../model/Proveedores.php');

class ProveedorController extends Response{
    private $db;
    private $idProveedor = false;
    private $idCliente = false;

    public function __construct($idProveedor = false, $idCliente = false){
        try{
            $this->idProveedor = $idProveedor;
            $this->idCliente = $idCliente;
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
        if(array_key_exists("idProveedor", $_GET)){
            $this->idProveedor = $_GET['idProveedor'];            
            if($this->idProveedor == '' || !is_numeric($this->idProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Proveedor no válido");
                $this->send();
                exit;
            }
        }elseif(array_key_exists("idCliente", $_GET)){
            $this->idCliente = $_GET['idCliente'];
            if($this->idCliente == '' || !is_numeric($this->idCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Cliente no válido");
                $this->send();
                exit;
            }
        }
        $this->executeProcessProveedor();
    }

    private function executeProcessProveedor(){
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
        if(is_numeric($this->idProveedor)){
            try {
                $query = $this->db->prepare('SELECT * FROM proveedor WHERE nit = :idProveedor');
                $query->bindParam(':idProveedor', $this->idProveedor);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Proveedor no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Proveedor = new Prov($row['nit'], $row['nombre_proveedor'], $row['direccion_proveedor'], $row['telefono_proveedor'], $row['email'], $row['id_cliente']);
                    $ProveedorArray[] = $Proveedor->returnProvAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['Proveedores'] = $ProveedorArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (ProvException $ex) {
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
                $query = $this->db->prepare('SELECT * FROM proveedor');
                if($this->idCliente != false){
                    $query .= " WHERE id_cliente = ".$this->idCliente;
                }
                $query->execute();
                $rowCount = $query->rowCount();
                $ProveedoresArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $Proveedor = new Prov($row['nit'], $row['nombre_proveedor'], $row['direccion_proveedor'], $row['telefono_proveedor'], $row['email'], $row['id_cliente']);
                    $ProveedoresArray[] = $Proveedor->returnProvAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['Proveedores'] = $ProveedoresArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(ProvException $ex){
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
            //Validar que el proveedor exista
            $query = $this->db->prepare('SELECT count(*) AS conteo FROM proveedor WHERE nit = :idProveedor');
            $query->bindParam(':idProveedor', $this->idProveedor);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar Proveedor.');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('DELETE FROM proveedor WHERE nit = :idProveedor');
            $query->bindParam(':idProveedor', $this->idProveedor);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Proveedor no encontrado'); 
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Proveedor eliminado');
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
            if(!isset($jsonData->nitProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nit de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->nomProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->dirProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Direccion de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->telProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Telefono de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->emailProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Email de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('id Cliente es obligatorio');
                $this->send();
                exit(); 
            }
            // $newProveedor = new Prov(null, $jsonData->nomProveedor);
            $query = $this->db->prepare('INSERT INTO proveedor (nit,nombre_proveedor,direccion_proveedor,telefono_proveedor,email,id_cliente) 
                                        VALUES (:nitProveedor,:nomProveedor,:dirProveedor,:telProveedor,:emailProveedor,:idCliente)');
            $query->bindParam(':nitProveedor', $jsonData->nitProveedor, PDO::PARAM_INT);
            $query->bindParam(':nomProveedor', $jsonData->nomProveedor, PDO::PARAM_STR);
            $query->bindParam(':dirProveedor', $jsonData->dirProveedor, PDO::PARAM_STR);
            $query->bindParam(':telProveedor', $jsonData->telProveedor, PDO::PARAM_INT);
            $query->bindParam(':emailProveedor', $jsonData->emailProveedor, PDO::PARAM_STR);
            $query->bindParam(':idCliente', $jsonData->idCliente, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Proveedor');
                $this->send();
                exit();
            }
            $lastIdProveedor = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Proveedor creado');
            $this->setData($lastIdProveedor);
            $this->send();
            exit();
        }catch(ProvException $ex){
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
            if(!isset($jsonData->nitProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nit de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->nomProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->dirProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Direccion de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->telProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Telefono de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->emailProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Email de Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('id Cliente es obligatorio');
                $this->send();
                exit(); 
            }
            $query = $this->db->prepare('UPDATE proveedor SET 
                                            nombre_proveedor = :nomProveedor,
                                            direccion_proveedor = :dirProveedor,
                                            telefono_proveedor = :telProveedor,
                                            email = :emailProveedor
                                            WHERE nit = :nitProveedor 
                                            AND id_cliente = :idCliente');
            $query->bindParam(':nitProveedor', $jsonData->nitProveedor, PDO::PARAM_INT);
            $query->bindParam(':nomProveedor', $jsonData->nomProveedor, PDO::PARAM_STR);
            $query->bindParam(':dirProveedor', $jsonData->dirProveedor, PDO::PARAM_STR);
            $query->bindParam(':telProveedor', $jsonData->telProveedor, PDO::PARAM_INT);
            $query->bindParam(':emailProveedor', $jsonData->emailProveedor, PDO::PARAM_STR);
            $query->bindParam(':idCliente', $jsonData->idCliente, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();

            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló actualización de Proveedor');
                $this->send();
                exit();
            }
            $lastIdCliente = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Proveedor Actualizado');
            $this->setData($lastIdCliente);
            $this->send();
            exit();
        }catch(ProvException $ex){
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

$Proveedor = new ProveedorController();