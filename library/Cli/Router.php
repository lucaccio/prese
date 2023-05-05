<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Router
 *
 * @author Luca
 */
class Cli_Router extends Zend_Controller_Router_Abstract {
    
    
    
    public function route(Zend_Controller_Request_Abstract $dispatcher) {
        $getopt     = new Zend_Console_Getopt (array ());
        $args  = $getopt->getRemainingArgs();
        $action = $args[0];
        $dispatcher->setControllerName('cli');
        $dispatcher->setActionName($action);
        return $dispatcher;
    }
    
    
    
    
    public function assemble($userParams, $name = null, $reset = false, $encode = true) {
        
    }
    
    
    
    
    
    
    
    
    
    
    
}

 
