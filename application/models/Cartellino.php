<?php
/**
 * Cartellino mensile  dei Dipendenti
 * 
 * calendario dei giorni/ore lavorati dal singolo dipendente
 * più eventuali assenze e giorni di festa del periodo
 * 
 * 
 * 
 * @author Luca
 */
class Application_Model_Cartellino {
    
    // id dell'user
    protected $_user_id ;
    
    // cognome e nome utente
    protected $_name ;
    
    // sede id
    protected $_sede = null;

    /**
     * mese da processare
     * @var null
     */
    protected $_month  = null;

    // primo giorno mese da processare
    protected $_first_day_in_month ;
    
    // ultimo diorno del mese da processare
    protected $_last_day_in_month;
    
    // array dei giorni del mese da processare
    protected $_range_of_month = array();
    
    // array delle assenze dell'user per il mese da processare
    protected $_list_of_events = array();
    
    // array dei contratti di lavoro dell'user
    protected $_list_of_contracts = array();
    
    // array di festivita per una determinata sede
    protected $_patrono = null ;

    protected $_lavorativo = null;

    //
    protected $_year = null;

    /**
     *  Array con chiave che corrisponde al giorno del mese e valore che corrisponde
     *  alle ore lavorate o al tipo di assenza
     *
     * @var array
     */
    protected $_cartellino;

    /**
     * 23/11/2016
     *
     * @array :  totali per assenza
     */
    protected $_elenco_totali_assenze = [];

    /**
     * @var array
     */
    protected $_users_no_processed = array();
    
    /**
     * chiavi standard
     */
    private $_options = array(
        'uid'   => null,
        'name'  => null,
        'date'  => null,
        'month'  => null,
        'sede'  => null
    );
    
    // chiavi standard
    private $_range = array(
        'start' => null,
        'stop'  => null
    );

    // array iniziale dove andrò a inserire i dati riguardanti assenze
    // e ore lavorate dall'utente.
    protected $_range_of_days = array();


    /**
     * giorni totali per tipologia
     * @var
     */
    private $_totali_tipologie = array();








    /**
     * 
     * 
     * @param type $options
     */
    public function __construct($options)
    {
        if( is_array($options) ) {
            $this->setOptions($options);
        }
        return $this;
    }
    
    /**
     * Setto parametri standard (uid e date)
     * 
     * @param type $options
     * @return type
     */
    public function setOptions(array $options = array())
    {
        if (empty($options)) {
            throw new Exception("Il parametro deve essere di tipo array.");
        } 
        foreach ($options as $name => $value) {
            $name  = strtolower($name);
            // controllo se l'opzione che passo al costruttore esiste di default
            if (array_key_exists($name,  $this->_options)) {
                if(is_null($value)) {
                    throw new Exception ("Valore nullo per la chiave $name");
                }
                switch($name) {
                    case 'uid' :
                        $this->setUserId($value);
                    break;
                    case 'name' :
                        $this->setName($value);
                    break;
                    case 'sede' :
                        $this->setSede($value);
                    break;

                    case 'month' :
                        $this->_month = $value;
                        break;

                    case 'date' :
                        if(null == $value) {
                            throw new Exception("Valore date nullo invalido");
                        }
                        if(Prisma_Tool_Array::isArray($value)) {
                            $this->setMonthArray($value);
                            $keys = array_keys($value) ;
                            //Prisma_Logger::logToFile($keys);
                            $this->setMonth($keys[0]);
                        } elseif(is_string($value)){
                              // @todo finire l'implementazione
                              // $this->setMonth($value);
                        } else {
                            throw new Exception("Valore per il campo date non riconosciuto");
                        }
                    break;
                }
            }
            else {
                throw new Exception("Opzione non conosciuta: $name = $value");
            }
        }
    }
    
    
    /**
     * 
     * @param type $uid
     * @return \Application_Model_Cartellino
     * @throws Exception
     */
    public function setUserId($uid) 
    {
        if( !isset($uid) ) {
            throw new Exception("Inserire user id");
        }
        $this->_options['uid'] = (int)$uid;
        $this->_user_id = (int)$uid;
        return $this;
    }
    
