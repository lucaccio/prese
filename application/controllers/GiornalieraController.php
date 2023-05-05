<?php

 /*
function console_log($data) {
    $output = $data;
    ob_start(); 
    $output = 'console.log(' . json_encode($data) . ');';
    $output  = sprintf('<script>%s</script>', $output);
    echo $output;
}


*/

/**
 * @
 */
class GiornalieraController extends Prisma_Controller_Action
{
    
    public $visible = false;

    /**
     *
     */
    public function stampeAction_old()
    {
        if ($this->_request->isPost()) {
            $params = $this->_request->getParams();
            $anno = $params['anno'];
            $mese = $params['mese'];
            $note = $params['note'];
            $datearray = array('year' => $anno, 'month' => $mese, 'day' => 1, 'note' => $note);
            switch ($params['stampa']) {
                case "Stampa Pdf":
                    $this->_genera('pdf', $datearray );
                    break;
                case "Stampa Excel":
                    $this->_genera('xls', $datearray );
                    break;
                case "Stampa Testo":
                    $this->_genera('txt', $datearray );
                    break;
            }
        }
    }


    public function stampeAction()
    {
        if ($this->_request->isPost()) {

            /* prendo i dati dalla form */
            $params = $this->_request->getParams();
            $anno = $params['anno'];
            $mese = $params['mese'];
            $note = $params['note'];
            /* setto la data della giornaliera da generare */
            $date = new Zend_Date();
            $date->setDay(1);
            $date->setMonth($mese);
            $date->setYear($anno);

            $options = array('data' => serialize($date), 'note' => $note);
            switch ($params['stampa']) {
                case "Stampa Pdf":
                    $this->_genera('pdf', $options );
                    break;
                case "Stampa Excel":
                    $this->_genera('xls', $options );
                    break;
                case "Stampa Testo":
                    $this->_genera('txt', $options );
                    break;
            }
        }
    }


    /**
     * Genera la giornaliera in base all'estensione del file richiesto
     *
     * @param [string] $extension
     * @param [array] $options
     * @return void
     */
    private function _genera($extension, $options) {
        $this->disableAll();
        $this->_timeGenerationFile('start');

        $note = $options['note'];

        // recupero tutti gli utenti
        $UM  = new Application_Model_UserMapper();
        $users  = $UM->getAllUsers();

        $zend_date = unserialize($options['data']);
        
        /* nome file usato per il salvataggio */
        $suffix = $zend_date->toString('MMM_Y');
        $file = "giornaliera_$suffix";

        // creo oggetto giornaliera con il mese da generare
        $giornaliera = new Application_Model_Giornaliera( $zend_date );

       // $handler = fopen('users.txt','a+');
       // fwrite($handler, json_encode($users));
        // @todo LOOP PER OGNI UTENTE
        foreach ($users as $k => $user) {
            /* inizializzo la data a ogni passaggio, 
               altrimenti mi ritrovo, a agni giro 
               con la data incrementata di un mese
            */
            $dateToProcess = unserialize($options['data']);
            /*@todo verificare possibile ridondanze nei vari passaggi 
                a volte creo l'user a volte creo l'uid
            */
            
            //$uid = $user->getId();
            
            /* NEW CARTELLINO UTENTE 16 APRILE 2018 */
            $cartellino = new Application_Model_Cartellino_CartellinoBase($user, $dateToProcess);

            $cartellino = new Application_Model_Cartellino_CartellinoOre($cartellino);
 
            $cartellino = new Application_Model_Cartellino_CartellinoAssenze($cartellino);

            $cartellino = new Application_Model_Cartellino_CartellinoFestivita($cartellino);

            
            $cartellinoUtente = $cartellino->genera();

            //console_log($cartellinoUtente);

            $giornaliera->append($cartellinoUtente   , false );
        }
       
        switch ($extension) {
            case "pdf":
                $ext  = ".pdf";
                $filename = $file . $ext;
                $giornaliera->toPdf(PUBLIC_PATH . "/reports/", $filename, $note);
                break;
            case "xls":
                $ext  = ".xlsx";
                $filename = $file . $ext;
                $path = PUBLIC_PATH . "/reports/";
                $giornaliera->toExcel($path, $filename, $note);
                /* da attivare se il download crea caratteri strani */
                  // $this->_downloadExcel($path, $filename);
                break;
            case "txt":
                break;
        }
            $this->_timeGenerationFile('stop', $extension);
    }


