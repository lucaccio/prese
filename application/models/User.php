<?php

/**
 * Description of User
 *
 * @author Luca
 */

class Application_Model_User /*extends Application_Model_Object */
{


    private $eventsService = null;

    protected $_user_id;

    protected $_nome;

    protected $_cognome;

    protected $_email;

    protected $_username;

    protected $_level;

    protected $_resource;

    protected $_secret;

    protected $_active;

    protected $_sede;

    protected $_contratto_id;

    protected $_data_assunzione;

    protected $_data_cessazione;

    protected $_events;

    protected $_contracts_list = null;

    /* configurazioni di ogni utente da db users_configs*/
    protected $_userConfig = null;

    /**
     * Application_Model_User constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        if ($id === null) {
            return;
        }
        $um = new Application_Model_UserMapper();
        return $um->find((int) $id);
    }

    /**
     * 
     * @param type $id
     * @return \Application_Model_User
     */
    public function setId($id)
    {
        $this->_user_id = $id;
        return $this;
    }

    /**
     * 
     * @param type $nome
     * @return \Application_Model_User
     */
    public function setNome($nome)
    {
        $this->_nome = $nome;
        return $this;
    }

    public function setCognome($cognome)
    {
        $this->_cognome = $cognome;
        return $this;
    }

    public function setEmail($email)
    {
        $this->_email = $email;
        return $this;
    }

    public function setUsername($username)
    {
        $this->_username = $username;
        return $this;
    }

    public function setLevel($level_id)
    {
        $level        = new Application_Model_LevelMapper();
        $this->_level = $level->find((int) $level_id);
        return $this;
    }

    public function setActive($active)
    {
        $this->_active = $active;
        return $this;
    }

    /**
     * 
     * @param type $sede_id
     * @return \Application_Model_User
     */
    public function setSede($sede_id)
    {
        //   echo $sede_id .'<br>';
        $sedeDb        = new Application_Model_SediMapper();
        $this->_sede = $sedeDb->find($sede_id);
        return $this;
    }

    public function setContratto($contratto_id)
    {
        $this->_contratto_id = $contratto_id;
        $contratti        = new Application_Model_ContrattiMapper();
        $this->_contratto = $contratti->find($contratto_id);
        return $this;
    }

    public function setAssunzione($date)
    {
        $this->_data_assunzione = $date;
        return $this;
    }

    public function setCessazione($date)
    {
        $this->_data_cessazione = $date;
        return $this;
    }

    public function setContractsList($rowset)
    {
        if ($rowset->count()) {
            $this->_contracts_list = $rowset;
        }
        return $this;
    }

    /**
     * Recupero le speciali configurazioni per singolo utente
     *
     *
     * @ 20/04/2018
     */
    public function setConfigs($rowset)
    {
        if (!$rowset->count()) {
            return $this;
        }
        /*
        foreach($rowset as $key => $row) {
            if ($row->type_value == 'int') {
                $this->_userConfig[$row->key] = $row->value;
            }
            if ($row->type_value == 'json') {
                $arr = json_decode($row->value, true);
                $jsonData = array();
                foreach( $arr as $subKey => $subValue) {
                    $jsonData[$subKey] = $subValue;
                }
                $this->_userConfig[$row->key] = $jsonData;
            }
        }
        */
        $row = $rowset->current();
        $values = json_decode($row->user_values, true);

        //Zend_Debug::dump($row);

        // Prisma_Logger::logToFile($values, true, "migrazione");

        $jsonData = array();

        if ($values == null)
            return $this;

        foreach ($values as $subKey => $subValue) {
            $jsonData[$subKey] = $subValue;
        }
        $this->_userConfig = $jsonData;
        //$this->_userConfig['configs'] = $jsonData;
        return $this;
    }






    ##########################################
    ############### GETTERS ##################
    ##########################################


    /**
     * @since 20/04/2018
     * @return array | null
     */
    public function getConfig()
    {
        return $this->_userConfig;
    }

    /**
     *
     */
    public function getConfiguration()
    {
        $stdObject = new stdClass();
        $stdObject = (object) $this->_userConfig;
        //Prisma_Logger::logToFile(json_encode($stdObject));
        return $stdObject;
    }


    public function getUserConfig()
    {
    }


    /**
     * 
     * @todo fare una funzione che ordini i risultati in base alla data start
     * 
     * @return boolean
     */
    public function getContractsList()
    {
        if (!$this->_contracts_list) {
            return false;
        }
        return $this->_contracts_list;
    }


    //GETTERS

    public function getId()
    {
        return $this->_user_id;
    }

    public function getNome()
    {
        return $this->_nome;
    }