    /**
     * 
     * @return type
     * @throws Exception
     */
    public function getUserId()
    {
        if(is_null($this->_options['uid'])) {
            throw new Exception("Invalid user id.");
        }
        return  $this->_options['uid'] ;
    }
    
    /**
     * 
     * @param type $name
     * @return \Application_Model_Cartellino
     */
    public function setName($name)
    {
        $this->_options['name'] = $name;
        $this->_name = $name;
        return $this;
    }
    
    /**
     * 
     * @return type
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * 
     * @param type $id
     * @return \Application_Model_Cartellino
     */
    public function setSede($id)
    {
        $this->_options['sede'] = $id;
        $this->_sede = $id;
        return $this;
    }
    
    /**
     * 
     * @return type
     */
    public function getSede() {
        return $this->_sede;
    }
    
    /**
     * 
     * @param type $value
     */
    public function setMonthArray($value) 
    {
        Prisma_Tool_Array::validateArray($value);
        $this->_range_of_days = $value;
    }
    
    /**
     * Setto i valori per il mese da elaborare e creo un range di date che va dal
     * primo giorno del mese all'ultimo giorno del mese
     * 
     * 
     * @param type $date
     * @throws Exception
     */
    public function setMonth($date) 
    {
        if(is_null($date)) {
            $date = date('Y-m-d');
        }
        if(!Zend_Date::isDate($date,'yyy-MM-dd')) {
            throw new Exception("Data invalida.");
        }
        $this->_options['date'] = $date;
        $date = new Zend_Date($date, Zend_Date::ISO_8601);
        $date->setDay('1');
        $this->_first_day_in_month = $date->toString('yyy-MM-dd');
        $date->addMonth('1')->subDay('1');
        $this->_last_day_in_month  = $date->toString('yyy-MM-dd');
        $this->_range['start'] = $this->_first_day_in_month;
        $this->_range['stop']  = $this->_last_day_in_month ;
        $this->_year = $date->toString('yyy');
        // crea range del mese  
        $this->_createRangeOfMonth();
        return $this;
    }
    
    /**
     * 
     * @return type
     * @throws Exception
     */
    public function getMonth()
    {
        if(!Zend_Date::isDate($this->_options['date'],'yyy-MM-dd')) {
            throw new Exception("Data invalida.");
        }
        return $this->_options['date'] ;
    }
    
    /**
     * Crea un array con i giorni di assenza per l'utente
     * 
     */
    public function setEvents()
    {
        if(!$this->checkValues()) {
            return false;
        }
        $events = array();
        $uid    = $this->getUserId();
        //Prisma_Logger::logToFile($this->_range);
        $range  = $this->_range;            
        $em     = new Application_Model_EventiMapper();
        $result = $em->findByUserAndRange($uid, $range);
        
        if(!$result) {
            return $events;
        }
        
        # @todo come gestire i doppioni lo stesso giorno per un singolo user?
        $value  = array();
        foreach($result as $k => $row) {
            
            // old 
            //$events[$row->giorno] = $row->sigla;
            
            // refactoring
            if(isset($events[$row->giorno])) {
                $value[] = $row->sigla; 
                $events[$row->giorno] =  $value;
            } else {
                $value  = array();
                $value[] = $row->sigla; 
                $events[$row->giorno] =   $value;;
            }
        }
        //Prisma_Logger::logToFile($events);
        $this->_list_of_events = $events;
        return $this;
    }
    
    /**
     * 
     * @return boolean
     */
    public function getEvents()
    {
        if(!$this->checkValues()) { return false; }
        return $this->_list_of_events;
    }
    
