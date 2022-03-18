<?php
include '../head.php';
require_once('../model/Categorias.php');

class CategoriaController extends Response{
    private $db;
    private $idCategoria = false;
    private $idCliente = false;

    public function __construct($idCategoria = false, $idCliente = false){
        try{
            $this->idCategoria = $idCategoria;
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

    private function init(){
        if(array_key_exists("idCategoria", $_GET)){
            $this->idCategoria = $_GET['idCategoria'];
            if($this->idCategoria == '' || !is_numeric($this->idCategoria)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Categoria no válido");
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
        if(is_numeric($this->idCategoria)){
            try {
                $query = $this->db->prepare('SELECT *,(SELECT nombre_cliente FROM cliente WHERE id = categoria.id_cliente) as nom_cliente 
                                            FROM categoria WHERE id_categoria = :idCategoria');
                $query->bindParam(':idCategoria', $this->idCategoria);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Categoria no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $cateogria = new Categoria($row['id_categoria'], $row['nombre_categoria'], $row['id_cliente'], $row['nom_cliente']);
                    $cateogriaArray[] = $cateogria->returnCategoriaAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['categorias'] = $cateogriaArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (CategoriaException $ex) {
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
                $sql = 'SELECT *,(SELECT nombre_cliente FROM cliente WHERE id = categoria.id_cliente) as nom_cliente FROM categoria';
                if($this->idCliente != false){
                    $sql .= " WHERE id_cliente = ".$this->idCliente;
                }
                $query = $this->db->prepare($sql);
                $query->execute();
                $rowCount = $query->rowCount();
                $categoriasArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $cateogria = new Categoria($row['id_categoria'], $row['nombre_categoria'], $row['id_cliente'], $row['nom_cliente']);
                    $categoriasArray[] = $cateogria->returnCategoriaAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['categorias'] = $categoriasArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(CategoriaException $ex){
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
            //Validar que el Categoria no tenga ciudades relacionadas
            $query = $this->db->prepare('SELECT count(*) as conteo FROM categoria WHERE id_categoria = :idCategoria');
            $query->bindParam(':idCategoria', $this->idCategoria);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar Categoria.');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('DELETE FROM categoria where id_categoria = :idCategoria');
            $query->bindParam(':idCategoria', $this->idCategoria);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Categoria no encontrado'); 
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Categoria eliminado');
            $this->send();
            exit();
        }catch(PDOException $ex){
            $this->setSuccess(false);
            $this->setHttpStatusCode(500);
            $this->addMessage('Error eliminando Categoria');
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
            if(!isset($jsonData->nomCategoria)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Categoria es obligatorio');
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
            // $newCategoria = new Categoria(null, $jsonData->nomCategoria);
            $query = $this->db->prepare('INSERT INTO categoria (nombre_categoria,id_cliente) values (:nomCategoria,:idCliente)');
            $query->bindParam(':nomCategoria', $jsonData->nomCategoria, PDO::PARAM_STR);
            $query->bindParam(':idCliente', $jsonData->idCliente, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Categoria');
                $this->send();
                exit();
            }
            $lastidCategoria = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Categoria creado');
            $this->setData($lastidCategoria);
            $this->send();
            exit();
        }catch(CategoriaException $ex){
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
            if(!isset($jsonData->idCategoria)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('ID de Categoria es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->nomCategoria)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Categoria es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idCliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id Cliente es obligatorio');
                $this->send();
                exit(); 
            }
            $query = $this->db->prepare('UPDATE categoria SET nombre_categoria = :nomCategoria
                                            WHERE id_categoria = :idCategoria 
                                            AND id_cliente = :idCliente');
            $query->bindParam(':nomCategoria', $jsonData->nomCategoria, PDO::PARAM_STR);
            $query->bindParam(':idCategoria',  $jsonData->idCategoria, PDO::PARAM_INT);
            $query->bindParam(':idCliente',    $jsonData->idCliente, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();

            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló actualización de Categoria');
                $this->send();
                exit();
            }
            $lastIdCliente = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Categoria Actualizada');
            $this->setData($lastIdCliente);
            $this->send();
            exit();
        }catch(CategoriaException $ex){
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

$categoriaController = new CategoriaController();