    public function getCognome()
    {
        return $this->_cognome;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    public function getUsername()
    {
        return $this->_username;
    }

    public function getLevel()
    {
        return $this->_level;
    }

    /**
     * @return string
     */
    public function getLevelName()
    {
        $mapper = new Application_Model_LevelMapper();
        $level = $mapper->find($this->getLevel()->getLevelId());
        return trim($level->getDescrizione());
    }

    /**
     * Ritorna cognome e nome oppure il contrario se $nome = true
     * @param boolean $nome
     * @return type
     */
    public function getAnagrafe($options = null)
    {

        $cognome = $this->_cognome;
        $nome    = $this->_nome;
        if (isset($options) && is_array($options)) {
            //Zend_Debug::dump($options);
            if (isset($options['nome_puntato'])) {
                $nome = substr($nome, 0, 1) . '.';
            }
            if (isset($options['name_first'])) {
                return $nome . ' ' . $cognome;
            }
        }
        return $cognome . ' ' . $nome;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->_active;
    }

    /**
     * 
     */
    public function getSede()
    {
        if ($this->_sede instanceof Application_Model_Sede) {
            return $this->_sede;
        }
        return new Application_Model_Sede();
    }

    public function getContratto()
    {
        return $this->_contratto;
    }

    /**
     * 
     * @param string $format
     * @return Zend_Date
     */
    public function getAssunzione($format = null)
    {
        if ((null == $this->_data_assunzione) || ('0000-00-00' == $this->_data_assunzione)) {
            return 0;
        }
        if (null == $format) {
            return  $this->_data_assunzione;
        }

        $date = new DateTime($this->_data_assunzione);
        $ts =  $date->getTimestamp();
        $data = new Zend_Date($ts);
        Prisma_Logger::logToFile($data->toString($format));
        return $data->toString($format);
    }

    /**
     * alias of
     * @return type
     */
    public function getDataAssunzione()
    {
        return $this->getAssunzione();
    }

    /**
     * 
     * @param type $format
     * @return boolean
     */
    public function getCessazione($format = null)
    {

        if (('0000-00-00' == $this->_data_cessazione) || ('' == $this->_data_cessazione)) {
            return false;
        }

        if (null == $format) {
            return  $this->_data_cessazione;
        }
        $date = new DateTime($this->_data_cessazione);
        $ts =  $date->getTimestamp();
        $data = new Zend_Date($ts);
        Prisma_Logger::logToFile($data->toString($format));
        return $data->toString($format);
    }



    /**
     * Restituisce l'anzianità di servizio in base alla data di assunzione
     * @param type $date
     * @param type $format
     */
    public function getAnzianitaServizio($date = null, Zend_Measure_Time $format = null)
    {

        (null === $date) ? $date = new Zend_Date() : $date;

        if (!$date instanceof Zend_Date) {
            $date = new Zend_Date($date);
        }

        $dataAssunzione = $this->getDataAssunzione();
        if (null == $dataAssunzione) {
            throw new Exception('Non risulta una data di assunzione inserita...impossibile procedere');
        }

        $assunto = new Zend_Date($dataAssunzione);
        $x = $date->sub($assunto);
        $measure = new Zend_Measure_Time($x->toValue(), Zend_Measure_Time::SECOND);

        if ($format === null) {
            $format = Zend_Measure_Time::YEAR;
        }

        $anzianita = $measure->convertTo($format);

        return $anzianita;
    }





    // CHECKING


    /**
     * 
     * @return boolean
     */
    public function isAdmin()
    {
        if (3 === (int) $this->getLevel()->getLevelId()) {
            echo '123123123';
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $secret
     * @return boolean
     */
    public function isSecret($secret)
    {
        $mapper = new Application_Model_UserMapper();
        if (true == $mapper->findSecret($this->getId(), $secret)) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @return boolean
     */
    public function isActive()
    {
        if ($this->getActive() == 1) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @return boolean
     */
    public function isSostituto()
    {
        if ($this->getLevel()->getLevelId() == 2) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param array $events
     * @return \Application_Model_User
     */
    public function insertEvents(array $events)
    {
        $this->_events = $events;
        return $this;
    }

    /**
     * 
     * @return type
     */
    public function getEvents()
    {
        return $this->_events;
    }


    public function getSedeObj()
    {

        $sede = new Application_Model_SediMapper();
        $this->_sedeObj = $sede->find($this->getSede());

        return $this->_sedeObj;
    }

    /**
     * 
     * @param datetime $date
     * @return boolean
     */
    public function isAssunto($date)
    {
        if (($this->getAssunzione() <= $date) && (($date <= $this->_data_cessazione)
            ||  ('' == $this->_data_cessazione)
            || ('0000-00-00' == $this->_data_cessazione)
            || (null == $this->_data_cessazione))) {
            return true;
        }
        return false;
    }





    /**
     * 
     * @param type $when
     * @param type $yearly
     */
    public function getGiornalieraMensile($m, $y)
    {
        if (!$this->isActive()) {
            return array('disabled' => true);
        }

        $values  = array();
        $values[$this->getId()] = null;

        $date = new Zend_Date();
        $date->setDay('1')
            ->setMonth($m)
            ->setYear($y)
            ->setTime('00:00:00');
        $inizio = $date->toString('yyyy-MM-dd');
        $date->addMonth('1')->subDay('1');
        $last = $date->toString('dd');
        $fine = $date->toString('yyyy-MM-dd');
        $date->setDay('1');

        //$lavorativi = Application_Service_Tools::getArrayOfActualDays($inizio, $fine);

        $lavorativi = Application_Service_Tools::listOfDays($inizio, true);
        //$lavorativi = array_flip($lavorativi);

        foreach ($lavorativi as $day => $value) {
            if ($this->isAssunto($day)) {
                $dOb = new Zend_Date($day, Zend_Date::ISO_8601);
                # se è sabato
                if (6 ===  (int) $dOb->get(Zend_Date::WEEKDAY_DIGIT)) {
                    $lavorativi[$day] = $this->getContratto()->getRidotto();
                    # se è domenica    
                } elseif (0 ===  (int) $dOb->get(Zend_Date::WEEKDAY_DIGIT)) {
                    $lavorativi[$day] = '';
                    # se è festivo diverso da domenica    
                } elseif (('H' == $value) || ('EM' == $value)) {
                    $lavorativi[$day] = '';
                    # se è lavorativo    
                } else {
                    $lavorativi[$day] = round($this->getContratto()->getPieno(), 1);
                }
            } else {
                $lavorativi[$day] = '';
            }
        }

        /* calcolo delle assenze */
        $service = $this->getEventsService();
        $rows    = $service->getUserEventsByMonth($this->getId(), $m, $y);
        $tm      = new Application_Model_TipologiaMapper();

        if ($rows->count()) {
            $events = array();
            foreach ($rows as $k => $value) {
                $tipo = $tm->find($value->tipo);
                $sigla = $tipo->getSigla();
                switch ($sigla) {
                    case 'PM':
                        $sigla = $this->getContratto()->getSera() . '¹';
                        break;
                    case 'PS':
                        $sigla = $this->getContratto()->getMattina() . '²';
                        break;
                }
                $lavorativi[$value->giorno]  = $sigla;
            }
        }
        return array('enabled' => $lavorativi);
    }

    /**
     * 
     */
    public function setDefaultEventsService()
    {
        $this->eventsService = new Application_Model_EventiMapper();
    }

    /**
     * 
     * @param type $es
     */
    public function setEventsService($es)
    {
        $this->eventsService = $es;
    }

    /**
     * 
     * @return type
     */
    public function getEventsService()
    {
        if (null === $this->eventsService) {
            $this->setDefaultEventsService();
        }
        return $this->eventsService;
    }


    /**
     * (2013-28-11)
     * 
     * @todo da implementare in una classe dbtable
     * 
     */
    public function getContractsList_old()
    {
        $uid = $this->_user_id;
        $db  = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $sql = "SELECT * FROM users_contracts 
                    WHERE user_id = ? 
                    ORDER BY id DESC 
                ";
        // AND last = 0 


        $result = $db->fetchAll($sql, $uid);
        return $result;
    }

    public function getOldContracts()
    {
        $uid = $this->_user_id;
        $db  = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $sql = "SELECT * FROM users_contracts 
                    WHERE user_id = ? 
                    AND last = 0
                    ORDER BY id ASC 
                ";
        // AND last = 0 


        $result = $db->fetchAll($sql, $uid);
        return $result;
    }

    #########################################
    /* CONTRATTI */
    #########################################

    /**
     * Restituisce il contratto in essere, che è quello ancora aperto alla data odierna (vedi uc.stop)
     * 
     * 
     * @return type
     */
    public function getActiveContract()
    {
        $uid = $this->_user_id;
        $db  = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $today = date('Y-m-d');
        $sql = "SELECT * FROM users_contracts AS uc 
                JOIN contratti AS c ON c.contratto_id=uc.contratto_id 
                WHERE uc.user_id = ? 
                AND (uc.stop IS NULL OR uc.stop >= '$today')";

        $result = $db->fetchAll($sql, $uid);
        if ($result) {
            return $result[0];
        }
        return $result;
    }

    /**
     * Restituisce il contratto con flag 'last' che indica che è l'ultimo inserito in ordine di tempo
     * 
     * se presente, restituisce un stdClass
     * altrimenti un array vuoto
     * 
     */
    public function getLastInsertedContract()
    {
        $uid = $this->_user_id;
        $db  = Zend_Registry::get('db');
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $today = date('Y-m-d');

        $sql = "SELECT * FROM users_contracts AS uc 
                JOIN contratti AS c ON c.contratto_id=uc.contratto_id 
                WHERE uc.user_id = ? 
                AND uc.last = 1";

        $result = $db->fetchAll($sql, $uid);
        //Prisma_Logger::log($result);
        // print_r($result);
        if ($result) {
            return $result[0];
        }
        return $result;
    }



    /**
     * 
     * @return boolean
     */
    public function hasActiveContract()
    {
        if (count($this->getActiveContract()) == 0) {
            return false;
        }
        return true;
    }


    /**
     * Controlla se l'user ha una sede associata
     * @return boolean
     * @date 24 marzo 2018
     */
    public function hasSede()
    {
        if ($this->_sede !=  null) {
            if ($this->_sede->getSedeId() != null) {
                return true;
            }
        }
        return false;
    }
}
