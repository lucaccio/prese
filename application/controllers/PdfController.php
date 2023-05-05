<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CaleController
 *
 * @author Luca
 */
class PdfController extends Zend_Controller_Action {

    
    public function init() 
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout()->disableLayout(); 
    }
    
    public function indexAction() {
         //$this->_helper->viewRenderer->setNoRender(false);
    }
    
    /**
     * DEPRECATED (in favore di printAction)
     */
    public function stampaAction() {
        
        include 'fpdf17/fpdf.php';
        $EM = new Application_Model_EventiMapper();
        $UM = new Application_Model_UserMapper();
        $TM = new Application_Model_TipologiaMapper();
        
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
        
        //$this->_helper->viewRenderer->setNoRender(true);
        $eventi = array();
        $dati   = array();
        $data = $this->_request->getParam('data');
        if(!isset($data)) {
            $year   = $this->_request->getParam('anno');
            $month  = $this->_request->getParam('mese');
        } else {
            $data = $this->_request->getParam('data');
                       
            $data = explode("-", $data);
            
            $month = array_search($data[0], $mesi);
            $year = $data[1];
        }
        
        $validYear  = new Zend_Validate_Date(array('format' => 'yyyy'));
        $validMonth = new Zend_Validate_Date(array('format' => 'm'));
        
        if(!$validYear->isValid($year)) {
            $year   = date('Y');
        }
        if(!$validMonth->isValid($month)) {
            $month   = date('n');
        }
        
        
        
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
        
        
        $assunzione = array(
            'year'  => $year,
            'month' => $month
        );
        
        $users = $UM->getAllUsers(0,1,$assunzione, $order = 'ordine ASC');
        $n = 0;
        foreach($users as $user) {
            $e = $EM->findByUserId($user->getId(), null, $periodo) ;
            if(array_key_exists($user->getId(), $e)) {
                $eventi = $e[$user->getId()];
            } else {
                $eventi = array();
            }
                         
            $maxDay = date('t', mktime(0,0,0,$month, 1, $year));
            for($i = 1; $i <= $maxDay; $i++) {
                $giorno = date('Y-m-d', mktime(0,0,0,$month, $i, $year));
                if(@array_key_exists($giorno, $eventi)){
                    
                    $tm = $TM->find($eventi[$giorno]);
                    $dati[$user->getAnagrafe()][$i] = $tm->getSigla(); 
                } else {
                    if($user->isAssunto($giorno)) {
                        if( Application_Service_Tools::isSunday($giorno) ) {
                            $dati[$user->getAnagrafe()][$i] = ''; 
                        } elseif(Application_Service_Tools::isHoliday($giorno, $user->getSede()->getSedeId())) {
                             $dati[$user->getAnagrafe()][$i] = ''; 
                        } elseif(Application_Service_Tools::isSaturday($giorno)) {
                            $dati[$user->getAnagrafe()][$i] = $user->getContratto()->getRidotto(); 
                        } else {
                            $dati[$user->getAnagrafe()][$i] = $user->getContratto()->getPieno(); 
                        }
                    } else {
                        $dati[$user->getAnagrafe()][$i] = ''; 
                    }
                }
                
            }
             
        }
        $pdf = new Application_Model_Pdf();
        
        //Creo l'array con i giorni del mese
        $header=array();
        for($i = 1; $i<=cal_days_in_month(CAL_GREGORIAN, $month, $year); $i++)  {
                  $header[] = $i ; 
        }

        $pdf->SetFont('Arial','',14);
        $pdf->AddPage();
       
        
        $title = "$mesi[$month] $year";
        //$TM = new Application_Model_TipologiaMapper();
        $tipi = $TM->getAll();
        $footer = $tipi;
        //creo la tabella da stampare
        $pdf->BuildTable($title, $header, $dati, $footer);
        $pdf->Output("report/giornaliera-$year-$month.pdf");
        $pdf->Output("giornaliera-$year-$month.pdf", 'I');
        //echo "giornaliera di $title stampata!";
       //$this->_redirect('/pdf/index');
    }
    
    
    public function provaAction() {
        include 'fpdf17/fpdf.php';
        $pdf = new FPDF();
        $pdf->SetFont('Arial','',14);
        $pdf->AddPage();
         $pdf->Output("prova.pdf", 'D');
    }
    
    
    
    /**
     * 
     * 
     * 
     * 
     */
    public function printAction() {
        Prisma_Logger::logToFile( "PDF print" );
        //$this->_helper->viewRenderer->setNoRender(false);
        include '_tcpdf_5.0.002/tcpdf.php';
        //Data loading
        $EM = new Application_Model_EventiMapper();
        $UM = new Application_Model_UserMapper();
        $TM = new Application_Model_TipologiaMapper();
        
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
        
         
        
        $eventi = array();
        $dati   = array();
        $note = $this->_request->getParam('note');
        $data = $this->_request->getParam('data');
        if( !isset($data) or ($data == '') ) {
            $year   = $this->_request->getParam('anno');
            $month  = $this->_request->getParam('mese');
        } else {
            $data = $this->_request->getParam('data');
            $data = explode("-", $data);
            $month = array_search($data[0], $mesi);
            $year = $data[1];
        }
        
        $validYear  = new Zend_Validate_Date(array('format' => 'yyyy'));
        $validMonth = new Zend_Validate_Date(array('format' => 'm'));
        
        if(!$validYear->isValid($year)) {
            $year   = date('Y');
        }
        if(!$validMonth->isValid($month)) {
            $month   = date('n');
        }
        
        
        // create new PDF document
        $pdf = new Application_Model_Tpdf('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Prisma Investimenti Spa');
        $pdf->SetTitle('Stampa Giornaliera di ' . $mesi[$month] . ' ' . $year);
         

        // set default header data
        $pdf->SetHeaderData('', '', 'Prisma Investimenti Spa', 'Giornaliera di ' . $mesi[$month] . ' ' . $year);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        //set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        //set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
 

        // ---------------------------------------------------------

        // set font
        $pdf->SetFont('helvetica', '', 8);

        // add a page
        $pdf->AddPage();

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
        
        
        $assunzione = array(
            'year'  => $year,
            'month' => $month
        );
        
        $users = $UM->getAllUsers(false, 1, $assunzione, $order='ordine ASC');
        $n = 0;
        $patrono = false;
        # creo le righe per ogni utente
        foreach($users as $user) {
            $e = $EM->findByUserId($user->getId(), null, $periodo) ;
            if(array_key_exists($user->getId(), $e)) {
                $eventi = $e[$user->getId()];
            } else {
                $eventi = array();
            }
                         
            $maxDay = date('t', mktime(0,0,0,$month, 1, $year));
            for($i = 1; $i <= $maxDay; $i++) {
                $giorno = date('Y-m-d', mktime(0,0,0,$month, $i, $year));
                if(@array_key_exists($giorno, $eventi)){
                    
                    $tm = $TM->find($eventi[$giorno]);
                    
                    $sigla = $tm->getSigla();
                    
                    if($sigla == 'PM') {
                        
                       if(Application_Service_Tools::isSaturday($giorno)) {
                           $stampa =  '0¹'; 
                       } else {
                           $stampa =  $user->getContratto()->getSera() . '¹'; 
                       }
                       
                       
                    } elseif($sigla == 'PS') {
                        $stampa = $user->getContratto()->getMattina() . '²';
                    } else {
                        $stampa = $sigla;
                    }
                    
                   // $dati[$user->getAnagrafe()][$i] = $tm->getSigla(); 
                 $dati[$user->getAnagrafe()][$i] = $stampa; 
                    
                    
                } else {
                    if($user->isAssunto($giorno)) {
                        $sede_id = $user->getSede()->getSedeId();
                        if($sede_id == '') {
                            $sede_id = 0;
                        }
                        //echo $user->getAnagrafe() . ' sede: ' . $user->getSede()->getSedeId() .'<br>';
                        
                        $patrono = Application_Service_Tools::isPatronSaint($giorno, $sede_id) ;
                        //echo  $user->getSede()->getSedeId(). '<br>';

                        //@todo qui inserisco il simbolo FV
                        // se è domenica nessun simbolo
                        if( Application_Service_Tools::isSunday($giorno) ) {

                            $dati[$user->getAnagrafe()][$i] = ''; 
                        // altrimenti se è un giorno festivo...    
                        } elseif(Application_Service_Tools::isHoliday($giorno, $user->getSede()->getSedeId())) {
     
                            // new feature 04/12/2020
                            // devo verificare se il festivo nazionale è lavortivo
                            if(Application_Service_Tools:: isHolidayLavorativo($date)) {
                                Prisma_Logger::logToFile("HOLIDAYS LAVORATIVO");
                                if(Application_Service_Tools::isSaturday($giorno)) {                                   
                                    $dati[$user->getAnagrafe()][$i] = $user->getContratto()->getRidotto() . '³';
                                } else {
                                    $dati[$user->getAnagrafe()][$i] = $user->getContratto()->getPieno();
                                }
                            } else if( Application_Service_Tools::isSunday($giorno) ) {
                                //...se ricade di domenica scrivo che è FV
                                $dati[$user->getAnagrafe()][$i] = 'FV';
                            } else {
                                // altrimenti 
                                $dati[$user->getAnagrafe()][$i] = 'FV';
                            }


                        } elseif(Application_Service_Tools::isEasterMonday($giorno)) {

                             $dati[$user->getAnagrafe()][$i] = 'FV';

                        } elseif(Application_Service_Tools::isSaturday($giorno)) {
                            if($patrono) {
                                $dati[$user->getAnagrafe()][$i] = $user->getContratto()->getRidotto() . '³';
                            } else {
                              $dati[$user->getAnagrafe()][$i] = $user->getContratto()->getRidotto();
                            }
                             
                        } else {
                            if($patrono) {
                                $dati[$user->getAnagrafe()][$i] = $user->getContratto()->getPieno() . '³';
                                $patrono = !$patrono;
                            } else {
                              $dati[$user->getAnagrafe()][$i] = $user->getContratto()->getPieno();
                            }
                        }
                        
                    // QUI NON METTO NULL PERCHE NON RISULTA ASSUNTO
                    } else {
                        $dati[$user->getAnagrafe()][$i] = ''; 
                    }
                }
                
            }
        }  
        
        //Column titles
        $header=array();
        for($i = 1; $i<=cal_days_in_month(CAL_GREGORIAN, $month, $year); $i++)  {
            $header[] = $i ; 
        }
        
        //descrizione delle assenze; vengono elencate tutte le tipologie, comprese le colonne nascoste (cosi come risulta nel db).
        $descrAssenze = $TM->getAll(1,1);
        $dettagli = $descrAssenze;
       
        $note = explode('\r', $note);
        
        // print colored table
        $pdf->ColoredTable($header, $dati, $dettagli, $note);
        
        $pdf->Output("giornaliera-$year-$month-" . time(). ".pdf", 'D');
 
        $pdf->Output("report/giornaliera-$year-$month-" . time(). ".pdf", 'F');
       
       
        //$this->_redirect('/calendario/stampa');
        
        
    }
    
    
    
     
    
    
  
    
    
}


            