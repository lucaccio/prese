<?php

/**
 * Created by PhpStorm.
 * User: Fabiola
 * Date: 04/04/2018
 * Time: 16:23
 */




class Application_Model_Cartellino_CartellinoOre extends Application_Model_Cartellino_CartellinoDecorator
{


    const NO_CONTRATTO = "NC";

    /*
        riguarda la lista di contratti utili nel periodo selezionato per singolo utente
        quindi, in questa vasriabile, vengono caricati i dati per un solo user alla volta
    */
    private $_list_of_contracts = array();

    /*
    private $_totali = array(
        'ore_lavorate'    => 0,
        'giorni_lavorati' => 0
    );
*/

    public function genera()
    {
        // TODO: Implement genera() method.
        $this->_data =  parent::genera();
        $this->setContracts($this->_data['user']['id'], $this->_data['date']['first_day_iso8610']);

        // @todo possibile refactoring
        // $contrattiUtenti = $contrattiModel->getContracts(uid, period);
        // $this->_generaCartellinoOreUtente($contrattiUtenti); oppure
        //  $this->_generaCartellinoOreUtente(uid $contrattiUtenti[uid])

        $this->_generate();
        return $this->_data;
    }

    /**
     *  Crea una Collection di utenti e relativi dettagli orari recuperati dai rispettivi contratti
     *
     *
     * @todo da spostare in altra classe
     * @ funziona
     *
     * @param $uid
     * @param $date
     * @throws Exception
     */
    private function setContracts($uid, $date)
    {
        $map = new Application_Model_UserMapper();

        // verifico la presenza di contratti nella nuova tabella contratti
        $contractsList = $map->userGetContracts($uid);

        // se non risulta almeno un contratto nella nuova tabella, allora prendo il contratto
        // dalla vecchia tabella e lo inserisco nella nuova tabella users_contracts
        if ($contractsList->count() == 0) {
            // inserisco nella tabella users_contracts il contratto della vecchia tabella
            $this->migrateContract($uid);
        }

        /* restituisco l'elenco di contratti dell'utente per il mese da processare */
        $contratti  = $map->userGetContractsByDate($uid, $date);
        if ($contratti->count()) {
            //per ogni contratto nel mese devo recuperarne le caratteristiche
            foreach ($contratti as $k => $row) {

                $parentRow = $row->findParentRow('Application_Model_DbTable_Contratti');
                $righeDettagliContratto   = $parentRow->findDependentRowset('Application_Model_DbTable_ContrattiDetails');
                if ($righeDettagliContratto->count()) {
                    //ogni dep è una riga dettagli per il determinato contrqtto che sto consumando (da contratti_details)
                    foreach ($righeDettagliContratto as $k => $dep) {
                        //$dep['ref'] == mattina | sera
                        $details[$dep['ref']] =  $dep->toArray();
                       //LOG Prisma_Logger::logToFile($details);
                    }
                }
                //Prisma_Logger::logToFile("--------------- inio lista contratti per il mese");

                // @ inserito il 25 marzo 2020
                //TODO probabilmente qui devo decidere come comportarmi quando ho 53 settimane
                $dateOfStartContract = new Zend_Date($row->start);
                
                //mi dice che la settimana di partenza del contratto in uso è pari o dispari
                //serve per il conrtatto misto
                $weekStart = ($dateOfStartContract->get(Zend_Date::WEEK) % 2); 

                //per ogni key c'è un contratto con inizio/fine e i dettagli delle ore matina e sera
                $this->_list_of_contracts[] = array(
                    'start'   => $row->start,
                    'end'     => $row->stop,
                    'details' => $details,
                    'info' => array( // @ inserito il 25 marzo 2020
                        'uid'               => $uid,
                        'misto'             => $row->misto,
                        'weekStart'         => $weekStart,
                        'weekStartContract' => $details['mattina'],       // per il contr.misto(bisettimanale) indico con quale inizio
                        'weekAlternateContract' => $details['sera'] //indico il contratto per la settimana alternata 
                    )


                );

                //   Prisma_Logger::logToFile($this->_list_of_contracts);
                //   Prisma_Logger::logToFile("fine lista contratti per il mese ######");

            }
            //Prisma_Logger::logToFile("Contratto per " . $this->_data['user']['nome'] . json_encode($this->_list_of_contracts), true, "contratti.txt");
        } else {
            Prisma_Logger::logToFile('Nessun contratto presente nel periodo selezionato per ' . $this->_data['user']['nome']);
        }
    }

