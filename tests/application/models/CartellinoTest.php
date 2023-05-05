<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CartellinoTest
 *
 * @author Luca
 */
class CartellinoTest extends PHPUnit_Framework_TestCase {  
    
    public function setUp()
    {
        //define('APPLICATION_ENV', 'testing');
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }
    
  
  
    
}
