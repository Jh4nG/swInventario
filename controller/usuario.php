<?php
include '../head.php';
require_once('../model/Usuarios.php');

class UsuarioController extends Response{
    private $db;
    private $idUsuario = false;
    private $contrasena = false;

    public function __construct($idUsuario = false){
        try{
            $this->idUsuario = $idUsuario;
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

    /**
     * Inicializa el proceso para determinar si continua o se detiene
     */
    private function init(){
        if(array_key_exists("idUsuario", $_GET)){
            $this->idUsuario = $_GET['idUsuario'];
            if($this->idUsuario == '' || !is_numeric($this->idUsuario)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400); 
                $this->addMessage("Id de Usuario no válido");
                $this->send();
                exit;
            }
        }
        $this->executeProcess();
    }

    /**
     * Determina el tipo de peticiones que recibe y a donde lo redirige según la petición
     * @Jhon González
     */
    private function executeProcess(){
        switch($_SERVER['REQUEST_METHOD']){
            case 'GET': // obtener usuario
                $this->getRequest();
                break;
            case 'DELETE': // Eliminar usuario
                $this->getDelete();
                break;
            case 'POST': // Setear usuario (ingresar)
                if(stristr($_SERVER['REQUEST_URI'],'/usuario/login/') !== false){ // ingreso al login
                    $this->getLogin();
                    return;   
                }
                $this->getPost();
                break;
            case 'PUT':
                $this->getUpdate();
                break;
            default : // No existe método
                $this->setSuccess(false);
                $this->setHttpStatusCode(500); 
                $this->addMessage("Request Method no encontrado.");
                $this->send();
                break;
        }
    }

    /**
     * Obtiene el usuario o los usuarios 
     * @Jhon González
     */
    private function getRequest(){
        if(is_numeric($this->idUsuario)){
            try {
                $query = $this->db->prepare('SELECT * FROM usuarios WHERE id_usuario = :idUsuario');
                $query->bindParam(':idUsuario', $this->idUsuario);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Usuario no encontrado");
                    $this->send();
                    exit;
                }
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $usuario = new Usuarios($row['id_usuario'], $row['contrasena'], $row['nombre_usuario'], $row['usuario'], $row['rol_usuario'], $row['id_cliente']);
                    $usuarioArray[] = $usuario->returnUsuarioAsArray();
                }
                $returnData = array();
                $returnData['nro_filas'] = $rowCount;
                $returnData['usuarios'] = $usuarioArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->setData($returnData);
                $this->send();
                exit;
            } catch (UsuariosException $ex) {
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
                $query = $this->db->prepare('SELECT * FROM usuarios');
                $query->execute();
                $rowCount = $query->rowCount();
                $deptosArray = array();
                while($row = $query->fetch(PDO::FETCH_ASSOC)){
                    $usuario = new Usuarios($row['id_usuario'], $row['contrasena'], $row['nombre_usuario'], $row['usuario'], $row['rol_usuario'], $row['id_cliente']);
                    $usuarioArray[] = $usuario->returnUsuarioAsArray();
                }
                $returnData = array();
                $returnData['filas_retornadas'] = $rowCount;
                $returnData['usuarios'] = $usuarioArray;
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->toCache(true);
                $this->setData($returnData);
                $this->send();
                exit;
            }catch(UsuariosException $ex){
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
            //Validar que el Usuario no tenga clientes relacionadas
            $query = $this->db->prepare('delete from usuarios where id_usuario = :idUsuario');
            $query->bindParam(':idUsuario', $this->idUsuario);
            $query->execute();
            $rowCount = $query->rowCount();
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(404);
                $this->addMessage('Usuario no encontrado');
                $this->send();
                exit();
            }
            $this->setSuccess(true);
            $this->setHttpStatusCode(200);
            $this->addMessage('Usuario eliminado');
            $this->send();
            exit();
        }catch(PDOException $ex){
            $this->setSuccess(false);
            $this->setHttpStatusCode(500);
            $this->addMessage('Error eliminando Usuario');
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
            
            if(!isset($jsonData->idUsuario)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->contrasena)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Contraseña de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->nomUsuario)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->usuario)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Usuario de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->rol)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Rol de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->id_cliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id Cliente de Usuario es obligatorio');
                $this->send();
                exit(); 
            }

            $jsonData->contrasena = password_hash($jsonData->contrasena, PASSWORD_DEFAULT);
            $query = $this->db->prepare('INSERT INTO usuarios (id_usuario, contrasena, nombre_usuario, usuario, rol_usuario, id_cliente) 
                                        values (:idUsuario, :contrasena, :nomUsuario, :usuario, :rolUsuario, :idCliente)');
            $query->bindParam(':idUsuario',  $jsonData->idUsuario, PDO::PARAM_INT);
            $query->bindParam(':contrasena', $jsonData->contrasena, PDO::PARAM_STR);
            $query->bindParam(':nomUsuario', $jsonData->nomUsuario, PDO::PARAM_STR);
            $query->bindParam(':usuario',    $jsonData->usuario, PDO::PARAM_STR);
            $query->bindParam(':rolUsuario', $jsonData->rol, PDO::PARAM_STR);
            $query->bindParam(':idCliente',   $jsonData->id_cliente, PDO::PARAM_INT);

            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló creación de Usuario');
                $this->send();
                exit();
            }
            $lastIdUsu = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Usuario creado');
            $this->setData($jsonData->idUsuario);
            $this->send();
            exit();
        }catch(UsuariosException $ex){
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

    /**
     * Ingreso por Login
     */
    private function getLogin(){
        try {
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
            if($jsonData->usuario != false && $jsonData->contrasena != false){
                $query = $this->db->prepare('SELECT * FROM usuarios WHERE usuario = :usuario');
                $query->bindParam(':usuario', $jsonData->usuario);
                $query->execute();
                $rowCount = $query->rowCount();
                if($rowCount === 0){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(404);
                    $this->addMessage("Usuario o contraseña incorrectos");
                    $this->send();
                    exit;
                }

                $obj = $query -> fetchAll(PDO::FETCH_OBJ);
                
                if (!password_verify($jsonData->contrasena,$obj[0]->contrasena)){
                    $this->setSuccess(false);
                    $this->setHttpStatusCode(403);
                    $this->addMessage("Usuario o contraseña incorrectos");
                    $this->send();
                    exit;
                }
                
                unset($obj[0]->contrasena);
                $this->setSuccess(true);
                $this->setHttpStatusCode(200);
                $this->addMessage("Ingreso Correcto");
                $this->setData($obj);
                $this->send();
                exit;
            }else{
                $this->setSuccess(false);
                $this->setHttpStatusCode(403);
                $this->addMessage("Usuario o contraseña incorrectos");
                $this->send();
                exit;
            }
        } catch (UsuariosException $ex) {
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
    }

    private function getUpdate(){
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
            
            if(!isset($jsonData->idUsuario)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->contrasena)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Contraseña de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->nomUsuario)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Nombre de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->usuario)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Usuario de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->rol)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Rol de Usuario es obligatorio');
                $this->send();
                exit(); 
            }
            if(!isset($jsonData->id_cliente)){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Id Cliente de Usuario es obligatorio');
                $this->send();
                exit(); 
            }

            $jsonData->contrasena = password_hash($jsonData->contrasena, PASSWORD_DEFAULT);
            $query = $this->db->prepare('UPDATE usuarios SET contrasena = :contrasena,
                                                             nombre_usuario = :nomUsuario, 
                                                             usuario = :usuario, 
                                                             rol_usuario = :rolUsuario,  
                                                             id_cliente = :idCliente
                                                    WHERE id_usuario =  :idUsuario');
            $query->bindParam(':idUsuario',  $jsonData->idUsuario, PDO::PARAM_INT);
            $query->bindParam(':contrasena', $jsonData->contrasena, PDO::PARAM_STR);
            $query->bindParam(':nomUsuario', $jsonData->nomUsuario, PDO::PARAM_STR);
            $query->bindParam(':usuario',    $jsonData->usuario, PDO::PARAM_STR);
            $query->bindParam(':rolUsuario', $jsonData->rol, PDO::PARAM_STR);
            $query->bindParam(':idCliente',   $jsonData->id_cliente, PDO::PARAM_INT);

            $query->execute();
            $rowCount = $query->rowCount();
            
            if($rowCount===0){
                $this->setSuccess(false);
                $this->setHttpStatusCode(400);
                $this->addMessage('Falló actualización de Usuario');
                $this->send();
                exit();
            }
            $lastIdUsu = $this->db->lastInsertId();
            $this->setSuccess(true);
            $this->setHttpStatusCode(201);
            $this->addMessage('Usuario Actualizado');
            $this->setData($jsonData->idUsuario);
            $this->send();
            exit();
        }catch(UsuariosException $ex){
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

$deptoController = new UsuarioController();