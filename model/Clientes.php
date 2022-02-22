<?php 
class ClientesException extends Exception {}

class Clientes {
    private $idCliente;
    private $nombreCliente;
    private $nitCliente;
    private $contactoCliente;

    public function __construct($idCliente, $nomCliente, $nitCliente, $contactoCliente){
        $this->idCliente = $idCliente;
        $this->nombreCliente = $nomCliente;
        $this->nitCliente = $nitCliente;
        $this->contactoCliente = $contactoCliente;
    }

    public function getIdCliente(){
        return $this->idCliente;
    }

    public function setIdCliente($idCliente){
        if($idCliente !== null && !is_numeric($idCliente)){
            throw new ClientesException("Error en Id de la Cliente");
        }
        $this->idCliente = $idCliente;
    }

    public function getNomCliente(){
        return $this->nombreCliente;
    }

    public function setNomCliente($nomCliente){
        if($nomCliente !== null && strlen($this->idCliente)>50){
            throw new ClientesException("Error en nombre de la Cliente");
        }
        $this->nombreCliente = $nomCliente;
    }

    public function getNitCliente(){
        return $this->nitCliente;
    }

    public function setNitCliente($nitCliente){
        if($nitCliente !== null){
            throw new ClientesException("Error en Nit de la Cliente");
        }
        $this->nitCliente = $nitCliente;
    }

    public function getContactoCliente(){
        return $this->contactoCliente;
    }

    public function setContactoCliente($contactoCliente){
        if($contactoCliente !== null){
            throw new ClientesException("Error en contacto de la Cliente");
        }
        $this->contactoCliente = $contactoCliente;
    }

    public function returnClienteAsArray(){
        $Cliente = array();
        $Cliente['idCliente'] = $this->getIdCliente();
        $Cliente['nomCliente'] = $this->getNomCliente();
        $Cliente['nitCliente'] = $this->getNitCliente();
        $Cliente['contactoCliente'] = $this->getContactoCliente();
        return $Cliente;
    }
}
?>