<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CalendarioController
 *
 * @author luca
 */
class CalendarioController extends Zend_Controller_Action 
{
        
    public function preDispatch() {
         
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
            $this->_redirect('auth/login');
            echo 'LOGGATI';
        }
        $this->user_id = $auth->getIdentity()->user_id;
    }
    
    
    public function init() {
       // $this->_helper->viewRenderer->setNoRender();
    }
    
    
    public function indexAction() {}
    
    
    public function mensileAction() {    
         
        //controllo se sto chiedendo mese e anno dalla barra o dalla select 
        if($this->getRequest()->getMethod() == 'POST') {
           $year  = $this->_request->getParam('post-anno');
           $month = $this->_request->getParam('post-mese');
        } else {
            $year  = $this->_request->getParam('anno');
            $month = $this->_request->getParam('mese');
        }
              
        
        if('' == $year) {
            $year = date('Y');
        } 
        
        if('' == $month || $month > 12) {
            $month = date('n');
        }
                
        $eventi = new Application_Model_DbTable_Eventi();
               
        $value = array(
            'year' => $year,
            'month' => $month
        );
        
        $user = new Application_Model_UserMapper();
        $role = $user->getRole($this->user_id);
        if('Amministratore' !== (string)$role) {
            $value['user_id'] = $this->user_id;
        }
       
        if('Sostituto' == (string)$role) {
            $value['sostituto_id'] = $this->user_id;
        }
                
        $events = $eventi->findByDate($value);

        // ricerco eventuali giri del periodo
        $giroTable  = new Application_Model_DbTable_Giro();
        $eventiGiro = $giroTable->findByDate($value);



        //@todo elenco festività nazionali e per sede
        $map = new Application_Model_FestivitaMapper();
        $feste = $map->getFestePerMese($month);

        //Zend_Debug::dump($res);

       // Zend_Debug::dump($events);

        /**
         * ANNO
         * MESE
         * EVENTI UTENTE: si riferisce all'insieme di assenze per il periodo
         * EVENTI GIRO
         * EVENTI FESTE
         */
        $calendar = new Application_Model_Calendario($year, $month, $events, $eventiGiro, $feste);
        //print_r($events);
        $this->view->calendar = $calendar;
        $this->view->year     = $year;
        $this->view->month    = $month;
        $this->view->role     = $role;
        $this->view->id       = $this->user_id;
       
         
    }
    
    /**
     * 
     */
    public function giornalieraAction() {
        
        //controllo se sto chiedendo mese e anno dalla barra o dalla select 
       if($this->getRequest()->getMethod() == 'POST') {
           $year  = $this->_request->getParam('post-anno');
           $month = $this->_request->getParam('post-mese');
       } else {
            $year  = $this->_request->getParam('anno');
            $month = $this->_request->getParam('mese');
        }
        if('' == $year) {
            $year = date('Y');
        } 
        if('' == $month || $month > 12) {
            $month = date('n');
        }
        
        $EM = new Application_Model_EventiMapper();
        $UM = new Application_Model_UserMapper();
        //$users = $UM->getAllUsers(0,1);
         
        $date = array(
            'YEAR'  => $year,
            'MONTH' => $month
        );
        
        // elenco utenti nel mese/anno specificato
        $users = $UM->getUtentiAssunti($date);
        
        
        $periodo = array(
            array(
                'function' => 'YEAR',
                'colname'  => 'giorno',
                'value'    => $year
            ),
            array(
                'function' => 'MONTH',
                'colname'  => 'giorno',
                'value'    => $month
            )
                 
        );
        
        foreach($users as $user){
            
            /* sole se devo mostrare le sostituzioni
            if(!$row->isSostituto()) {
                $e = $EM->findByUserId($row->getId(), null, $periodo) ;
            } else {
                $e = $EM->findByUserId($row->getId(), true, $periodo) ;
            }
            */
            $e = $EM->findByUserId($user->getId(), null, $periodo) ;
            $user->insertEvents($e);
        }
        
        $this->view->users    = $users;
        $this->view->year     = $year;
        $this->view->month    = $month;
    }
    
    
    /**
     * 
     */
    public function dailyAction() {
        $dayOfWeek = array(
                        '0' => 'D',
                        '1' => 'L',
                        '2' => 'M',
                        '3' => 'M',
                        '4' => 'G',
                        '5' => 'V',
                        '6' => 'S'
                );

        $mesi = array(
               "1" => "Gennaio", 
               "2" => "Febbraio",
               "3" => "Marzo",
               "4" => "Aprile",
               "5" => "Maggio",
               "6" => "Giugno",
               "7" => "Luglio",
               "8" => "Agosto",
               "9" => "Settembre",
               "10" => "Ottobre" ,
               "11" => "Novembre",
               "12" => "Dicembre"
        );
        $EM = new Application_Model_EventiMapper();
        $UM = new Application_Model_UserMapper();
        $users = $UM->getAllUsers(0,1);
        
        
        
        $periodo = array(
            array(
                'function' => 'YEAR',
                'colname'  => 'giorno',
                'value'    => '2012'
            ),
            array(
                'function' => 'MONTH',
                'colname'  => 'giorno',
                'value'    => '11'
            )
                 
        );
        
        foreach($users as $user){
            $e = $EM->findByUserId($user->getId(), null, $periodo) ;
            $user->insertEvents($e);
        }
        
        $anno = '2012';
        $mese = '11';
        $pasquetta = Application_Model_Pasqua::pasquetta($anno);
        $sabato   = false;
        $domenica = false; 

        $table = "<table border=1 width=\"100%\"><thead><th></th>";
        
        if($mese < 10) {
               $mese = '0'.$mese; 
        }
        //procedura per il colore dei giorni del mese
        for($i = 1; $i<=cal_days_in_month(CAL_GREGORIAN, $mese, $anno); $i++)  {
            //per stampare il giorno della settimana devo determinarne il numero
            $dOfW = date('w', mktime(0,0,0,$mese, $i, $anno));
            if($i< 10) {
                $i = '0'.$i;
            }
            $domenica  = Application_Service_Tools::isSunday(date($anno.'-'.$mese.'-'.$i));
            $adesso    = new DateTime($anno .'-'.$mese.'-'.$i);
            $adesso    = $adesso->getTimestamp();
            $festivita = Application_Service_Tools::isHoliday(date($anno.'-'.$mese.'-'.$i));
            
            
            if(!$domenica && ($adesso != $pasquetta) && !$festivita ){
                $table .= "<th width=\"25px\">$dayOfWeek[$dOfW] <br> $i</th>";
            } else {
                $table .= "<th width=\"25px\" bgcolor=\"#FF0000\">$dayOfWeek[$dOfW] <br> $i</th>";
            }
        }  //fine procedura giorni
        
        $uid = 0;
        foreach($users as $user){
            $uid++;
            $user_id = $user->getId();
            
            if($uid % 2 == 1) {
                $class = 'dispari';
            } else {
                $class = 'pari';
            }
            
            
            $table .= "<tr class=\"$class\">";
            $table .= "<td><b>".$user->getAnagrafe()."</b></td>";
            for($i = 1; $i<=cal_days_in_month(CAL_GREGORIAN, $mese, $anno); $i++)  {
                if($i< 10) {
                    $i = '0'.$i;
                }
                              
               $D = $anno .'-'.$mese.'-'.$i;
               $sabato   = false;
               $domenica = false;
               $sabato   = Application_Service_Tools::isSaturday($D);
               $domenica = Application_Service_Tools::isSunday($D);
               
                //STAMPO L'ASSENZA
                    $e = array();
                    $e = $user->getEvents();
                    
                    if(!$user->isAssunto($D)) {
                        $table .= "<td align=\"center\"></td>";
                    } else {
                        if( @array_key_exists($D, $e[$user->getId()]) ) {
                            $x = $e[$user->getId()];
                            $table .= "<td align=\"center\" bgcolor=\"#00FF00\">" . $this->siglatipo($x[$D]) ."</td>";
                        } else {
                            if($D <= date('Y-m-d')) {
                                if($sabato) {
                                    $table .= "<td align=\"center\">" . $user->getContratto()->getRidotto() . "</td>";
                                } elseif($domenica) {
                                        $table .= "<td align=\"center\" bgcolor=\"#ffffff\"></td>";
                                } elseif(Application_Service_Tools::isHoliday($D, $user->getSede()->getSedeId())) {
                                        $table .= "<td align=\"center\" bgcolor=\"#FFffff\"></td>";
                                } else {
                                           $table .= "<td align=\"center\">" . $user->getContratto()->getPieno() . "</td>";
                                }
                            } else {
                                    $table .="<td align=\"center\"></td>";
                            }
                        }
                    }
                                 
            }
           $table .= "</tr>";
        }   
       $table .= "</thead>";
       $table .= "</table>";
       //$this->view->table = $table;
    }

    
    
    /**
     * Stampa Pdf
     */
    public function stampaAction() {
       
    }
    
    /**
     * 
     */
    public function excelAction()
    {
        $this->_helper->layout()->disableLayout(); 
        
        $EM = new Application_Model_EventiMapper();
        $UM = new Application_Model_UserMapper();
        $year = date('Y');
        $month = date('n');
        $date = array(
            'YEAR'  => $year,
            'MONTH' => $month
        );
        
        // elenco utenti nel mese/anno specificato
        $users = $UM->getUtentiAssunti($date);
        $periodo = array(
            array(
                'function' => 'YEAR',
                'colname'  => 'giorno',
                'value'    => $year
            ),
            array(
                'function' => 'MONTH',
                'colname'  => 'giorno',
                'value'    => $month
            )
        );
        
        foreach($users as $user)
        {
            $e = $EM->findByUserId($user->getId(), null, $periodo) ;
            $user->insertEvents($e);
        }
        
        $this->view->users    = $users;
        $this->view->year     = $year;
        $this->view->month    = $month;
        $filename = "giornaliera_" . $month . "_" . $year . ".xls";
        header ("Content-Type: application/vnd.ms-excel");
        header ("Content-Disposition: inline; filename=$filename");
    }
    

    
    
    
    
    
}


