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
class Application_Model_DbTable_Strutture  extends Zend_Db_Table_Abstract {
 
    
    protected $_name = 'strutture';
          
    protected $_primary = 'struttura_id';
        
        
    public function __construct(){
      $this->_db = Zend_Registry::get('db');
    }
    
    
    
    
    
}

 
