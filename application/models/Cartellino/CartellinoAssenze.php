<?php
/**
 * Created by PhpStorm.
 * User: Luca
 * Date: 04/04/2018
 * Time: 16:15
 */

class Application_Model_Cartellino_CartellinoAssenze extends Application_Model_Cartellino_CartellinoDecorator
{

    /* simbolo da inserire in caso di permessi orari */
    const SIMBOLO_PERMESSI = "²";

/* simbolo da inserire in caso di FIS orari */
    const SIMBOLO_FIS = "³";

    /**
     * Crea un array con i giorni di assenza per l'utente
     *
     */
    public function getEvents()
    {

        $events = array();
        $uid    = $this->_data['user']['id'];
        $range  = array(
            'start' => $this->_data['date']['first_day_iso8610'],
            'stop'  => $this->_data['date']['last_day_iso8610']
        );

        $em     = new Application_Model_EventiMapper();
        $result = $em->findByUserAndRange($uid, $range);

        if(!$result) { return $events; }

        # @todo come gestire i doppioni lo stesso giorno per un singolo user?
        $value  = array();
       
        foreach($result as $k => $row) {
           // Prisma_Logger::logToFile($row->toArray());
            if(!isset($events[$row->giorno])) {
                $value  = array();
            }
                $value['sigla']       = $row->sigla;
                $value['descrizione'] = $row->descrizione_admin;
                $value['fulltime']    = $row->fulltime;
                $value['qta']         = $row->qta;
                $value['ferie']       = $row->ferie;
                $value['permesso']    = $row->permesso;
                $value['malattia']    = $row->malattia;

                //@ inserito il 14/4/2021
                $value['assenza_oraria'] = $row->assenza_oraria;
                $events[$row->giorno][] =  $value;
        }
      //  Prisma_Logger::logToFile( $events );
        return $events;

    }


    /**
     * @return mixed
     * @throws Zend_Date_Exception
     */
    public function genera()
    {
        $this->_data = parent::genera();
        $events = $this->getEvents();
        //Prisma_Logger::logToFile(json_encode($events));
        foreach($events as $iso8610 => $value) {

            //mostra il valore dell'assenza e le sue caratteristiche 
           // Prisma_Logger::logToFile( $value );

            $date = new Zend_Date($iso8610);
            $day = $date->toString('d');
            $this->_data['giornaliera'][$day]['assenze'][] = $value;
             
            
            //@29/06/2021
            //refactor: passo un array intero nel caso in cui in uno stesso giorno abbia due tipi di 
            // assenze divere, che devono solo poter essere di tipo permesso mattina e sera fondamentalmente
            //$this->popolaCartellinoFinale($day, $value[0]);
            $this->popolaCartellinoFinale($day, $value);
        }

        $this->checkTotale();
        return $this->_data;
    }


    /**
     * creare classe apposita
     */
    private function  checkTotale() {
        if( $this->_data['totale']['ore_lavorate']  < 0 ) {
            $this->_data['totale']['ore_lavorate']  = 0;
        }
         if( $this->_data['totale']['giorni_lavorati']  < 0) {
             $this->_data['totale']['giorni_lavorati']  = 0;
         }

    }


    /**
     * @param $day
     * @param $assenza
     * note: qui è possibile inserire i simboletti nelle celle in base a delle regole
     */
    private function popolaCartellinoFinale($day, $assenze) {
       // console_log("popolaCartellinoFinale");

        //console_log(json_encode($this->_data));


        Prisma_Logger::logToFile("DAY: " . $day);
        /*
                if($day == 26 ) {
                    Prisma_Logger::logToFile(json_encode($assenza));
                }
        */
       // Prisma_Logger::logToFile($day);
       // Prisma_Logger::logToFile($assenza);
        $dati = $this->_data['giornaliera'][$day];
       // Prisma_Logger::logToFile($assenze);
        $visualizzatoreOre = isset($dati['ore_lavorative']) ? (double)$dati['ore_lavorative'] : 0;
        $visualizzatoreOre = (double)$visualizzatoreOre;
        $giorno = $dati['giorno'];
        foreach($assenze as $key => $assenza) {           
        
            // @todo  ASSENZE DIVERSE DAI PERMESSI ORARI
            if($assenza['fulltime'] == 1) {
                $ore_lavorative = isset($this->_data['giornaliera'][$day]['ore_lavorative'])  ? $this->_data['giornaliera'][$day]['ore_lavorative']  : 0;
                $this->_data['totale']['ore_lavorate'] -= (double)$ore_lavorative;
                $this->_data['totale']['giorni_lavorati'] -= 1;

            // Prisma_Logger::logToFile($this->_data['totale']['giorni_lavorati']  ." => ". $assenza['sigla']);
                $this->_data['cartellino'][$day] = $assenza['sigla'] ;

                /*@todo PERMESSO GIORNALIERO*/
                if($assenza['sigla'] == 'PG') {
                    /* qui devo sommare */
                    $this->_data['totale']['permessi'] +=  $visualizzatoreOre;
                }
                //$result = $assenza['sigla'] . " ";

            }  else if($assenza['assenza_oraria']) { // @TODO PERMESSI ORARI vedere linea 50
                //Prisma_Logger::logToFile("SI");
                $orePermessoUtilizzate = (double)$assenza['qta'];

                $this->_data['totale']['ore_lavorate'] -= $orePermessoUtilizzate;

            // Prisma_Logger::logToFile( $this->_data['totale']['ore_lavorate']  . " =>" . $orePermessoUtilizzate);

                if('sabato' == $giorno) {
                    if($orePermessoUtilizzate >= $visualizzatoreOre) {

                        $this->_data['totale']['giorni_lavorati'] -= 1;
            //          Prisma_Logger::logToFile($this->_data['totale']['giorni_lavorati']  ." => ". $assenza['sigla'] . "(sabato)");
                    }
                }
                Prisma_Logger::logToFile("Ore utilizzate per permesso: " .  $orePermessoUtilizzate );
                $visualizzatoreOre -=  $orePermessoUtilizzate;
                Prisma_Logger::logToFile("Ore lavorate il giorno: " .  $visualizzatoreOre  );
                // aggiungo la sigla alle ore da visualizzare
                
                //bug: se ci sono più permessi lo stesso giorno questa parte viene ripetuta
                if( $assenza['permesso'] ) {
                    $this->_data['cartellino'][$day] = $visualizzatoreOre . self::SIMBOLO_PERMESSI ;
                } else {
                    $this->_data['cartellino'][$day] = $visualizzatoreOre  . self::SIMBOLO_FIS ;
                }
               // Prisma_Logger::logToFile($visualizzatoreOre);
                //@todo CARTELLINO FINALE       
 

            }  else  {
                

            }
            
            if($assenza['ferie'] == 1) {
                //console_log(json_encode($this->_data['giornaliera'][$day]));
                if( array_key_exists('sabato_lavorativo' , $this->_data['giornaliera'])
                 && $this->_data['giornaliera']['sabato_lavorativo'] == false ) {                   
                    $this->_data['totale']['ferie'] += 1.2;
                } else {                    
                    $this->_data['totale']['ferie']++;
                }                
            } elseif($assenza['permesso'] == 1) {
                $this->_data['totale']['permessi'] += $assenza['qta'];
            } elseif($assenza['malattia'] == 1) {
                $this->_data['totale']['malattia']++;
            } elseif($assenza['assenza_oraria'] == 1) { //@inserted 14/04/2021
                $this->_data['totale']['fis_oraria'] += $assenza['qta'];
            }
        }


       




    }


}
