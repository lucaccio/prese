<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Object
 *
 * @author Luca
 */
class Prisma_Model_Object {
    
    
    

    public function __set($name, $value) {
        $method = 'set' . $name;
        if (  !method_exists($this, $method) ) {
            throw new Exception('Invalid  property');
        }
        $this->$method($value);
    }
 
    
    
    
    public function __get($name) {
        $method = 'get' . $name;
        if ( !method_exists($this, $method) ) {
            throw new Exception('Invalid   property');
        }
        return $this->$method();
    }

 
}