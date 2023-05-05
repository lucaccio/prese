<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Permessi
 *
 * @author Luca
 */
class Application_Model_Permesso
{
    
     const TOTALE_ORE_PERMESSI_ANNUI = 63.90;
     
     protected $_totale;
     
     /**
      * 
      * @param Application_Model_MyDate $date
      * @param type $user
      */
     public function __construct(Application_Model_MyDate $date, $user) {
         
         if($date instanceof Application_Model_MyDate) {
            if(true == $date->same()) {
                if($user) {
                    $sede_id = $user->getSede()->getSedeId();
                 }
                 $this->_totale = $date->countActualDays($sede_id);
             } else {
                 echo "<h4>error</h4>";
             }
         }
         
     }
     
     /**
      * 
      * @return type
      */
     public function getTotale() {
         return $this->_totale;
     }
     
     
     
     
     
     
     
     
     
     
}

 
