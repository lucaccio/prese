<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SostituzioniNote
 *
 * @author Luca
 */
class Application_Model_DbTable_SostituzioniNote extends Zend_Db_Table_Abstract {
    
    protected $_primary = 'id';
    
    protected $_name = 'sostituzioni_note';
    
    public function __construct() {
        $this->_db = Zend_Registry::get('db');
    } 
    
    
}

 
