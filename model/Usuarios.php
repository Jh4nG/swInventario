<?php 
class UsuariosException extends Exception {}

class Usuarios {
    private $idUsuario;
    private $contrasena;
    private $nomUsuario;
    private $usuario;
    private $rolUsuario;
    private $cliente;

    public function __construct($idUsuario,$contrasena,$nomUsuario,$usuario,$rolUsuario,$cliente){
        $this->idUsuario   = $idUsuario;
        $this->contrasena  = $contrasena;
        $this->nomUsuario  = $nomUsuario;
        $this->usuario     = $usuario;
        $this->rolUsuario  = $rolUsuario;
        $this->cliente     = $cliente;
    }

    public function getIdUsuario(){
        return $this->idUsuario;
    }

    public function setIdUsuario($idUsuario){
        if($idUsuario !== null && !is_numeric($idUsuario)){
            throw new UsuariosException("Error en Id del Usuario");
        }
        $this->idUsuario = $idUsuario;
    }

    public function getNomUsuario(){
        return $this->nomUsuario;
    }

    public function setNomUsuario($nomUsuario){
        if($nomUsuario !== null){
            throw new UsuariosException("Error en el nombre del Usuario");
        }
        $this->nomUsuario = $nomUsuario;
    }    

    public function getContrasena(){
        return $this->contrasena;
    }

    public function setContrasena($contrasena){
        if($contrasena !== null){
            throw new UsuariosException("Error en la contraseña del Usuario");
        }
        $this->contrasena = $contrasena;
    }
    
    public function getUsuario(){
        return $this->usuario;
    }

    public function setUsuario($usuario){
        if($usuario !== null){
            throw new UsuariosException("Error en el usuario del Usuario");
        }
        $this->usuario = $usuario;
    } 

    public function getRolUsuario(){
        return $this->rolUsuario;
    }

    public function setRolUsuario($rolUsuario){
        if($rolUsuario !== null){
            throw new UsuariosException("Error en el rol del Usuario");
        }
        $this->rolUsuario = $rolUsuario;
    } 

    public function getCliente(){
        return $this->cliente;
    }

    public function setCliente($cliente){
        if($cliente !== null){
            throw new UsuariosException("Error en el id cliente del Usuario");
        }
        $this->cliente = $cliente;
    } 

    public function returnUsuarioAsArray(){
        $usuario = array();
        $usuario['idUsuario'] = $this->getIdUsuario();
        $usuario['nombre'] = $this->getNomUsuario();
        $usuario['contrasena'] = $this->getContrasena();
        $usuario['usuario'] = $this->getUsuario();
        $usuario['rol'] = $this->getRolUsuario();
        $usuario['cliente'] = $this->getCliente();
        return $usuario;
    }
}
?>