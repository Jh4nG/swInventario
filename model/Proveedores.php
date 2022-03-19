<?php 
class ProvException extends Exception {}

class Prov {
    private $nitProv;
    private $nomProv;
    private $dirProv;
    private $telProv;
    private $emailProv;
    private $cliente;

    public function __construct($nitProv, $nomProv, $dirProv, $telProv, $emailProv, $cliente){
        $this->nitProv = $nitProv;
        $this->nomProv = $nomProv;
        $this->dirProv = $dirProv;
        $this->telProv = $telProv;
        $this->emailProv = $emailProv;
        $this->cliente = $cliente;
    }

    public function getNitProv(){
        return $this->nitProv;
    }

    public function setNitProv($nitProv){
        if($nitProv !== null && !is_numeric($nitProv)){
            throw new ProvException("Error en Id de la Prov");
        }
        $this->nitProv = $nitProv;
    }

    public function getNomProv(){
        return $this->nomProv;
    }

    public function setNomProv($nomProv){
        if($nomProv !== null){
            throw new ProvException("Error en nombre de la Prov");
        }
        $this->nomProv = $nomProv;
    }

    public function getDirProv(){
        return $this->dirProv;
    }

    public function setDirProv($dirProv){
        if($dirProv !== null){
            throw new ProvException("Error en direccion de la Prov");
        }
        $this->dirProv = $dirProv;
    }

    public function getTelProv(){
        return $this->telProv;
    }

    public function setTelProv($telProv){
        if($telProv !== null){
            throw new ProvException("Error en telefono de la Prov");
        }
        $this->telProv = $telProv;
    }

    public function getEmailProv(){
        return $this->emailProv;
    }

    public function setEmailProv($emailProv){
        if($emailProv !== null){
            throw new ProvException("Error en email de la Prov");
        }
        $this->emailProv = $emailProv;
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

    public function returnProvAsArray(){
        $Prov = array();
        $Prov['nitProv'] = $this->getNitProv();
        $Prov['nomProv'] = $this->getNomProv();
        $Prov['dirProv'] = $this->getDirProv();
        $Prov['telProv'] = $this->getTelProv();
        $Prov['emailProv'] = $this->getEmailProv();
        $Prov['cliente'] = $this->getCliente();
        return $Prov;
    }
}
?>