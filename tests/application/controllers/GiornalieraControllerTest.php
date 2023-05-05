<?php

class GiornalieraControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
 
    
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/testing.ini');
        parent::setUp();
    }
    
    public function testDbInstance() 
    {
        $db = Zend_Registry::get('db');
        $this->assertInstanceOf('Zend_Db_Adapter_Abstract', $db);
    }
    
    /**
     * 
     */
    public function _testCartellino() {
        $first = '2014-01-01';
        $last  = '2014-01-31';
        $arrayOfDates = Application_Service_Tools::generateArrayOfDays($first, $last);
        //Prisma_Logger::logToFile($array);
        $arrayOfDates1 = null;
        $options = array(
            'uid'  => 37,
            'date' => $arrayOfDates 
        );
        // check
        $result = false;
        try {
            $cartellino = new Application_Model_Cartellino($options);
            Prisma_Logger::logToFile( $cartellino->creaCartellinoMensile() );
            $result = true;
        } catch (Exception $ex) {
            Prisma_Logger::logToFile( $ex->getMessage() );
        }
        $this->assertTrue($result);
    }
    
   public function getUsers()
   {
       
   }
    
    /**
     * 
     * 
     */
    public function testGiornaliera()
    {
        $first = '2014-08-01';
        $last  = '2014-08-30';
        $arrayOfDates = Application_Service_Tools::generateArrayOfDays($first, $last);
        $options['date'] = $arrayOfDates ;
    
       /*
        $UM  = new Application_Model_UserMapper();
        $us  = $UM->getAllUsers( );
        $users = array();
        $username = array();
        foreach($us as $k => $uo) {
            //$users[]    = $uo->getId();
            //$username[] = $uo->getAnagrafe();
            $i = $uo->getId();
            $users[$i]  = $uo->getAnagrafe();
        }
   */
          
        $users = array(18);
        //sort($users);
        
        //Prisma_Logger::logToFile($users);
        
        $giornaliera = new Application_Model_Giornaliera($arrayOfDates);
        foreach($users as $k => $v) 
        {
            if(!is_int($v)) {
               $options['uid']  = $k;
               $options['name'] = $v ;     
            } else {
               $options['uid']  = $v;
            }
            $result = false;
            try {
                $cartellino = new Application_Model_Cartellino($options);
                $giornaliera->append( $cartellino->creaCartellinoMensile(), false );
                $result     = true;
                $this->assertTrue($result);
            } catch (Exception $ex) {
                Prisma_Logger::logToFile( $ex->getMessage() );
                Prisma_Logger::logToFile( $ex->getTraceAsString() );
                $result = false;
                $this->assertTrue($result);
                
            }
        }
        
        if($result) {
            try {
               // $giornaliera->toTxt(TEST_PATH . '/test.txt', 'new');
                $giornaliera->toPdf(TEST_PATH . '/test.pdf');
                Prisma_Logger::logToFile(  "File generato con successo." );
            } catch(Exception $ex) {
                $result = false;
                $this->assertTrue($result);
                Prisma_Logger::logToFile( $ex->getMessage() );
            }
        }
        
        
    }
     
      public function  _testEvents() {
        
        $first = '2014-07-01';
        $last  = '2014-07-31';
        $arrayOfDates = Application_Service_Tools::generateArrayOfDays($first, $last);
        $o = array('uid' => 37, 'date' => $arrayOfDates);
        
        $c = new Application_Model_Cartellino($o);
        $c->setEvents();
        $this->assertInternalType('array',  $c->getEvents() );
        Prisma_Logger::logToFile($c->getEvents());
    }
    
    
    
}
    
    
 