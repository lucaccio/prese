<?php

/**
 * Description of Primanota
 *
 * @author Luca
 */



class Application_Model_Primanota extends Prisma_Model_Object {
    
    
    protected $_primanota_id;
    
    protected $_sostituzione_id;
    
    protected $_descrizione;
    
    protected $_data;
    
    protected $_importo;
    
    protected $_cassa;
    
    protected $_banca;
    
    protected $_note;
    
    
    public function __construct($value = null) {
        
        
        if($value instanceof Zend_Db_Table_Rowset) {
            $value = $value->current();
        }
        
        if($value instanceof Zend_Db_Table_Row) {
            
            $this->setId($value->primanota_id);
            $this->setSostituzioneId($value->sostituzione_id);
            $this->setDescrizione($value->descrizione);
            $this->setData($value->data);
            $this->setImporto($value->importo);
            $this->setCassa($value->cassa);
            $this->setBanca($value->banca);
            $this->setNote($value->note);
                    
            return $this;
        }
        
        
    }
    
    
    
    public function setId($id) {
        return $this->setPrimanotaId($id);
    }
    
    public function setPrimanotaId($id) {
        $this->_primanota_id = (int)$id;
        return $this; 
    }
    
    public function getId() {
        return $this->getPrimanotaId();
    }
    
    public function getPrimanotaId() {
        return $this->_primanota_id;
    }
 
    public function setSostituzioneId($id) {
        $this->_sostituzione_id = (int)$id;
        return $this;
    }
    
    public function getSostituzioneId() {
        return $this->_sostituzione_id;
    }
    
    public function setDescrizione($descrizione) {
        $this->_descrizione = (string) $descrizione;
        return $this;
    }
    public function getDescrizione() {
        return $this->_descrizione;
    }
    
    public function setData($data) {
        $this->_data = $data;
        return $this;
    }
    
    public function getData($format = 'it') {
        if($format == 'it') {
            $data = Application_Service_Tools::convertDataUsToIt($this->_data);
            return $data;
        } else {
            return $this->_data;
        }
        
    }
    
    public function setImporto($value) {
        $this->_importo = $value;
        return $this;
    }
    
    public function getImporto() {
        return $this->_importo;
    }
    
    public function setCassa($value) {
        $this->_cassa = $value;
        return $this;
    }
    
    public function getCassa() {
        return $this->_cassa;
    }
    
    public function setBanca($value) {
        $this->_banca = $value;
        return $this;
    }
    
    public function getBanca() {
        return $this->_banca;
    }
    
    public function setNote($note) {
        $this->_note = (string) $note;
        return $this;
    }
    
    public function getNote() {
        
        return $this->_note;
    }
         
    
    /**
     * 
     * @param type $importo
     * @return type
     */
    protected function _formatValuta($importo) {
        return $importo;
    }
    
    
    /**
     * 
     * @param type $sostituzione_id
     * @return boolean
     */
    public function isComponentOf($sostituzione_id) {
        if($this->getSostituzioneId() === (int)($sostituzione_id)) {
            return true;
        }
        return false;
    }
    
    
    
    
    
}

 