    /**
     * 
     */
    public function setContracts()
    {
        $map = new Application_Model_UserMapper();
        $uid = $this->getUserId();
        // verifico la presenza di contratti nella nuova tabella contratti
        $contractsList = $map->userGetContracts($uid);
        
        // se non risulta almeno un contratto nella nuova tabella, allora prendo il contratto
        // dalla vecchia tabella e lo inserisco nella nuova tabella users_contracts
        if($contractsList->count() == 0) {
            // inserisco nella tabella users_contracts il contratto della vecchia tabella
            $this->migrateContract($uid);
        }
        
        //Prisma_Logger::log("Cerco il contratto per user: $uid");
        // from table users_contracts
        $contratti  = $map->userGetContractsByDate($uid, $this->getFirstDayInMonth());
        if( $contratti->count() ) 
        {
            foreach($contratti as $k => $row) {
                $parentRow = $row->findParentRow('Application_Model_DbTable_Contratti');
                $depRows   = $parentRow->findDependentRowset('Application_Model_DbTable_ContrattiDetails'); 
                if($depRows->count()) {
                    foreach($depRows as $k => $dep) {
                        $details[$dep['ref']] =  $dep->toArray() ;
                    }
                }
                $this->_list_of_contracts[] = array(
                  'start'   => $row->start,
                  'end'     => $row->stop,
                  'details' => $details 
                );
            }
        }
        else {
            Prisma_Logger::log('Nessun contratto presente nel periodo selezionato.');    
        }
    }
    
