<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PdfTest
 *
 * @author Luca
 */
class PdfTest  extends PHPUnit_Framework_TestCase {
     
    
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
    }
    
    public function testPdfConstruct()
    {
        try {
            $pdf = new Application_Model_Giornaliera_Pdf();
            $this->assertInstanceOf('Application_Model_Giornaliera_IGiornaliera', $pdf);
            return $pdf;
        } catch (Exception $ex) {
            print_r($ex);
        }
        
        
         
        /*
        $mock = $this->getMock('Application_Model_Giornaliera_IGiornaliera');
        $this->assertTrue(Application_Model_Giornaliera_IGiornaliera instanceof  $mock);
        */
    }
    
    public function testReport()
    {
        $pdf = $this->testPdfConstruct();
        
        $filename = TEST_PATH . '/pdf_test.pdf';
        $report = array(
            'Uda Luca' => array(
                3,3,3,3,"F",3,3,3,3,3,3,3,3
            ),
            'Sanna Luca' => array(
                3,3,3,3,"F",3,3,3,3,3,3,3,3
            ),
        );
        
        $pdf->fillReport($report, $filename);
    }
}
