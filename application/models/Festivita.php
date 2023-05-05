<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Festivita
 *
 * @author Zack
 */
class Application_Model_Festivita {
 
    
    protected $_festivita_id;
    protected $_sede_id;
    protected $_descrizione;
    protected $_giorno;
    protected $_mese;
    protected $_lavorativo;
    protected $_nazionale;
    protected $_infrasettimanale;
    protected $_patrono;
    
    
    
    public function __construct($data = null) {
        
        if(null != $data) {
            
            if($data instanceof Zend_Db_Table_Row_Abstract) {
                $this->setId($data->festivita_id);
                $this->setSedeId($data->sede_id);
                $this->setDescrizione($data->descrizione);
                $this->setGiorno($data->giorno);
                $this->setMese($data->mese);
                $this->setLavorativo($data->lavorativo);
                $this->setNazionale($data->nazionale);
                $this->setInfrasettimanale($data->infrasettimanale);
                $this->setPatrono($data->patrono);
            }
                       
        }
    }
    
    public function setId($id) {
        $this->_festivita_id = $id;
        return $this;
    }
    
    public function setSedeId($sede) {
        $this->_sede_id = $sede;
        return $this;
    }
    public function setDescrizione($desc) {
        $this->_descrizione = $desc;
        return $this;
    }
    public function setGiorno($g) {
        $this->_giorno = $g;
        return $this;
    }
    public function setMese($m) {
        $this->_mese = $m;
        return $this;
    }
    public function setLavorativo($l) {
        $this->_lavorativo = $l;
        return $this;
    }
    public function setNazionale($n) {
        $this->_nazionale = $n;
        return $this;
    }
    public function setInfrasettimanale($i) {
        $this->_infrasettimanale = $i;
        return $this;
    }
    public function setPatrono($p) {
        $this->_patrono = $p;
        return $this;
    }
    
    
    //GETTERS
    
    public function getId() {
        return $this->_festivita_id;
    }
    public function getSedeId() {
        return $this->_sede_id;
    }
    public function getDescrizione() {
        return $this->_descrizione;
    }
    public function getGiorno() {
        return $this->_giorno;
    }
    public function getMese() {
        return $this->_mese;
    }
    public function getLavorativo() {
        return $this->_lavorativo;
    }
    public function getNazionale() {
        return $this->_nazionale;
    }
    public function getInfrasettimanale() {
        return $this->_infrasettimanale;
    }
    public function getPatrono() {
        return $this->_patrono;
    }

    public function isLavorativo() {
        if($this->_lavorativo)
        return  true;
            else return false;
    }
    
    
    
    
    
}

 
