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
class Application_Model_DbTable_UsersResidui_Row extends Zend_Db_Table_Row_Abstract {
     
    
    protected $_tipo;
    
    public function init(){
        
        
       
        
    }
    
    public function __construct() {
        $this->setTipo('PROVA');
        // print_r($this);
    }
    
    
    protected function _insert() {
        #parent::_insert();
    }
    
    protected function setTipo($name) {
        $this->_tipo = $name;
    }
    
    public function getTipo() {
        return  $this->_tipo ;
    }
    
    
}

 
