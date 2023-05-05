<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ToolsControllerTest
 *
 * @author Luca
 */
class ToolsControllerTest 
    extends Zend_Test_PHPUnit_ControllerTestCase 

{
    
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }
        
    
    public function _testArrayOfDays()  
    {
        $first = '2014-07-01';
        $last  = '2014-07-31';
        $search_value = 'D';
        $num_of_keys  = 4;
        $array  = Application_Service_Tools::generateArrayOfDays($first, $last);
        $arrayElements = Prisma_Logger::logToFile( array('testArrayOfDays',$array) );
        $result = array_keys($array, $search_value);
        try {
            
        } catch (Exception $ex) {
            Prisma_Logger::logToFile($ex->getMessage());
        }
        $this->assertCount($num_of_keys, $result);
        $this->assertEquals($arrayElements , 32);
    }
    
     
    public function testArray()
    {
        $val = 12;
        $var = array();
        $this->assertTrue( Prisma_Tool_Array::isArray($var) );
        $this->assertTrue( !Prisma_Tool_Array::isArray($val) );
        //$this->assertTrue( !Prisma_Tool_Array::isArray($var) );
       //$this->assertTrue( Prisma_Tool_Array::validateArray($var) );
       //$this->assertTrue( Prisma_Tool_Array::validateArray($val) );
       // $this->assertTrue( is_array($var));
       //  $this->assertTrue( !is_array($var));
    }
    
    
}