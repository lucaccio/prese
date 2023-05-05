<?php

class GiornalieraControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

    public $_arrayOfDates = array();
    
    public $_user = null;
    
    public $_first_day = '2014-06-01';
    
    public $_last_day = '2014-06-30';
    
    public function setUp()
    {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
        parent::setUp();
        
        //$this->_arrayOfDates = $this->arrayCreateRangeOfDates();
        //$this->_user = $this->createUser();
    }

    /**
     * 
     * @deprecated
     */
    public function arrayCreateRangeOfDates()
    {
        $start = new Zend_Date($this->_first_day, Zend_Date::ISO_8601);
        $stop  = new Zend_Date($this->_last_day, Zend_Date::ISO_8601);
        
        $this->assertTrue( $start->toString() <  $stop->toString()  );
        $this->assertTrue( $start->toString() <= $stop->toString()  );
        $this->assertTrue( $stop->toString()  >= $start->toString() );
        
        $days = array( );
        for ($i = $start ; $i <= $stop ; $i = $start->addDay('1') ) {
            $days[$i->toString()] = NULL;
        }
        return $days;
    }    
    
    public function _createUser()
    {
        $uid  = 37;
        $map  = new Application_Model_UserMapper();
        $user = $map->find($uid);
        return $user;
    }
    
    public function _testUserInstanceOf()
    {
        $this->assertInstanceOf('Application_Model_User', $this->_user);
    }
    
    public function _testCreateContracts()
    {
        $uid  = 37;
        $map  = new Application_Model_UserMapper();
        $user = $map->find($uid);
        
        //$contratti = $user->getHistoricalContracts();
        //$this->assertTrue();
        $data = array(
            'contratto_id' => 2,
            'user_id'      => $uid,
            'start'        => '2014-06-01',
            'stop'         => '2014-06-30',
            'last'         => 1
        );
        
    }
    
    public function _testNotEmptyArray() 
    {
        $this->assertNotEmpty($this->_arrayOfDates) ; 
    }
       
    public function _testCountArray() 
    {
        $this->assertCount(30, $this->_arrayOfDates); 
    }    
        
    public function _testLastContract()
    {
        $contratto = $this->_user->getLastInsertedContract();
        $this->assertInstanceOf('stdClass', $contratto );
         
    }        
    
    public function _testGenerate1()
    {
        $inizio_mese = false;
        $fine_mese = false;
        $contratto = $this->_user->getLastInsertedContract();
        
        if($contratto->stop === null) {
            $contratto->stop = '2014-12-31';
        }
        
        # passo 1
        if( ($contratto->stop >= $this->_last_day) ) {
            $fine_mese = true;
        } else {
            $cStop = new Zend_Date($contratto->stop, Zend_Date::ISO_8601);
            $gStop = new Zend_Date($this->_last_day, Zend_Date::ISO_8601);
            if($cStop->getMonth() == $gStop->getMonth()) {
                 $fine_mese = true;
            }
        }
        
        if($fine_mese) {
            $cStart = new Zend_Date($contratto->start, Zend_Date::ISO_8601);
            $gStart = new Zend_Date($this->_first_day, Zend_Date::ISO_8601);
            if( $cStart <= $gStart ) {
               $inizio_mese = true; 
            } else {
                if($cStart->getMonth() == $gStart->getMonth()) {
                    $inizio_mese = true; 
                    # qui funzione ricorsiva con nuove date da cercare:
                    # start = primo del mese
                    # stop = $cStart->subDay(1);
                    
                } 
            }
        }
        $number = range($cStart->getTimestamp(),$cStop->getTimestamp(),86400);
        print_r ($number);
        $this->assertTrue($inizio_mese);
        $this->assertTrue($fine_mese);
        
    }        
     
        
    public function _testRange()
    { 
        $range = 86400;
        
        $fillArray = array(
            'mattina' => null,
            'sera'    => null,
            'assenza' => null
        );
        
        $aStart = new Zend_Date('2014-06-01',Zend_Date::ISO_8601);
        $aStop  = new Zend_Date('2014-06-30',Zend_Date::ISO_8601);
        
        $bStart = new Zend_Date('2014-06-01',Zend_Date::ISO_8601);
        $bStop  = new Zend_Date('2014-06-10',Zend_Date::ISO_8601);
        
        $cStart = new Zend_Date('2014-06-25',Zend_Date::ISO_8601);
        $cStop  = new Zend_Date('2014-06-30',Zend_Date::ISO_8601);
        
        $aRange = range($aStart->getTimestamp(), $aStop->getTimestamp(), $range);
        $bRange = range($bStart->getTimestamp(), $bStop->getTimestamp(), $range);
        $cRange = range($cStart->getTimestamp(), $cStop->getTimestamp(), $range);
        
        //$aRange = array_flip($aRange);
        //$bRange = array_flip($bRange);
        //$cRange = array_flip($cRange);
        $diff   = array_diff($aRange, $bRange,  $cRange ) ;
        
        # tests
        $this->assertCount(30, $aRange);
        $this->assertCount(10, $bRange);
        $this->assertCount(6, $cRange);
        $this->assertCount(14, $diff);
        print_r($diff);
        
        
        
        
        // SELECT * FROM `users_contracts` WHERE MONTH(start) <= 6 AND MONTH(stop) >= 6 AND user_id = 37 ORDER BY start DESC
        // SELECT * FROM `users_contracts` WHERE   CURDATE() BETWEEN start AND  stop 
        
        // SELECT * FROM `users_contracts` WHERE  MONTH(CURDATE()) BETWEEN MONTH(start) AND  MONTH(stop)
        // SELECT * FROM `users_contracts` WHERE  MONTH('2014-03-01') BETWEEN MONTH(start) AND  MONTH(stop)
        
        // SELECT * FROM `users_contracts` WHERE   '2014-03-01'  BETWEEN start AND  stop
        // SELECT * FROM `users_contracts` WHERE   (('2014-06-01'  BETWEEN start AND  stop) OR ('2014-06-30'  BETWEEN start AND  stop) ) AND user_id=37
        
        //SELECT * FROM `users_contracts` WHERE (DATE_FORMAT('2014-06-01','%Y-%m')  BETWEEN DATE_FORMAT(start,'%Y-%m') AND  DATE_FORMAT(stop,'%Y-%m'))   AND user_id='37'
        
        
        
       // SELECT * FROM `users_contracts` WHERE (DATE_FORMAT('2014-06-01','%Y-%m') BETWEEN DATE_FORMAT(start,'%Y-%m') AND  ( IF( stop IS NULL,DATE_FORMAT('2099-12-31','%Y-%m'),DATE_FORMAT(stop,'%Y-%m')  ) ) )  AND user_id='37' ORDER BY start DESC 
        
        
        //eventi 
        //SELECT * FROM `eventi` WHERE DATE_FORMAT(giorno, '%Y-%m') = '2014-06' AND user_id = 37
        
    }
    
    /**
     * 
     * Creo un array multidimensionale con, per ogni array, la data e il nome abbreviato del giorno
     * 
     * @param DateTime $start
     * @param DateTime $stop
     * @param type $step
     * @param type $multiarray
     * @return type
     */
    public function createRangeOfDates($start, $stop, $step = null, $multiarray = false)
    {
        
       // $UC = new Application_Model_Cartellino();
        //return $UC->createRangeOfDates($start, $stop, $step = null, $multiarray = false);
        
        /* 
        (!$step) ? $step = 86400 : $step ;
        $aStart = new Zend_Date($start, Zend_Date::ISO_8601);
        $aStop  = new Zend_Date($stop, Zend_Date::ISO_8601);
        $aRange = range($aStart->getTimestamp(), $aStop->getTimestamp(), $step);
               
        return  $aRange  ;
        */
        
        $val = array(); 
        $interval = new DateInterval("P1D");  
        $start    = new DateTime($start);
        $stop     = new DateTime($stop);
        $stop->add(new DateInterval('P1D'));
        $period   = new DatePeriod($start, $interval, $stop);
        foreach ( $period as $dt )
        {
            if($multiarray) {
                $value = array(
                    'iso8610'     => $dt->format( "Y-m-d" ),
                    'day_of_week' => strtolower( $dt->format('D') )
                );
                $val[] = $value;
            } else {
                $val[] = $dt->format( "Y-m-d" );
            }
             
        }
         
        //print_r($val); 
        return $val;
    }
     
    
    public function  _testIfInRange()
    {
        $a   = '2014-06-01' ;
        $b   = '2014-06-30' ;
        $range = $this->createRangeOfDates($a,$b);
        //print_r($val);
        //$this->assertContains( strtotime('2014-06-30'), $val) ;
        $this->assertCount(30, $range);
        $this->assertContains( '2014-06-30', $range) ;
        
        //$this->asserFalse(array_search('2014-06-03', $val));
        
    }
            
    /**
     * Creo un elenco dei giorni che, in base al contratto specifico, rientrano nel mese della giornaliera
     * 
     * @param string $s (data inizio contratto)
     * @param string $e (data cessazione contratto)
     * @return array
     */
    public function getDateLimit($s,$e)
    {
        $value = array( );
        $a   = '2014-06-01' ;
        $b   = '2014-06-30' ;
        
        if($s <= $a) {
            $value['start'] = $a;
            $cs = $a;
        } else {
            $value['start'] = $s;
            $cs = $s;
        }
        
        if($e >= $b) {
            $value['stop'] = $b;
            $ce = $b;
        } else {
            $value['stop'] = $e;
            $ce = $e;
        }
        $this->assertContains($cs, $value['start']);
        $this->assertContains($ce, $value['stop']);
         
        return $value;
    }
    
    
    
    public function _testGiornaliera()
    {
        
        // calendario giornaliera
        $a   = '2014-06-01' ;
        $b   = '2014-06-30' ;
        $val = $this->createRangeOfDates($a,$b,null,true);
              
        # recupero le assenze per il mese della giornaliera
        $assenze = array(
            '2014-06-11' => 'FE',
            '2014-06-12' => 'FE',
            '2014-06-13' => 'FE',
            '2014-06-14' => 'FE',
            '2014-06-21' => 'PS',
            '2014-06-25' => 'PS',
            '2014-06-26' => 'PM',
        );
        
        # simulo due tuple dell'users_contracts
        $contratti = array();
        
        $contratti[0] = array(
            'start'   => '2014-06-11' ,
            'end'    => null,
            'orario' => array('totale' => 6.5)
        );
        
        $contratti[1] = array(
            'start'   => '2013-06-28' ,
            'end'    => '2014-06-05' ,
            'orario' => array('totale' => 6)
        );
        
        $array = array();
        foreach($contratti as $k => $contratto) {
            $start = $contratto['start'];
            $end   = $contratto['end'];
            (!$end) ? $end = $b : $end = $end;  

            $limit = $this->getDateLimit($start, $end);
            $conn  = $this->createRangeOfDates($limit['start'], $limit['stop']);

            $this->testGenerate($val, $assenze, $conn, $array);
        }
        // risultato finale
        print_r($array);
        
    }
    
    /**
     * Genero la giornaliera
     * 
     * @param array $giorni_del_mese ( tutti i giorni del mese da valutare )
     * @param array $elenco_assenze ( elenco delle assenze nel mese considerato )
     * @param array $contratto ( elenco dei giorni che, in base al contratto, rientrano nel range della giornaliera)
     * @param array pointer $result ( calendario completo per il mese )
     * @return null
     */
    public function _testGenerate($giorni_del_mese, $elenco_assenze, $contratto, &$result )
    {
        
        foreach($giorni_del_mese as $k => $array) {
            $iso8610     = $array['iso8610'];
            $day_of_week = $array['day_of_week'] ;
             
            if(in_array($iso8610, $contratto)) {
                if(array_key_exists($iso8610, $elenco_assenze)) {
                    // inserisco l'assenza se esiste
                    $result[$iso8610] = $elenco_assenze[$iso8610];
                } else {
                    // inserisco le ore lavorate quel giorno
                    $result[$iso8610] = array(
                        'mattina' => 3,
                        'sera'    => 3,
                        'totale'  => 6
                    );
                } 
                $this->assertContains( $iso8610 , $contratto ) ;
            } else {
                if(!array_key_exists($iso8610, $result)) {
                    $result[$iso8610] = null;
                }
                $this->assertNotContains( $iso8610 , $contratto ) ;
            }
        }
        print_r($result);
        //$this->assertCount(30, $result);
        return $result;
    }
    
    public function testValidateDate($date) 
    {
        if(is_null($date)) {
            $date = '2014-12-21';
        }
        $this->assertTrue( Prisma_Utility_Validate::validateDate($date, 'Y-m-d'));
    }
    
    /**
     * 
     * @param type $opt
     * @return \Application_Model_Cartellino
     */
    public function createObject($opt)
    {
        $o = new Application_Model_Cartellino($opt);
        if(is_null($opt)) {
            # $date = date('Y-m-d') ;
            $date = '2014-07-11';
            $this->testValidateDate($date);
            $o->setMonth($date);
        }
        return $o;
    }
    
    
    
    public function _testDaysOfMonth() {
        $o = $this->createObject();
        $this->assertEquals('2014-07-01', $o->getFirstDayInMonth());
        $this->assertEquals('2014-07-31', $o->getLastDayInMonth());
    }
    
    public function _testRange1()
    {
        $opt = array('date' => '2014-07-23');
        $o = $this->createObject($opt);
        $this->assertCount(31, $o->getRangeOfMonth());
        $this->assertNotCount(20, $o->getRangeOfMonth());
    }
    
    
    public function _testDate()
    {
        $this->assertTrue( Zend_Date::isDate('2014-13-12', 'yyy-dd-MM'));
    }
    
    
    public function _testValues()
    {
        $var = array('uid' => 12, 'date' => '2014-07-23');
        $o   = $this->createObject($var);
        $this->assertTrue($o->checkValues());
    }
    
    public function _testEvents() 
    {
        $var = array('uid' => 37, 'date' => '2014-07-23');
        $o   = $this->createObject($var);
        $e = $o->setEvents();
        $this->assertCount(0, $e->getEvents());
    }
    
    
    public function _testContracts() 
    {
        $var = array('uid' => 37, 'date' => '2014-06-23');
        $o   = $this->createObject($var);
        $o->cartellinoMensile();
        $this->assertCount(2, $o->getContracts());
    }
    
    public function _testDbInstance() 
    {
        $db = Zend_Registry::get('db');
        $this->assertInstanceOf('Zend_Db_Adapter_Abstract', $db);
    }
    
    public function testDbConnection()
    {
        $db = Zend_Registry::get('db');
        
        try {
            $db->getConnection();
            $this->assertTrue(true);
        } catch (Exception $ex) {
            $this->assertTrue(false);
        }
        
    }
        
    public function testConstruct() {
        $o = $this->createObject();
        $this->assertInstanceOf("Application_Model_Cartellino", $o);
    }
    
    
    public function testCartellino()
    {
        $UM  = new Application_Model_UserMapper();
        $us  = $UM->getAllUsers();
        $users = array();
        foreach($us as $k => $uo) {
            $users[] =  $uo->getId();
        }
        
        //$users = array(29);
        
        sort($users);
        //print_r($users);
        foreach($users as $k => $uid) {
            $opt = array(
                'uid'  => $uid,
                'date' => '2014-07-12' 
            );
            // rivedere meglio cosa viene restituito etc etc
            $cartellino = $this->createObject($opt);
            
            $calendario = $cartellino->creaCartellinoMensile();
            
            // meglio metterlo nell'oggetto
            if($cartellino->isEmpty()) {
                continue;
            }
            print_r($calendario);

            $this->assertCount(31,$calendario['date']);
            $this->assertCount(0, $cartellino->getUsersNoProcessed() );
        }
        
        //$this->assertInstanceOf('Application_Model_Cartellino', $o);
    }
     
    /**
     * 
     */
    public function testUserDataCessazioneContrattoOrigine()
    {
        $m = new Application_Model_UserMapper();
        $user = $m->getMeUser(6);
        $this->assertTrue($user->getCessazione() == false);
    }
    
    
    
    
    
     
    
}
    
    
 