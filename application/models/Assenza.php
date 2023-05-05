<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Assenze
 *
 * @author Zack
 */
class Application_Model_Assenza {
   
    
    protected $_events = array();
 
    protected $_assenza_id;
    
    protected $_richiesta_id;
    
    protected $_user_id;
    
    protected $_sostituto_id;
    
    protected $_tipologia_id;
    
    protected $_dateStart;
    
    protected $_dateStop;
    
    protected $_giorni;        
      
    protected $_tipologia;
    
    public function __construct($id = null) {
        if(null != $id) {
            $id = (int)($id);
            if( is_int($id) ) {
                $mapper = new Application_Model_AssenzeMapper();
                $mapper->find($id, $this);
                return $this;
            }
        }
    }
    
    /**
     * 
     * @return array
     */
    public function getEvents() {
        return $this->_events;
    }
    
    public function setAssenzaId($assenza_id){
        $this->_assenza_id = $assenza_id;
        return $this;
    }
    
    public function setRichiestaId($richiesta_id){
        $this->_richiesta_id = $richiesta_id;
        return $this;
    }
    
    public function setUserId($user_id){
        $this->_user_id = $user_id;
        return $this;
    }
    
    public function setSostitutoId($sostituto_id){
        $this->_sostituto_id = $sostituto_id;
        return $this;
    }
    
    public function setTipologiaId($tipologia_id){
        $this->_tipologia_id = $tipologia_id;
        return $this;
    }
    
    public function setTipologia($id){
        $mapper = new Application_Model_TipologiaMapper();
        $this->_tipologia = $mapper->find($id);
        return $this;
    }
    
    public function getTipologia() {
        return $this->_tipologia;
    }
    
    
    public function setDateStart($dateStart){
        $this->_dateStart = $dateStart;
        return $this;
    }
    
    public function setDateStop($dateStop){
        $this->_dateStop = $dateStop;
        return $this;
    }
    
    public function setGiorni($giorni){
        
        if($giorni == 0) {
            $giorni = Application_Service_Tools::getTotalDays($this->getDateStart(), $this->getDateStop());
        }
        
        $this->_giorni = $giorni;
        return $this;
    }
    
    
    
    public function getAssenzaId(){
        return $this->_assenza_id;
    }
    
    public function getRichiestaId(){
        return $this->_richiesta_id;
    }
    
    public function getUserId(){
        return $this->_user_id;
    }
    
    public function getSostitutoId(){
        return $this->_sostituto_id;
    }
    
    public function getTipologiaId(){
        return $this->_tipologia_id;
    }
    
    public function getDateStart(){
       return $this->_dateStart; 
    }
    
    public function getDateStop(){
        return $this->_dateStop;
    }
    
    public function getGiorni() {
        return $this->_giorni;
    } 
    
    
    
    
    
}

 