    /**
     * 
     */
    private function _timeGenerationFile($fase = 'start', $ext = null) {
        if('start' == $fase) {
            Prisma_Timer::start();
            Prisma_Logger::log("Starting to generate Giornaliera");
            Prisma_Logger::logToFile("Starting to generate Giornaliera");
        }
        elseif('stop' == $fase) {
            Prisma_Logger::log("Giornaliera generated !");
            Prisma_Logger::logToFile("Giornaliera generated !");
            Prisma_Timer::stop();
            Prisma_Logger::logToFile( Prisma_Timer::elapsedTime(5,true,"Tempo per generare il file $ext: ") );
        
        }
    }



    /**
     *  Generazione giornaliera mensile con file di estensione richiesto
     *
     *  Qui viene generato il dettaglio mensile di ogni singolo utente
     *
     *
     *
     */
    private function _genera_old($extension, $datearray)
    {
        Prisma_Timer::start();
        Prisma_Logger::log("Starting to generate Giornaliera");
        Prisma_Logger::logToFile("Starting to generate Giornaliera");


        $date  = new Zend_Date($datearray);
        $first = $date->toString('yyy-MM-dd');
        // mese da processare
        $month = $date->toString(	Zend_Date::MONTH);

        $date->addMonth('+1');
        $date->subDay('+1');
        $last  = $date->toString('yyy-MM-dd');

        $arrayOfDates = Application_Service_Tools::generateArrayOfDays($first, $last);

        // recupero tutti gli utenti
        $UM  = new Application_Model_UserMapper();
        $users  = $UM->getAllUsers();

        // creo oggetto giornaliera
        $giornaliera = new Application_Model_Giornaliera($arrayOfDates);

        // @todo LOOP PER OGNI UTENTE
        foreach ($users as $k => $user) {
            // azzero l'array a ogni ciclo per evitare che rimanagano dati precedenti
            $options = array();
            $options['date'] = $arrayOfDates ;
            $options['month'] = $month;
            $options['uid']  = $user->getId();
            $options['name'] = $user->getAnagrafe(array('nome_puntato' => true));
            $sede = $user->getSede()->getSedeId();

            if ((int) $sede > 0) {
                $options['sede'] = $sede;
            }
            $result = false;
            try {
                //Prisma_Logger::logToFile("Processo user: " . $user->getId(), ON);

                // creo oggetto cartellino per ogni utente, ma SENZA il dettaglio mensile
                $cartellino = new Application_Model_Cartellino($options);
                // creo il dettaglio mensile delle ore lavorata da ciascuno
                $cartellinoUtente = $cartellino->creaCartellinoMensile();
                $uid = $options['uid'] ;


                // appendo al documento 'Giornaliera' il dettaglio ore lavorate/non lavorate dell'utente
                // che sto processando
                $giornaliera->append($cartellinoUtente  , false );

                $result     = true;

            } catch (Exception $ex) {
                Prisma_Logger::logToFile ( $ex->getMessage() );
                Prisma_Logger::logToFile ( $ex->getTraceAsString() );
                $result = false;
            }
        }

        // settaggio legenda
        if ($result) {
            try {
                $this->disableAll();
                $legenda  = array();
                $tm   = new Application_Model_TipologiaMapper();
                $tipi = $tm->getALL(1,true);
                foreach ($tipi as $k => $tipo) {
                    $sigla = $tipo->getSigla();
                    switch ($sigla) 
                    {
                        case "PM":
                            $legenda[] = '¹ = ' . $tipo->getDescrizioneAdmin() ;
                            break;
                        case "PS":
                            $legenda[] = '² = ' . $tipo->getDescrizioneAdmin() ;
                            break;
                        case "PA":
                            $legenda[] = $tipo->getDescrizioneAdmin() ;
                            $legenda[] = '³ = ' . $tipo->getDescrizioneAdmin() . ' (lavorativo)' ;
                            break;
                        default:
                            $legenda[] =  $sigla . ' = ' . $tipo->getDescrizioneAdmin() ;
                            break;
                    }
                }
                // inserisco la legenda
                $giornaliera->setLegenda( $legenda );
                // stampo file
                $y = $datearray['year'];
                $m = $datearray['month'];
                $note = $datearray['note'];
                $file = "giornaliera-$y-$m";
                    //@todo funzionante, ma da rivedere
                    switch ($extension) {
                        case "pdf":
                            $ext  = ".pdf";
                            $filename = $file . $ext;
                            $giornaliera->toPdf(PUBLIC_PATH . "/reports/", $filename, $note);
                            break;
                        case "xls":
                            $ext  = ".xlsx";
                            $filename = $file . $ext;
                            $path = PUBLIC_PATH . "/reports/";
                            $giornaliera->toExcel($path, $filename, $note);
                            // da attivare se il download crea caratteri strani
                            // $this->_downloadExcel($path, $filename);
                            break;
                        case "txt":
                            break;
                    }
                    Prisma_Logger::log("Giornaliera generated !");
                    Prisma_Logger::logToFile("Giornaliera generated !");
                    Prisma_Timer::stop();
                    Prisma_Logger::logToFile( Prisma_Timer::elapsedTime(5,true,"Tempo per generare il file $ext: ") );
            } catch (Exception $ex) {
                Prisma_Logger::logToFile ( $ex->getMessage() );
            }
        }
    }

