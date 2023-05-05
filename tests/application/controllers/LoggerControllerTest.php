<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServiceControllerTest
 *
 * @author Luca
 */
class LoggerControllerTest 
    extends Zend_Test_PHPUnit_ControllerTestCase 
{
    
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }
    
    /**
     * 
     */
    public function testLoggerWithArray()
    {
        $data = array(1,array(5,6,array(8,9,7)),3,4, array(a,b,c,d,e));
        $num = Prisma_Logger::logToFile($data);
        $this->assertEquals($num, 13);
    }
    
    /**
     * 
     */
    public function testLogger()
    {
        $data = "prova logger to file";
        $num = Prisma_Logger::logToFile($data);
        $this->assertEquals($num, 1);
    }
    
    
}