    /**
     * @todo da spostare in altra classe
     * Fa una migrazione dal vecchio sistema dei contratti al nuovo sistema
     *
     */
    public function migrateContract($uid)
    {
        Prisma_Logger::logToFile("Migrazione contratto per l'utente con ID: " . $uid);
        /**
            aggiorno la tabella contratti (la tabella di nuova generazione)
            inserendo i vecchi valori presi dalla vecchia tabella dei contratti
         */
        $gateway = new Application_Model_UserMapper();
        $row     = $gateway->getMeUser($uid);

        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            if ($row->getCessazione() == false) {
                $cessazione = null;
            } else {
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
     * @todo da spostare in altra classe e/o ridefinire
     * @param $s
     * @param $e
     * @return array
     */
    protected function _getDateLimit($s, $e)
    {
        $value = array();
        $a     = $this->_data['date']['first_day_iso8610'];
        $b     = $this->_data['date']['last_day_iso8610'];

        if ($s <= $a) {
            $value['start'] = $a;
            $cs = $a;
        } else {
            $value['start'] = $s;
            $cs = $s;
        }

        if ($e >= $b) {
            $value['stop'] = $b;
            $ce = $b;
        } else {
            $value['stop'] = $e;
            $ce = $e;
        }
        return $value;
    }


    /**
     *  Genera il cartellino
     */
    private function _generate()
    {

        $cartellino = array();

        /* verifico la presenza della Collection di Utenti e
            relativi dettagli orari dei rispettivi contratti
        */
        if (!count($this->_list_of_contracts)) {
            $user = $this->_data['user']['nome'];
            Prisma_Logger::logToFile("Lista contratti utenti vuota, impossibile generare la giornaliera delle ore per $user");
        } else {
            foreach ($this->_list_of_contracts as $k => $contratto) {

                /** 26 marzo 2020 */
                $rangeDateContratto['contratto'] =  $contratto;

                /* dettagli del contratto in termini di ore/giorno settimana */
                $rangeDateContratto['details'] = $contratto['details'];

                /* da in formato iso8610 */
                $start = $contratto['start'];

                /* mi restituisce una data in iso8610 oppure null value */
                $end   = $contratto['end'];

                /* se non trovo fine contratto allora l'user va processato per tutto il mese
                altrimenti fino alla data utile del contratto */
                (!$end) ? $end = $this->_data['date']['last_day_iso8610'] : $end = $end;

                // per ogni contratto da processare verifico in quale range di date del mese va inserito
                $limit = $this->_getDateLimit($start, $end);

                // creo il range di date di un contratto utile
                $rangeDateContratto['range'] = Prisma_Utility_Date::createRangeOfDates($limit['start'], $limit['stop']);

                //  Prisma_Logger::logToFile($rangeDateContratto);

                // @25 marzo 2020 in pieno corona vairus !!!
                // aggiungo dati per permettere contratti misti un week un tot di ore e l'altro un tot divero di ore
                $rangeDateContratto['info']  = $contratto['info'];


                $this->_generaGiornaliera($this->_data,   $rangeDateContratto);
            }
        }
    }


    /**
     * Genera la giornaliera per ogni contratto dell'utente presente nel mese di riferimento
     * @param $range
     * @param $contratto
     */
    protected function _generaGiornaliera($range, $contratto)
    {
        try {

            /* array $cDetails mi dice le ore lavorate di mattina e sera durante la settimana
               (in base al contratto?)
            */
            $cDetails = $contratto['details'];
            //Prisma_Logger::logToFile($contratto);

            // range di date del contratto, ovvero inizio e fine contratto
            $cRange   = $contratto['range'];

            foreach ($range['giornaliera'] as $giornoDelMese => $string) {
                $iso8610 = $string["iso8610"];
                if (in_array($iso8610, $cRange)) // qui vuol dire che risulta un contratto per questo giorno specifico
                {

                    /** TODO contratto Paola Loi @25marzo 2020 */

                    //se il contratto è standard allora tutto normale
                    if ($contratto['info']['misto'] == 0) {
                        /** restituisce le ore lavorate (in base al contratto)  per il giorno che gli passo */
                        $ore = $this->_getContractSchedule($iso8610, $cDetails);
                    } else {  
                        //TODO:29/01/2021  
                        // nuova implementazione per contratto con orari diversi a settimane alterne

                        //è la data del giorno del mese nella quale devo inserire le ore lavorate
                        $date = new Zend_Date($iso8610);
                        
                        // se siamo in ora legale aggiungo 1 ora alla data da processare
                        if ( date('I', $date->getTimestamp() ) )
                        {
                            Prisma_Logger::logToFile('DST');
                            $date->add('01:00:00', Zend_Date::TIMES);
                        }
 
                        // è la data d'inizio contratto
                        $firstDateContract = new Zend_Date($contratto['contratto']['start']);
                       // Prisma_Logger::logToFile( $cRange  );

                        
                       // assegno il bisettimanale in base al numero della settimana attuale (ovvero pari o dispari)
                       // errato
                       // $numWeek = floor( ($date->sub($firstDateContract)->toValue() / 60 / 60 / 24)   /  7 );
                       // giusto
                        $numWeek = $date->get(Zend_Date::WEEK);

                        //  Prisma_Logger::logToFile( "settimana num : " .  $numWeek);
                        
                       
                        if (floor($numWeek) % 2 == $contratto['info']['weekStart']) {
                            
                            $c = $contratto['info']['weekStartContract']; // dentro c'è la riga dei contratti_details[mattina o sera] come array
                            $ore = $this->_getContractSchedule($iso8610, $c);
                           // Prisma_Logger::logToFile( "giorno: " . $iso8610 . "week: " . $numWeek . " ore: " . $ore );
                        } else {
                            $c = $contratto['info']['weekAlternateContract'];// dentro c'è la riga dei contratti_details[mattina o sera] come array
                            $ore = $this->_getContractSchedule($iso8610, $c);
                           // Prisma_Logger::logToFile( "giorno: " . $iso8610 . "week: " . $numWeek . " ore: " . $ore );
                        }
                    }



                    /** aggiorno i dati con le ore lavorate */
                    $res[$iso8610] = $ore;
                    $this->popolaArrayConOreGiornalieriereLavorate($giornoDelMese, $ore);
                } else {
                    /** se qui, allora per questo giorno non c'è contratto */
                    $this->popolaArrayConNoContrattoPrsente($giornoDelMese);
                }
            }

           //LOG Prisma_Logger::logToFile($this->_data);
        } catch (Exception $e) {
            Prisma_Logger::logToFile($e->getMessage());
        }
    }


    /**
     * Setto NO CONTRATTO nel giorno specificato (mi serve per eventuali festivita da non calcolare)
     * @param $giornoDelMese
     */
    protected function popolaArrayConNoContrattoPrsente($giornoDelMese)
    {
        if (!isset($this->_data['giornaliera'][$giornoDelMese]['ore_lavorative'])) {
            if ($this->_data['giornaliera'][$giornoDelMese]['giorno'] != "domenica") {
                $this->_data['giornaliera'][$giornoDelMese]['ore_lavorative'] = self::NO_CONTRATTO;
            } else {
                $this->_data['giornaliera'][$giornoDelMese]['ore_lavorative'] = "";
            }
        }
    }



    /**
     *
     * @modificato 26/04/2018
     * @param $giornoDelMese
     * @param $oreLavorate
     */
    protected function popolaArrayConOreGiornalieriereLavorate($giornoDelMese, $oreLavorate)
    {

        $this->_data['giornaliera'][$giornoDelMese]['ore_lavorative'] = $oreLavorate;

        //console_log(json_encode($this->_data));
        //console_log(json_encode( $this->_data['giornaliera'][$giornoDelMese]['giorno']     );
//@13/01/2022
//console_log(" $giornoDelMese  $oreLavorate");

        if( $this->_data['giornaliera'][$giornoDelMese]['giorno']  == 'sabato') {
            if($oreLavorate == 0 ) {
                //console_log("sabato non lavorativo");
                $this->_data['giornaliera']['sabato_lavorativo'] = false;   
            }       
        }
       
                    
      


        /**  CARTELLINO FINALE DA USARE PER I DOCUMENTI UFFICIALI */
        $this->_data['cartellino'][$giornoDelMese] =  $oreLavorate;

        // popolo totali
        if (is_numeric($oreLavorate) && ($oreLavorate > 0)) {
            $this->_data['totale']['_ore_totali']     += $oreLavorate;
            $this->_data['totale']['ore_lavorate']    += $oreLavorate;
            $this->_data['totale']['giorni_lavorati'] += 1;
        }
    }


    /**
     * Restituisce il totale delle ore lavorate un determinato giorno dell'anno ($day)
     * senza contare assenze o festivi
     *
     * @param type $day
     * @param array $list  la tupla di contratti_detail corrispondente o a mattina o a sera
     * @param string $when ( full | morning | evening )
     * @param type $locale
     * @return boolean
     */
    protected function _getContractSchedule($day, $list, $when = 'full', $locale = 'en_US')
    {
        // procedura per determinare le ore di lavoro stabilite dal contratto,
        // per quel determinato giorno della settimana impostato nella variabile $day
        //
        $zd = new Zend_Date($day, Zend_Date::ISO_8601);
        $zd->setLocale($locale);
        $week = $zd->toString(Zend_Date::WEEKDAY_NAME);
        $week = (string) strtolower($week);
        if (isset($list['mattina'][$week]) && isset($list['sera'][$week])) {
            // $mattina e $sera contengono le ore di lavoro del giorno della settimana impostato in $week,
            // come stabilito dal contratto
            $mattina = $list['mattina'][$week];
            $sera    = $list['sera'][$week];

            // questo serve a restituire le ore lavorate:
            // se faccio permesso mattina mi restituisce le ore lavorate di sera e cosi via
            switch ($when) {
                    /*
                case 'morning': // se mi assento di mattina restituisco le ore lavorate di sera
                    if($sera == 0) { return 0; }
                    return $sera;
                    break;
                case 'evening': // se mi assento di sera restituisco le ore lavorate di mattina
                    if($mattina == 0) { return  0; }
                    return $mattina;
                    break;
                */
                case 'full':
                    $totale = $mattina + $sera;
                    if ($totale == 0) {
                        return 0;
                    }
                    return $totale;
                    break;
                    /*
                case 'none':
                    $totale = 0;
                    return $totale;
                    break;
                    */
            }
        } else {
            //@ 25 marzo 2020
            // si tratta di contratto misto che si alterna a seconda della settimana
            // le ore mattina o di sera in realta sono le ore giornaliere perr le diverse settimane
            // faccio cosi per non rifattorizzare il database 
            // in pratica il termine mattina e sera in questo tipo di contratto misto è come se
            // rappresentassero due contratti settimanali diversi e i numeri indicati rappresentano il totale delle ore lavorate
            // durante ogni singolo giorno senza sapere se sono lavorate sia mattina che sera o solo mattina o solo sera
           if($week !== 'sun') {
            return $list[$week];
           } else {
               return false;
           }
            
        }
        // domenica ed eventuali giorni non presenti in db
        return false;
    }
}