    /**
     * Fa una migrazione dal vecchio sistema dei contratti al nuovo sistema
     * 
     */
    public function migrateContract($uid)
    {
        // aggiorno la tabella contratti (la tabella di nuova generazione)
        // inserendo i vecchi valori presi dalla vecchia tabella dei contratti
        $gateway = new Application_Model_UserMapper();        
        $row     = $gateway->getMeUser($uid);
                                 
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            if($row->getCessazione() == false) {
                $cessazione = null;
            }else {
                $cessazione = $row->getCessazione();
            }
            $data = array(
                'contratto_id' => $row->getContratto()->getId(),
                'user_id'      => $row->getId(),
                'start'        => $row->getAssunzione(),
                'stop'         => $cessazione,
                'last'         => 1
            );
            $db->insert('users_contracts', $data);
            $db->commit();
        } catch (Exception $ex) {
            $db->rollBack();
            throw new Exception($ex->getMessage());
        }
    }
    
    /**
     * Setto il patrono per la sede dell'user, se presente
     * 
     * @return type
     */
    public function setPatrono()
    {
        if($this->getSede() == null) {
             Prisma_Logger::logToFile("No sede per: " . $this->getUserId(), OFF);
            return  ;
        } 
        $gateway = new Application_Model_FestivitaMapper();
        $row = $gateway->findPatronalSaint($this->getSede());

        //Zend_Debug::dump($row);

        if($row) {                    
            $mese = $row->mese;
            if($mese <= 9) {
                $mese = "0" . $mese;
            } 
            $giorno = $row->giorno;
            if($giorno <= 9) {
                $giorno = "0" . $giorno;
            } 
            $day = $this->_year . '-' . $mese . '-' . $giorno;
            Prisma_Logger::logToFile("Patrono: " . $day . ' : ' . $this->getSede(),OFF);
            $this->_patrono =  $day;
            $this->_lavorativo = $row->lavorativo;
        }
    }
    
    /**
     * 
     */
    public function getContracts()
    {
        return $this->_list_of_contracts;
    }
    
    /**
     * 
     * @return type
     */
    public function getFirstDayInMonth()
    {
        return $this->_first_day_in_month;
    }
    
    /**
     * 
     * @return type
     */
    public function getLastDayInMonth()
    {
        return $this->_last_day_in_month;
    }
    
    /**
     * 
     * @return type
     */
    public function getRangeOfMonth()
    {
        return $this->_range_of_month;
    }
    
    /**
     * 
     * @return array
     */
    public function creaCartellinoMensile()
    {
        //@todo 01 04 2018
        $this->azzeraTotaliTipologie();

        // popolo l'array delle eventuali assenze
        $this->setEvents();
        
        // creo un array con i contratti validi per il mese selezionato
        $this->setContracts();
        
        // creo il patrono se esiste nel db
        $this->setPatrono();
        
        $cartellino = array();
        if(count($this->getContracts()) > 0 ) {
            foreach($this->getContracts() as $k => $contratto) {
                $start = $contratto['start'];
                $end   = $contratto['end'];
                $rangeDateContratto['details'] = $contratto['details'];
                (!$end) ? $end = $this->getLastDayInMonth() : $end = $end; 
                
                // seleziono le date utili per il mese da generare, all'interno di un contratto            
                $limit = $this->_getDateLimit($start, $end);
                // creo il range di date di un contratto utile
                $rangeDateContratto['range'] = Prisma_Utility_Date::createRangeOfDates($limit['start'], $limit['stop']);
                $range   = $this->getRangeOfMonth();


                $assenzeInEventi = $this->getEvents();
                                   
                // popolo il cartellino
                // $this->_generate($range, $assenze , $rangeDateContratto, $cartellino);
                $uid = $this->getUserId();



                $this->_generate( $this->_range_of_days, $assenzeInEventi , $rangeDateContratto, $cartellino, $uid );
            }
        }
        else {
            $this->_users_no_processed[] = $this->getUserId();
        }
        $result = array(
            'uid'  => $this->getUserId(),
            'name' => $this->getName(),
            'date' => $cartellino,
            'totali' => $this->_elenco_totali_assenze
        ); 
        
        // Prisma_Logger::logToFile($result);
        $this->_cartellino = $result;
       // Prisma_Logger::logToFile(json_encode($this->_cartellino));
        return $this->_cartellino;
    }
    
    /**
     * Genera il cartellino mensile per un singolo utente
     * 
     * @param type $range 
     * @param type $assenze 
     * @param type $contratto
     * @param array $result ( è il cartellino che verrà generato, ovvero un array che comprende solo le date,
     *                        e non nome o altro)
     * @return boolean
     */
    protected function _generate($range, $assenze, $contratto, &$result, $uid = null)
    {
        try {




            // loggo i tipi di assenza trovati per l'utente
            // Prisma_Logger::logToFile($assenze);

            // controllo di validità iniziale
            if(!$this->checkValues()) {
                Prisma_Logger::logToFile('Controllo di validita fallito: utente o mese non settato.');
                return false;
            }

            $date =
                // elenco festività nazionali e per sede
            $map = new Application_Model_FestivitaMapper();
             $festivita = $map->getFestePerMese($this->_month);
            Prisma_Logger::logToFile(json_encode($festivita));

            //array $cDetails mi dice le ore lavorate di mattina e sera durante la settimana
            // (in base al contratto?)
            $cDetails = $contratto['details'];
            $cRange   = $contratto['range'];

 


            foreach($range as $k => $string)
            {
                $iso8610 = $k;


                // aggiungo festivita

                if(in_array($iso8610, $festivita )) {
                    Prisma_Logger::logToFile($festivita['feste'] );
                    $result[$iso8610] = 'FV';

                    $this->aggiornaTotaleTipologia('FV', 1 );
                    continue;
                }




                // data in formato iso8610 0000-00-00

                if(in_array($iso8610, $cRange))
                {
                    // inserisco l'assenza se esiste
                    if(array_key_exists($iso8610, $assenze))
                    {

                        $tipo = $assenze[$iso8610]; // individuo il tipo di assenza

                        if(count($tipo) == 1) // vuol dire che ho trovato una sola assenza per quel giorno
                        {

                            //@date 06/06/2016
                            /*## RECUPERO QUANTITA ORE DI PERMESSO GIORNALIERO ASSEGNATE */
                            $mapE = new Application_Model_EventiMapper();
                            $range = array(
                                'start' => $iso8610,
                                'stop'  => $iso8610
                            );
                            $evento = $mapE->findByUserAndRange($uid, $range);
                            $assenza_id = $evento->current()->assenza_id;

                            $mapA = new Application_Model_AssenzeMapper();
                            $assenza = $mapA->getAssenzaById($assenza_id);

                            // in base a un refactoring del database, attualmente riesco a recuperare le ore di assenza
                            // per permessi, ma pre-refactoring il risultato da 0
                            // perciò l'algoritmo successivo ne tiene conto
                            //
                            $totale_ore_giornaliere_di_assenza = $assenza->qta;

                            //Prisma_Logger::logToFile("uid)" . $uid . " quantità = " . $assenza->qta);
                            /*## FINE CALCOLO */


                            $case = $tipo[0]; // PERMESSO MATTINA / SERA

                            $orario_lavoro_giornaliero = $this->_getTotalWorkedHoursByDay($iso8610, $cDetails);

                            switch($case) {
                                case 'PM':

                                    $tipo = '';
                                    //$tipo = $totale_ore_giornaliere_di_assenza . '¹';

                                    if($totale_ore_giornaliere_di_assenza == 0) { // pre-refactoring database?
                                        // recupero le ore serali
                                        $ore_effettive_lavorate = $this->_getContractSchedule($iso8610, $cDetails, 'morning');
                                        $tipo = $ore_effettive_lavorate . '¹';
                                        $totale_ore_giornaliere_di_assenza = $orario_lavoro_giornaliero - $ore_effettive_lavorate;
                                    } else { // post-refactoring database
                                        $ore_effettive_lavorate = $orario_lavoro_giornaliero - $totale_ore_giornaliere_di_assenza;
                                        //
                                        $tipo =  $ore_effettive_lavorate . '¹';
                                    }
                                   // $result_t['totali']['P'] += $totale_ore_giornaliere_di_assenza ;

                                    //doppione
                                    $this->aggiornaTotaleTipologia('P', $totale_ore_giornaliere_di_assenza );

                                    break;
                                case 'PS':
                                    $tipo = '';
                                    if($totale_ore_giornaliere_di_assenza == 0) { // pre-refactoring database
                                        $ore_effettive_lavorate = $this->_getContractSchedule($iso8610, $cDetails, 'evening');
                                        $tipo = $ore_effettive_lavorate . '²';
                                        $totale_ore_giornaliere_di_assenza = $orario_lavoro_giornaliero - $ore_effettive_lavorate;
                                    } else {
                                        $ore_effettive_lavorate = $orario_lavoro_giornaliero - $totale_ore_giornaliere_di_assenza;
                                        //$tipo = $totale_ore_giornaliere_di_assenza . '²';
                                        $tipo = $ore_effettive_lavorate .  '²';
                                    }

                                   // $result_t['totali']['P'] += $totale_ore_giornaliere_di_assenza ;
                                    //doppione
                                    $this->aggiornaTotaleTipologia('P', $totale_ore_giornaliere_di_assenza );

                                    break;
                            }
                        } elseif(count($tipo) == 2) { // vuol dire che risultano 2 permessi lo stesso giorno

                            /**
                             * dovrebbe valere solo per il pre-refactoring del database
                             */

                            // qui se ho sia PM che PS lo stesso giorno
                            // Prisma_Logger::logToFile("$uid) permesso giornaliero");
                            $tipo = 'PG';
                            //$tipo = $this->_getContractSchedule($iso8610, $cDetails, 'none') . '²';
                            $tot = $this->_getTotalWorkedHoursByDay($iso8610, $cDetails);
                          //  $result_t['totali']['P'] += $tot;
                            //doppione
                            $this->aggiornaTotaleTipologia('P', $tot );
                        }

                        // qui vengono stampati tutti i tipi di assenze diversi dai permessi
                        if(is_array($tipo)) {
                            $tipo = $tipo[0];
                            switch ($tipo) {
                                case 'FE':
                                    //$result_t['totali']["$tipo"] += 1;
                                    //doppione
                                    $this->aggiornaTotaleTipologia('FE', 1 );
                                    break;

                                case 'PG' :
                                    $tot = $this->_getTotalWorkedHoursByDay($iso8610, $cDetails);
                                    //$result_t['totali']['P'] += $tot;
                                    $this->aggiornaTotaleTipologia('P', $tot );
                                    break;

                                case 'ML' :
                                    //$result_t['totali']['ML'] += 1;
                                    $this->aggiornaTotaleTipologia('ML', 1);
                                    break;

                            }
                        }
                        $result[$iso8610] = $tipo;

                    }
                    //@TODO zona importante PATRONO
                    else { // altrimenti, inserisco le ore lavorate quel giorno
                        if($string == 'L') {
                            $ore = $this->_getContractSchedule($iso8610, $cDetails);
                            //PATRONO
                            if( $iso8610 == $this->_patrono) {
                                if($this->_lavorativo) {
                                   // $result_t['totali']['ORE'] += $ore;

                                    $this->aggiornaTotaleTipologia('ORE', $ore );
                                    $this->aggiornaTotaleTipologia('GIORNI', 1 );
                                    $result[$iso8610] =  $ore . '³';

                                    //$result_t['totali']['FV'] += 1;

                                } else {
                                    $result[$iso8610] = 'FV';
                                   // $result_t['totali']['FV'] += 1;
                                    $this->aggiornaTotaleTipologia('FV', 1 );
                                }

                            } else {
                                $result[$iso8610] =  $ore;
                               // $result_t['totali']['ORE'] += $ore;
                                $this->aggiornaTotaleTipologia('ORE', $ore );
                                $this->aggiornaTotaleTipologia('GIORNI', 1 );
                            }

                        } else {  // domenica ??
                            // qui inserisco la stringa D | H

                            //$result[$iso8610] = $string;
                            $result[$iso8610] = '';
                        }
                    }
                } else {
                    if(!array_key_exists($iso8610, $result)) {
                        $result[$iso8610] = null;
                    }
                }
            }



           // Prisma_Logger::logToFile("uid: " . $uid . " -> " . json_encode($this->_totali_tipologie));
            // riga mensile con i giorni lavorati/assenze

            $this->_elenco_totali_assenze = $this->getTotaliTipologie() ;

            $this->_cartellino = $result;
        } catch(Exception $e) {
            Prisma_Logger::logToFile($e->getMessage());
        }

    }
                        
    /**
     * 
     */
    public function dettaglioGiorno() {}
           
    /**
     * Controlla che i dati essenziali siano validi.
     * 
     * @return boolean
     */
    public function checkValues() {
        
        if(!$this->getUserId()) {
            return false;
        }
        if(!$this->getMonth()) {
            return false;
        }
        return true;
    }
    
    
    /**
     * 
     * 
     */
    protected function _createRangeOfMonth() 
    {
        $start = $this->_first_day_in_month ;
        $end   = $this->_last_day_in_month ;
                    
        $this->_range_of_month = Prisma_Utility_Date::createRangeOfDates($start, $end, null, true);
        //$this->_range_of_month = Application_Service_Tools::generateArrayOfDays($start, $end);
    }
    
    /**
     * 
     * @param type $s
     * @param type $e
     * @return type
     */
    protected function _getDateLimit($s,$e)
    {
        $value = array();
        $a     = $this->getFirstDayInMonth() ;
        $b     = $this->getLastDayInMonth() ;
        
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
        return $value;
    }

    /**
     * @date 05/08/2016
     *
     * Funzione per determinare il totale delle ora lavorate da un dipendente
     * un determinato giorno dell'anno,
     * in base al suo contratto di lavoro
     *
     * @param $day_of_week
     * @param $contract
     * @param type $locale
     * @throws Zend_Date_Exception
     * @return double|false
     */
    protected function _getTotalWorkedHoursByDay($day_of_week, $contract, $locale = 'en_US')
    {
        $zd = new Zend_Date($day_of_week,Zend_Date::ISO_8601);
        $zd->setLocale($locale);
        $week = $zd->toString(Zend_Date::WEEKDAY_NAME);
        $week = (string) strtolower($week);
        if(isset($contract['mattina'][$week]) && isset($contract['sera'][$week])) {
            // $mattina e $sera contengono le ore di lavoro del giorno della settimana impostato in $week,
            // come stabilito dal contratto
            $mattina = $contract['mattina'][$week];
            $sera    = $contract['sera'][$week];
            return $mattina + $sera;
        }
        return false;
    }



    /**
     * Restituisce il totale delle ore lavorate un determinato giorno dell'anno ($day)
     * 
     * @param type $day
     * @param type $list
     * @param string $when ( full | morning | evening ) 
     * @param type $locale
     * @return boolean
     */
    protected function _getContractSchedule($day, $list, $when = 'full' , $locale = 'en_US') 
    {
        // procedura per determinare le ore di lavoro stabilite dal contratto,
        // per quel determinato giorno della settimana impostato nella variabile $day
        //
        $zd = new Zend_Date($day,Zend_Date::ISO_8601);
        $zd->setLocale($locale);
        $week = $zd->toString(Zend_Date::WEEKDAY_NAME);
        $week = (string) strtolower($week);
        if(isset($list['mattina'][$week]) && isset($list['sera'][$week])) {
            // $mattina e $sera contengono le ore di lavoro del giorno della settimana impostato in $week,
            // come stabilito dal contratto
            $mattina = $list['mattina'][$week];
            $sera    = $list['sera'][$week];

            // questo serve a restituire le ore lavorate:
            // se faccio permesso mattina mi restituisce le ore lavorate di sera e cosi via
            switch( $when ) {
                case 'morning': // se mi assento di mattina restituisco le ore lavorate di sera
                    if($sera == 0) { return 0; }
                    return $sera;
                break;
                case 'evening': // se mi assento di sera restituisco le ore lavorate di mattina
                    if($mattina == 0) { return  0; }
                    return $mattina;
                break;
                case 'full':
                    $totale = $mattina + $sera;
                    if($totale == 0) { return ; }
                    return $totale;
                break;
                case 'none':
                    $totale = 0;
                    return $totale;
                break;
            }
        }
        // domenica ed eventuali giorni non presenti in db
        return false;
    }
    
    /**
     * 
     * @return type
     */
    public function isEmpty()
    {
        return is_null($this->_cartellino);
    }
       
    /**
     * 
     * @return type
     */
    public function getUsersNoProcessed()
    {
        return $this->_users_no_processed;
    }



    /**
     * @param $key
     * @param $value
     */
    private function aggiornaTotaleTipologia($key, $value) {

        if(!isset($this->_totali_tipologie[$key])) {
            $this->_totali_tipologie[$key] = 0;
        }
        $this->_totali_tipologie[$key] += $value;
    }

    /**
     * @param null $tipologia
     * @return array|mixed
     */
    private function getTotaliTipologie($tipologia = null) {
        if($tipologia) {
           if(isset($this->_totali_tipologie[$tipologia])) {
               return $this->_totali_tipologie[$tipologia];
           }
        }
        return $this->_totali_tipologie;
    }


    /**
     *
     */
    private function azzeraTotaliTipologie(){
        $this->_totali_tipologie = null;
        $this->_totali_tipologie   = [
            'FE'  => 0,
            'P'   => 0,
            'FV'  => 0,
            'ML'  => 0,
            'GIORNI' => 0,
            'ORE' => 0,

        ];
    }
        
        
}
    
    
                        