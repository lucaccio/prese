<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Strutture
 *
 * @author Luca
 */
class Application_Model_Struttura {
    
    
    
    protected $_struttura_id;
    
    protected $_denominazione;
    
    protected $_citta;
    
    protected $_indirizzo;
    
    protected $_telefono;
    
    protected $_email;
    
    protected $_coordinate;
    
    protected $_count;
    
    /**
     * 
     * @param type $id
     * @return \Application_Model_Struttura
     */
    public function __construct($id) {
        
        $m = new Application_Model_StruttureMapper();
        $row = $m->find($id);
        if($row) {
            $this->setDenominazione($row->denominazione);
            $this->setCitta($row->citta);
            $this->setId($row->struttura_id);
            $this->setIndirizzo($row->indirizzo);
            $this->_count = 1;
            return $this;
        }
        else {
            $this->_count = 0;
            return $this;
        }
    }
    
    public function setId($id) {
        $this->_struttura_id = $id;
        return $this;
    }
    
    public function getId() {
        return $this->_struttura_id ;
    }
    
    public function setDenominazione($data) {
        $this->_denominazione = $data;
        return $this;
    }
    
    public function getDenominazione() {
        return $this->_denominazione;
    }
    
    public function setCitta($data) {
        $this->_citta = $data;
        return $this;
    }
    
    public function getCitta() {
        return $this->_citta;
    }
    
    public function setIndirizzo($data) {
        $this->_indirizzo = $data;
        return $this;
    }
    
    public function getIndirizzo() {
        return $this->_indirizzo;
    }
    
    public function count() {
        return $this->_count;
    }
    
    
}

 
