<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Budget
 *
 * @author Luca
 */
class Application_Model_Budget {
    
    
    
    protected $_budget_id;
    
    protected $_sostituzione_id;
    
    protected $_importo;
    
    protected $_date;
    
    /**
     * Oggetto sostituzione
     */
    protected $_sostituzione;
    
    
    public function __construct($row = null) {
              
        $this->setBudgetId($row->budget_id);
        $this->setSostituzioneId($row->sostituzione_id);
        $this->setImporto($row->importo);
        $this->setDate($row->date);                
        $this->setSostituzione($row);
        return $this;
        
    }
    
    //
    // SETTERS
    //
    public function setBudgetId($id) {
        $this->_budget_id = $id;
        return $this;
                
    }
    
    public function setSostituzioneId($id) {
        $this->_sostituzione_id = $id;
        return $this;
    }
    
    public function setImporto($importo) {
        $this->_importo = $importo;
        return $this;
    }
    
    public function setDate($date) {
        $this->_date = $date;
        return $this;
    }
    
    public function setSostituzione($data) {
        $this->_sostituzione = new Application_Model_Sostituzione($data);
        return $this;
    }
    
    
    //
    //  GETTERS
    //
    public function getBudgetId() {
        return $this->_budget_id;
    }
    
    public function getSostituzioneId() {
        return $this->_sostituzione_id;
    }
    
    public function getImporto() {
        return $this->_importo;
    }
    
    public function getDate() {
        return $this->_date;
    }
    
    public function getSostituzione() {
        return $this->_sostituzione;
    }
    
    
    
    
    
    
    
    
    
}

 
