<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Luca
 */
interface Application_Model_Request_Interface {

    
    public function isValid($value);
    
    public function getMessage();
    
    
}

