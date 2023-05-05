<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConfigurazioniController
 *
 * @author Luca
 */
class EmailController extends Zend_Controller_Action {
     
    
    public function init() {
        //$this->_helper->_layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
    
    
    public function indexAction() {
        
    }
    
    
    public function listAction() {
        $this->_helper->viewRenderer->setNoRender(false);
    }
    
    public function addAction() {
        
    }
    
    public function updateAction() {
        
    }
    
}

 