    /**
     * function downloadExcel
     *
     * Funzione da attivare nel caso in cui il download
     * crea un file con caratteri strani a causa di righe vuote all'inizio della pagina
     *
    */
    private function _downloadExcel($path, $filename)
    {
        $this->disableAll();
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $url = $request->getScheme() . '://'
                . $request->getHttpHost() . '/'
                . $request->getBaseUrl() . '/../download.php' ;
        $data = array(
            'path' => $path,
            'file' => $filename
        );
        $query = http_build_query($data);
        $this->_helper->redirector->gotoUrlAndExit($url . '?' . $query);
    }

    /**
     * Restituisce la giornaliera annuale di un utente specifico
     */
    public function getAnnualeByUserAction()
    {
        $this->disableView();
        $format = 'yyy-MM-dd';
        $ammualita = array();
        Prisma_Timer::start();
        Prisma_Timer::start(1);
        $inizio = new Zend_Date('2013-01-01');
        $fine   = new Zend_Date('2013-12-31');
        
        for($i = $inizio->get(Zend_Date::DAY_OF_YEAR); $i<=$fine->get(Zend_Date::DAY_OF_YEAR); $i++ ) {
            $day = $inizio->toString($format);
            $annualita[$day] = null;
            $inizio->addDay('+1');
        }
        
        Prisma_Timer::stop(1);
        $annualita['giorni_processati'] = count($annualita);
        $annualita['tempo_impiegato_creazione_array_giorni_processati'] = Prisma_Timer::elapsedTime(5, null, null, 1);
                        
        if ($this->_request->isGet()) {
            $params = $this->_request->getParams();
            
            $map = new Application_Model_EventiMapper();
            $eventi = $map->findByUserAndRange(30, array('start' => '2013-01-01', 'stop' => '2013-12-31'));
            /*
            $days =  array();
            $days['giorni_assenze'] = $eventi->count();
            
            foreach($eventi as $k => $row) {
                $key = $row->giorno;
                $days[$key] = $row->toArray() ;
            }
            foreach($days as $k => $v) {
                $annualita[$k] = $v;
            }
            
             */
            $annualita['giorni_assenze'] = $eventi->count();
            foreach($eventi as $k => $row) {
                $key = $row->giorno;
                $annualita[$key] = $row->toArray() ;
            }
            
            Prisma_Timer::stop();
            $annualita['array_generato_in'] =  Prisma_Timer::elapsedTime();
            Prisma_Logger::log( $annualita);
        }

    }

    /**
     * Restituisce la giornaliera mensile di un utente specifico
     */
    public function getMensileByUser()
    {
        if ($this->_request->isPost()) {
            $params = $this->_request->getParams();
          }
    }





}
