<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AddUser
 *
 * @author Luca
 */
class Application_Form_AddUser extends Zend_Form {
    
    public function init()
    {
         
        $this->setMethod('post');
 
        $select = new Zend_Form_Element_Select('level', array(
            "label" => "lEVEL",
            "required" => true,
        ));
        
    
        $submit = new Zend_Form_Element_Submit('submit');
        
        $this->addElement($select, $submit);
        
    }
    
    
    
    
    
}

 
