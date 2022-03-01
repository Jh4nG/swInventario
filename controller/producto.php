<?php
include '../head.php';
require_once('../model/Productos.php');

class ProductoController extends Response{
    private $db;
    private $idProducto = false;
    private $idCliente = false;

    public function __construct($idProducto = false, $idCliente = false){
        try{
            $this->idProducto = $idProducto;
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
        if(array_key_exists("idProducto", $_GET)){
            $this->idProducto = $_GET['idProducto'];
            if($this->idProducto == '' || !is_numeric($this->idProducto)){
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
        if(is_numeric($this->idProducto)){
            try {
                $query = $this->db->prepare('SELECT *,
                                            (SELECT nombre_categoria FROM categoria WHERE id_categoria = producto.id_categoria) as nom_categoria,
                                            (SELECT nombre_cliente FROM cliente WHERE id = producto.id_cliente) as nom_cliente
                                            FROM producto WHERE id_producto = :idProducto');
                $query->bindParam(':idProducto', $this->idProducto);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Producto no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $producto = new Producto($row['id_producto'], $row['nombre_producto'], $row['descripcion'], $row['precio'], $row['id_categoria'], $row['nom_categoria'], $row['id_cliente'], $row['nom_cliente']);
                    $productoArray[] = $producto->returnProductoAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['productos'] = $productoArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (ProductoException $ex) {
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
                $sql = 'SELECT *,
                            (SELECT nombre_categoria FROM categoria WHERE id_categoria = producto.id_categoria) as nom_categoria,
                            (SELECT nombre_cliente FROM cliente WHERE id = producto.id_cliente) as nom_cliente FROM producto';
                if($this->idCliente != false){
                    $sql .= " WHERE id_cliente = ".$this->idCliente;
                }
                $query = $this->db->prepare($sql);
                $query->execute();
                $rowCount = $query->rowCount();
                $productosArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $producto = new Producto($row['id_producto'], $row['nombre_producto'], $row['descripcion'], $row['precio'], $row['id_categoria'], $row['nom_categoria'], $row['id_cliente'], $row['nom_cliente']);
                    $productosArray[] = $producto->returnProductoAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['productos'] = $productosArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(ProductoException $ex){
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
            $query = $this->db->prepare('SELECT count(*) as conteo FROM producto WHERE id_producto = :idProducto');
            $query->bindParam(':idProducto', $this->idProducto);
            $query->execute();
            while($row = $query->fetch(PDO::FETCH_ASSOC)){
                $numRows = $row['conteo']; 
            }
            if($numRows == 0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(500);
                $this->addMessage('No es posible eliminar producto.');
                $this->send();
                exit();
            }
            $query = $this->db->prepare('DELETE FROM producto where id_producto = :idProducto');
            $query->bindParam(':idProducto', $this->idProducto);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Producto no encontrado'); 
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Producto eliminado');
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
            if(!isset($jsonData->nomProducto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Producto es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->descProducto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Descipción de Producto es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->precioProducto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Precio es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idCategoria)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('id Categoria es obligatorio');
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
            // $newProducto = new Producto(null, $jsonData->nomProducto);
            $query = $this->db->prepare('INSERT INTO producto (nombre_producto,descripcion,precio,id_categoria,id_cliente) values (:nomProducto,:descProducto,:precioProducto,:idCategoria,:idCliente)');
            $query->bindParam(':nomProducto', $jsonData->nomProducto, PDO::PARAM_STR);
            $query->bindParam(':descProducto', $jsonData->descProducto, PDO::PARAM_STR);
            $query->bindParam(':precioProducto', $jsonData->precioProducto, PDO::PARAM_STR);
            $query->bindParam(':idCategoria', $jsonData->idCategoria, PDO::PARAM_INT);
            $query->bindParam(':idCliente', $jsonData->idCliente, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Producto');
                $this->send();
                exit();
            }
            $lastidProducto = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Producto creado');
            $this->setData($lastidProducto);
            $this->send();
            exit();
        }catch(ProductoException $ex){
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
            if(!isset($jsonData->idProducto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id de Producto es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->nomProducto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Producto es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->descProducto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Descipción de Producto es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->precioProducto)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Precio es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->idCategoria)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('id Categoria es obligatorio');
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
            $query = $this->db->prepare('UPDATE producto SET nombre_producto = :nomProducto,
                                            descripcion = :descProducto,
                                            precio = :precioProducto
                                            WHERE id_producto = :idProducto 
                                            AND id_cliente = :idCliente
                                            AND id_categoria = :idCategoria');
            $query->bindParam(':idProducto', $jsonData->idProducto, PDO::PARAM_INT);
            $query->bindParam(':nomProducto', $jsonData->nomProducto, PDO::PARAM_STR);
            $query->bindParam(':descProducto', $jsonData->descProducto, PDO::PARAM_STR);
            $query->bindParam(':precioProducto', $jsonData->precioProducto, PDO::PARAM_STR);
            $query->bindParam(':idCategoria', $jsonData->idCategoria, PDO::PARAM_INT);
            $query->bindParam(':idCliente', $jsonData->idCliente, PDO::PARAM_INT);
            $query->execute();
            $rowCount = $query->rowCount();

            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló actualización de Producto');
                $this->send();
                exit();
            }
            $lastIdCliente = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Producto Actualizado');
            $this->setData($lastIdCliente);
            $this->send();
            exit();
        }catch(ProductoException $ex){
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

$productoController = new ProductoController();