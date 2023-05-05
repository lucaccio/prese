<?php
/**
 * Created by PhpStorm.
 * User: Luca
 * Date: 04/04/2018
 * Time: 16:21
 */
class Application_Model_Cartellino_CartellinoFestivita extends Application_Model_Cartellino_CartellinoDecorator
{

    /** */
    const SIMBOLO_FESTIVITA = "Fv";

    /** */
    const SIMBOLO_PATRONO   = "PT";

    /** */
    const SIMBOLO_PATRONO_LAVORATIVO   = "¹";


    /** @04/12/2020 */
    const SIMBOLO_FESTA_LAVORATIVO   = "¹";

    /** */
    private $_calcolaPasqua = false;


    /**
     * Genera il cartellino delle festivita
     *
     * @return array
     * @throws Zend_Date_Exception
     */
    public function genera()
    {
    
        Prisma_Logger::logToFile( "#### GENERA CARTELLINO FESTIVITA"  );

        $this->_data = parent::genera();

        Prisma_Logger::logToFile( "utente: " . $this->_data['user']['id']    );
 

        
        $mese = $this->_data['date']['mese'];
        $anno = $this->_data['date']['anno'];
        $sede_patrono = $this->_data['user']['configs']['sede_lavoro'];
        $periodo = array(
            'mese' => $mese,
            'anno' => $anno
        );

        /* restituisce l'elenco delle festivita del mese ed della sede p*/
        $elenco = $this->getFestivita($periodo , $sede_patrono);

        if(count($elenco['feste']) > 0) {

            foreach($elenco['feste'] as $iso8610 => $value) {

                /** calcolo la data*/
                $date = new Zend_Date($iso8610);
                $day = $date->toString('d');

                /** inserisco la festivita nella struttura generale per eventuali controlli successivi*/
                //@todo valutare se lasciarla qui o metterla dopo il check sul contratto
                $this->_data['giornaliera'][$day]['festivita'] = $value;

                //BUG FIX 02/09/2019
                /**
                 * in qualche momento non trova l'indice ['ore_lavorative']
                 */
                if(!array_key_exists('ore_lavorative', $this->_data['giornaliera'][$day])) {
                   continue;
                } else {                    
                    /** se NO CONTRATTO per il giorno specioficato, allora passo al giorno successivo dell'elenco*/
                    //bugfix 19/05/2021
                    if($this->_data['giornaliera'][$day]['ore_lavorative'] === "NC") {
                       continue;
                    }
                }
                

                /** controllo del PATRONO */
                $patrono    = $value['patrono'];
                //Prisma_Logger::logToFile($patrono);
              //  Prisma_Logger::logToFile($this->_data['user']['configs']);
              //  Prisma_Logger::logToFile($this->_data['user'] );
                $lavorativo = $this->_data['user']['configs']['patrono_lavorativo'];

                 
                if($patrono) {
                    if($lavorativo) {
                        $this->_data['cartellino'][$day] = $this->_data['giornaliera'][$day]['ore_lavorative'] . self::SIMBOLO_PATRONO_LAVORATIVO;
                    } else {
                        $this->_data['cartellino'][$day]  = self::SIMBOLO_PATRONO;
                        $this->_data['totale']['festivita'] += 1;
                    }
                } else {
                    //refactoring new feature festivita lavorativa es: immacolata 8 dic
                   //@04/12/2020
                    $isFestivitaLavorativa = $this->_data['giornaliera'][$day]['festivita']['lavorativo'] ;
                    // 1) se la festività risulta lavorativa...
                    if( $isFestivitaLavorativa ) {

                        if($this->_data['user']['id'] == 40) {
                            Prisma_Logger::logToFile( " -> lavorativa per il 40"  );
                        }


                        // se nella festività lavorativa qualuno è assente
                        if(isset($this->_data['giornaliera'][$day]['assenze'])) {
                            // inserisco la sigla dell'assenza...
                            $data=  $this->_data['giornaliera'][$day]['assenze'][0][0]['sigla'] ;
                            // aggiungo 1 gg al totale festività del cartellino personale
                            $this->_data['totale']['festivita'] += 1;
                        } else {
                            // ...altrimenti, inserisco le ore lavorate quel giorno pi un simbolo che identifica che è festa
                           $data =  $this->_data['giornaliera'][$day]['ore_lavorative'] . self::SIMBOLO_FESTA_LAVORATIVO;
                        }
                        // aggiorno il cartellino
                        $this->_data['cartellino'][$day] = $data;
                   
                    } else { // 2)  ... altrimenti, se  è festa , ma NON lavorativa 
                        // aggiorno il cartellino
                        if($this->_data['user']['id'] == 40) {
                            Prisma_Logger::logToFile( " -> Festa per il 40"  );
                        }
                        $this->_data['cartellino'][$day]  = self::SIMBOLO_FESTIVITA;
                        // aggiungo 1 gg al totale festività del cartellino personale
                        $this->_data['totale']['festivita'] += 1;
                    }
                    
                }

               
                


                ############## AGGIORNO I TOTALI ################
                //@info log totale - vanno sottratte anche le ore, ma come?
                // forse con i dati dell'user?

                /** se il giorno non è domenica allora...*/
                if($date->toString(Zend_Date::WEEKDAY_DIGIT) > 0) {

                    if( isset($this->_data['giornaliera'][$day]['ore_lavorative']) &&
                                $this->_data['giornaliera'][$day]['ore_lavorative'] != null )
                    {
                        /** @info calcolo totali se è patrono */
                        if($patrono) {
                            if($lavorativo) {
                                /** non modifico nulla*/
                            } else {
                                $this->_data['totale']['ore_lavorate']    -=  $this->_data['giornaliera'][$day]['ore_lavorative'];
                                $this->_data['totale']['giorni_lavorati'] -= 1;
                            }
                        } elseif($isFestivitaLavorativa) { //@04/12/2020
                        /** se la festa è lavorativa non sottraggo nulla */
                        } else {
                            /** aggiorno totale ore e totale giorni lavorati effettivamente */
                            $this->_data['totale']['ore_lavorate']    -=  $this->_data['giornaliera'][$day]['ore_lavorative'];
                            $this->_data['totale']['giorni_lavorati'] -= 1;
                        }
                    }
                } ############# FINE AGGIORNAMENTO TOTALI #############
            }
        }
        $this->checkTotale();
        return $this->_data;
    }


    /**
     * @todo creare classe apposita
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
     * @param $mese
     * @param $sede
     * @return array
     */
    protected function getFestivita($periodo, $sede) {
        $map = new Application_Model_FestivitaMapper();
        $feste = $map->getFesteNazionaliEPerSede($periodo, $sede, false);
        //Zend_Debug::dump($feste);
        return $feste;
    }

    /**
     * @param bool $value
     */
    public function calcolaPasqua($value = true) {
        $this->_calcolaPasqua = $value;
    }



}
