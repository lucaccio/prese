<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Row
 *
 * @author Luca
 */
class Application_Model_DbTable_Richieste_Row extends Zend_Db_Table_Row_Abstract {
   
    
    protected $_user;
    
    protected $_userObj;
    
    protected $_tipologia;
    
    protected $_level;    
    
    protected $_level_id;
    
    public $ruolo  ;
    
    public $cognome;
    
    public $nome;
    
    public $tipologia;
    
    //public $created_by_user_id;
    
    protected $_role_name;
    
     
    
    
    public function init() {
         
        $this->_user      = new Application_Model_UserMapper();
        $this->_tipologia = new Application_Model_TipologiaMapper();
        $this->_level     = new Application_Model_LevelMapper();
        
        $this->setUser();
        $this->setRole();
        $this->setTipologia();
         
    }
    
    public function getUserId() {
        return $this->user_id;
    }
    
    public function setUser() {
        $user            = $this->_user->find($this->user_id);
        $this->nome      = $user->getNome();
        $this->cognome   = $user->getCognome();
        $this->_level_id = $user->getLevel()->getLevelId();
        $this->_userObj  = $user;
    }
    
    public function setTipologia() {
        $this->tipologia = $this->_tipologia->find($this->tipologia_id);
        
    }
    
    /**
     * 
     */
    public function setRole() {
       
        $level = $this->_level->find($this->_level_id);
        $this->_role_name = $level->getDescrizione();
        
    }
        
    /**
     * 
     * @return type
     */
    public function getUser() {
        return $this->cognome . ' ' . $this->nome ;
    }
    
    public function getUserObj() {
        return $this->_userObj;
    }
    
    /**
     * 
     * @return type
     */
    public function getTipologia() {
        return $this->tipologia;
    }
    
    public function getRole() {
         return $this->_role_name;
    }
    
    public function getDateStart()
    {
        return new Zend_Date( $this->dateStart );
    }
    
    public function getDateStop()
    {
        return new Zend_Date( $this->dateStop );
    }
    
    public function getGiorni()
    {
        return $this->giorni;
    }
    
    public function getNoteAdmin()
    {
        return $this->note;
    }

    public function getNoteUser()
    {
        return $this->note_user;
    }
    
    public function getId(){
        return $this->richiesta_id;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    
    
}

 
