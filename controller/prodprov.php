<?php
include '../head.php';
require_once('../model/Prod_Prov.php');

class ProdProvController extends Response{
    private $db;
    private $idProdProv = false;
    private $idCliente = false;

    public function __construct($idProdProv = false, $idCliente = false){
        try{
            $this->idProdProv = $idProdProv;
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
        if(array_key_exists("idProdProv", $_GET)){
            $this->idProdProv = $_GET['idProdProv'];
            if($this->idProdProv == '' || !is_numeric($this->idProdProv)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Producto no válido");
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
        if(is_numeric($this->idProdProv)){
            try {
                $query = $this->db->prepare('SELECT *,p.nombre_producto as nom_producto,
                                            (SELECT nombre_proveedor FROM proveedor WHERE nit = pp.proveedor_nit) as nom_proveedor
                                            FROM producto_proveedor pp
                                            INNER JOIN producto p ON p.id_producto = pp.id_producto
                                            WHERE id_prod_prov = :idProdProv');
                $query->bindParam(':idProdProv', $this->idProdProv);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Registro no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $prodprov = new ProdProv($row['id_prod_prov'], $row['id_producto'], $row['nom_producto'], $row['proveedor_nit'], $row['nom_proveedor'], $row['cantidad_actual'], $row['id_cliente']);
                    $prodprovArray[] = $prodprov->returnProdProvAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['prodprov'] = $prodprovArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (ProdProvException $ex) {
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
                $sql = 'SELECT *,p.nombre_producto as nom_producto,
                        (SELECT nombre_proveedor FROM proveedor WHERE nit = pp.proveedor_nit) as nom_proveedor
                        FROM producto_proveedor pp
                        INNER JOIN producto p ON p.id_producto = pp.id_producto';
                if($this->idCliente != false){
                    $sql .= " WHERE p.id_cliente = ".$this->idCliente;
                }
                $query = $this->db->prepare($sql);
                $query->execute();
                $rowCount = $query->rowCount();
                $prodprovArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $prodprov = new ProdProv($row['id_prod_prov'], $row['id_producto'], $row['nom_producto'], $row['proveedor_nit'], $row['nom_proveedor'], $row['cantidad_actual'], $row['id_cliente']);
                    $prodprovArray[] = $prodprov->returnProdProvAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['prodprov'] = $prodprovArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(ProdProvException $ex){
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
            //Validar que el Producto no tenga ciudades relacionadas
            $query = $this->db->prepare('SELECT count(*) as conteo FROM producto_proveedor WHERE id_prod_prov = :idProdProv');
            $query->bindParam(':idProdProv', $this->idProdProv);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar el registro.');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('DELETE FROM producto_proveedor WHERE id_prod_prov = :idProdProv');
            $query->bindParam(':idProdProv', $this->idProdProv);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Producto x Proveedor no encontrado'); 
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Producto x Proveedor eliminado');
            $this->send();
            exit();
        }catch(PDOException $ex){
            $this->setSuccess(false);
            $this->setHttpStatusCode(500);
            $this->addMessage('Error eliminando Producto');
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
            if(!isset($jsonData->idProducto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id de Producto es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('id Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->cantidadActual)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Cantidad es obligatorio');
                $this->send();
                exit(); 
            }
            
            $query = $this->db->prepare('INSERT INTO producto_proveedor(id_producto,proveedor_nit,cantidad_actual) 
                                    VALUES (:idProducto,:idProveedor,:cantidadActual)');
            $query->bindParam(':idProducto', $jsonData->idProducto, PDO::PARAM_INT);
            $query->bindParam(':idProveedor', $jsonData->idProveedor, PDO::PARAM_INT);
            $query->bindParam(':cantidadActual', $jsonData->cantidadActual, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Registro');
                $this->send();
                exit();
            }
            $lastidProdProv = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Registro creado');
            $this->setData($lastidProdProv);
            $this->send();
            exit();
        }catch(ProdProvException $ex){
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
            if(!isset($jsonData->idProdProv)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id de Producto x Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idProducto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id de Producto es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idProveedor)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('id Proveedor es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->cantidadActual)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Cantidad es obligatorio');
                $this->send();
                exit(); 
            }
            $query = $this->db->prepare('UPDATE producto_proveedor 
                                            SET id_producto = :idProducto,
                                                proveedor_nit = :idProveedor,
                                                cantidad_actual = :cantidadActual
                                        WHERE id_prod_prov = :idProdProv');
            $query->bindParam(':idProdProv', $jsonData->idProdProv, PDO::PARAM_INT);
            $query->bindParam(':idProducto', $jsonData->idProducto, PDO::PARAM_INT);
            $query->bindParam(':idProveedor', $jsonData->idProveedor, PDO::PARAM_INT);
            $query->bindParam(':cantidadActual', $jsonData->cantidadActual, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();

            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló actualización de Producto x Proveedor');
                $this->send();
                exit();
            }
            $lastIdCliente = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Producto x proveedor Actualizado');
            $this->setData($lastIdCliente);
            $this->send();
            exit();
        }catch(ProdProvException $ex){
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

$productoController = new ProdProvController();