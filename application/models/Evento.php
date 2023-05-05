<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Evento
 * CLASSE CHE MI GENERA I SINGOLI GIORNI DA INSERIRE NEL DATABASE EVENTI
 * @author Luca
 */
class Application_Model_Evento {
    
    
    protected $_events = array();
    
    protected $_assenza_id;
    
    protected $_user_id;
    
    protected $_sostituto_id;
    
    protected $_totaleGiorni;
    
    public function __construct() {
        $this->_dbAssenze = new Application_Model_AssenzeMapper();
    }
    
    
    
    public function creaEventoDaAssenza($assenza_id) {
        Prisma_Logger::logToFile("##### CREO EVENTO");
        $assenza             = $this->_dbAssenze->getAssenzaById($assenza_id);
        $this->_assenza_id   = $assenza->assenza_id;
        $this->_user_id      = $assenza->user_id;
        $this->_sostituto_id = $assenza->sostituto_id;
        return $this->generaGiorniEffettivi( $assenza->dateStart,  $assenza->dateStop, $assenza->tipologia_id);
    }

    /**
     *
     * per malattia e maternità si considera anche la domenica e i festivi
     * @param $start
     * @param $stop
     * @param null $tipologia
     * @return array
     */
    public function generaGiorniEffettivi($start, $stop, $tipologia = null) {

        if($tipologia != null) {
            $map  = new Application_Model_TipologiaMapper();
            $tipo = $map->find($tipologia);
        }



        $start = new DateTime($start);
        $stop  = new DateTime($stop);
        $diff  = $start->diff($stop);
        $intervallo = (int)$diff->format('%a') ;
        
        $totale = $intervallo + 1;
         
        $start->modify('-1 day');



        $userMapper = new Application_Model_UserMapper();

        $user = $userMapper->find($this->_user_id);

        $sede = $user->getSede();
        $sedeId = null;
        if($sede->getSedeId()) {
            $sedeId = $sede->getSedeId();
        }



        for($i = 0; $i <= $intervallo; $i++ ) {
           $isPatronalDay = false;
           $festivo = 0;
           $date = $start->modify('+1 day')->getTimestamp();
             
           if( (!$tipo->isMalattia()) && (!$tipo->isMaternita())) {

               if (Application_Service_Tools::isSunday($date)) {
                   //echo 'domenica :' . date('Y-m-d', $date) .'<br>';
                   $totale--;
                   continue;
            
                   //refactor 04/12/2020
               } elseif (!Application_Service_Tools::isHolidayLavorativo($date)) { 
                    if(Application_Service_Tools::isHoliday($date)) {
                        $totale--;
                        continue;
                    }                   
               } elseif (Application_Service_Tools::patrono($date, $sedeId)) {
                   $festivo = 1;
                   $isPatronalDay = true;
                   $totale--;
                  // Prisma_Logger::logToFile("patrono: " . $festivo);
                   //echo 'festivo .' . date('Y-m-d', $d).'<br>';

                   //@todo fermo l'esecuzione per non fare inserire il patrono negli eventi
                   continue;
               } elseif (Application_Service_Tools::isEasterMonday($date)) {
                   $totale--;
                   continue;
               }
           }

            //@todo updated 24 marzo 2018
           if($isPatronalDay) {
                $db = new Application_Model_DbTable_Tipologia();
                $object = $db->getPatronalId();
                $tipologia = $object->tipologia_id;
           } else {
               $tipologia = $tipo->getId();
           }


            //ogni array rappresenta una riga da inserire nella tabella eventi
           $this->_events[] = array(
                'assenza_id'   => $this->_assenza_id,
                'user_id'      => $this->_user_id,
                'sostituto_id' => $this->_sostituto_id,
                'giorno'       => date('Y-m-d', $date),
                //@todo updated 24 marzo 2018
                'tipologia_id'    => $tipologia,
                'festivo'      => $festivo,
                'date_insert'  => date('Y-m-d H:i:s')
           );
        }
       // echo $totale;
        $this->_totaleGiorni = $totale;
        
       // echo '<br>';
       // print_r($this->_events);
        return $this->_events;
        
    }
    
    
    public function getGiorniEffettivi() {
        return $this->_events;
    }
    
    public function getTotaleGiorni() {
        return $this->_totaleGiorni;
    }
    
    public function cancellaEvento($assenza_id) {
        
    }
    
   
    
    
    
    
    
    
    
}

 
