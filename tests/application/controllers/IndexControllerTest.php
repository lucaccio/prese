<?php

class IndexControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }

    public function testIfAEgualsB()
    {
        $a = 1;
        $b = 1;
        $this->assertEquals($a,$b);
            
    }
    
    public function testDb()
    {
        
        $dsn = 'mysql:dbname=feriemanager_tests;host=192.168.1.30';
        $user = 'root';
        $password = 'prisma2013';

        try {
            $db = new PDO($dsn, $user, $password);
             
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
        
        
    }
    
    
    
}

