<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Application_Model_Giornaliera_Pdf
 *
 * @author Luca
 */
require_once 'tcpdf/config/tcpdf_config.php';
require_once 'tcpdf/tcpdf.php';
//include '_tcpdf_5.0.002/tcpdf.php';




class Application_Model_Giornaliera_Pdf extends TCPDF 
        implements Application_Model_Giornaliera_IGiornaliera {


    /* larghezza cella iniziale */
    const MYPDF_FIRST_WIDTH = 20;
    /* larghezza cellette successive alla prima*/
    const MYPDF_WIDTH = 6.7;
    /* altezza celle */
    const MYPDF_HEIGHT_CELL = 5;
    /* font size*/
    const MYPDF_FONT_SIZE = 8;

    // tcpdf 
    protected $_pdf;
        
    // percorso + nome file pdf 
    protected $_pathfile;




    /**
     * 
     * @param type $options
     * @return \Application_Model_Giornaliera_Pdf
     */
    public function __construct($options = null) 
    {

        if(is_array($options)) {
            $month = $options['month'];
            $year  = $options['year'];
        } else {
            $month = date('m');
            $year  = date('y');
        }
        
        
        try {
            // create new PDF document
           //$pdf =  new Application_Model_Tpdf('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
          
            parent::__construct('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $pdf = $this;
            // set document information
            $pdf->SetCreator(PDF_CREATOR);

            $pdf->SetAuthor('Prisma Investimenti Spa');

            $pdf->SetTitle('Stampa Giornaliera mese di ' );
            
            //$pdf->SetSubject('TCPDF Tutorial');
            //$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
         
            // set default header data
            $pdf->SetHeaderData('', '', 'Prisma Investimenti Spa', 'Giornaliera di ' . $month . ' ' . $year);

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

            $pdf->setFontSubsetting(true);

            $pdf->SetFont('times', '', self::MYPDF_FONT_SIZE);

            // add a page
            $pdf->AddPage(); 

           // $this->_pdf = $pdf;
            return $this;
        } catch (Exception $ex) {
            Prisma_Logger::log($ex->getMessage());
        }
    } 
    
    
    /**
     * 
     * @param type $title
     */
    public function setHeaderTitle($title) {}
    
    
    
    /**
     * 
     * @param type $title
     * @param array $dates
     * @return \Application_Model_Giornaliera_Pdf
     */
    public function setHeaderCalendar($title, array $dates) 
    {
        // colore sfondo date
        $this ->SetFillColor(79, 79, 79);
        
        $this ->SetTextColor(255);
        
        // colore linee del bordo
        $this ->SetDrawColor(128, 0, 0);

        $this ->SetLineWidth(0.3);

        $this ->SetFont('', 'B',10);

     //   $border = 'LRBT';

     //   $fill = 0;

        /* stampo il nome del mese*/
        $this ->Cell(self::MYPDF_FIRST_WIDTH,7,$title,1,0,'C',1);

        /* aggiungo i giorni del mese da stampare */
        $this->setDaysOfMonth($dates);

        // cella vuota
        $this->setEmptyCell();
        
        // titoli dei totali
        $this->setHeaderTotali();

        return $this;        
    }
    
    /* header dei  giorni del mese*/
    public function setDaysOfMonth($dates) {
        foreach($dates as $x => $y) {
            $this ->Cell(self::MYPDF_WIDTH, 7, $x, 1, 0, 'C', 1);
        }
    }

    /* stampo una cella vuota di separazione */
    public function setEmptyCell() {
        $this ->SetFillColor(255, 255, 255);
        $this ->Cell(2, 7, '', null, 0, 'C', 1);
    }

    /**
     * Stampo l'header titoli per inserire i totali delle diverse tipologie
     */
    public function setHeaderTotali() {
        $this ->SetFillColor(79, 79, 79);
        /* stampo le abbreviazioni dei totali */
        $this ->Cell(self::MYPDF_WIDTH+1, 7, 'GG', 1, 0, 'C', 1);
        $this ->Cell(self::MYPDF_WIDTH+2, 7, 'Ore', 1, 0, 'C', 1);
        $this ->Cell(self::MYPDF_WIDTH+1, 7, 'FE', 1, 0, 'C', 1);
        $this ->Cell(self::MYPDF_WIDTH+1, 7, 'PE', 1, 0, 'C', 1);
        $this ->Cell(self::MYPDF_WIDTH+1, 7, 'FV', 1, 0, 'C', 1);
        // $this ->Cell(self::MYPDF_WIDTH+1, 7, 'ML', 1, 0, 'C', 1);
        // $this ->Cell(self::MYPDF_WIDTH+1, 7, 'FG', 1, 0, 'C', 1);
        $this ->Cell(self::MYPDF_WIDTH+1, 7, 'FO', 1, 0, 'C', 1);
    }


    /**
     * Scarica il file
     * 
     * @param type $filename
     */
    public function download($filename)
    {
        $this ->Output($filename, 'D');
    }
    
    /**
     * Salva il file sul disco
     * 
     * @param type $path
     */
    public function save($path)
    {
        $this->_pathfile = $path;
        $this ->Output($path, 'F');
    }
    
    /**
     * Inserisce l'array nel pdf
     * 
     * @param array $report
     */
    public function fillReport(array $report) 
    {
        $pdf = $this->_pdf;
        try {
            $this->ColoredTable( $report );
        } catch (Exception $ex) {
            Prisma_Logger::logToFile( $ex->getMessage() );
        }
    }
    
    /**
     * 
     * @param type $data
     */
    public function ColoredTable( $data ) 
    {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        

        
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(239, 239, 239);
        $this->SetTextColor(0);
        $this->SetFont('');
        
        // Data
        $fill = 0;
        
        $dataLenght = 31; //count($data);

        $counter = 0;

        //$data = array($data[0]);

       //Zend_Debug::dump($data);
        /**
         * $key =  
         * $data =
         */

        foreach($data as $key => $datiUtente)
        {
            Prisma_Logger::logToFile("array n: " . json_encode($datiUtente['user']['nome']), true, 'report_pdf.txt') ;

            $this->SetFont('times', '', self::MYPDF_FONT_SIZE);
            $counter++;
            $border = 'LRBT';
            if($dataLenght == $counter) {
                $border = 'LRBT';
            }
            $name = null;

            if(isset($datiUtente['user']['nome'])) {
                 $name = $datiUtente['user']['nome'];
            }else {
                 $name = $datiUtente['user']['uid'];
            }


            $gg  = $datiUtente['totale']['giorni_lavorati'] ;
            $ore = $datiUtente['totale']['ore_lavorate'];
            $fe  = $datiUtente['totale']['ferie'];
            $p   = $datiUtente['totale']['permessi'];
            $fv  = $datiUtente['totale']['festivita'];
            $ml  = $datiUtente['totale']['malattia'];
            $fis_day  = $datiUtente['totale']['fis'];
            $fis_oraria  = $datiUtente['totale']['fis_oraria'];

            /* CELLA COL NOME DIPENDENTE */
            $this->Cell(self::MYPDF_FIRST_WIDTH, self::MYPDF_HEIGHT_CELL, $name, $border, 0, 'L', $fill);
            $cartellinoUtente = $datiUtente['cartellino'];

            /* inserisco i valori giornalieri per ogni dipendente*/
            foreach($cartellinoUtente as $giornoMese => $valoreGiornoMese) {
                if(!is_numeric($valoreGiornoMese)) {
                    $this->SetFont('times', 'B', self::MYPDF_FONT_SIZE);
                }
                $this->Cell(self::MYPDF_WIDTH, self::MYPDF_HEIGHT_CELL, $valoreGiornoMese, $border , 0, 'C', $fill);
                $this->SetFont('times', '', self::MYPDF_FONT_SIZE);
            }

            // cella vuota
            $this->Cell(2, self::MYPDF_HEIGHT_CELL, '', null, 0, 'C', $fill);

            //@todo inserisco i totali
            $this->SetFont('times', 'B', 8);
            /* totale giorni lavorati*/
                $this->Cell(self::MYPDF_WIDTH+1, self::MYPDF_HEIGHT_CELL, $gg, $border, 0, 'C', $fill);
            /* totale ore lavorate*/
                $this->Cell(self::MYPDF_WIDTH+2, self::MYPDF_HEIGHT_CELL, $ore, $border, 0, 'C', $fill);
            // totale ferie
                $this->Cell(self::MYPDF_WIDTH+1, self::MYPDF_HEIGHT_CELL, $fe, $border, 0, 'C', $fill);
            // totale permesso
                $this->Cell(self::MYPDF_WIDTH+1, self::MYPDF_HEIGHT_CELL, $p , $border, 0, 'C', $fill);
            // totale festivi
                $this->Cell(self::MYPDF_WIDTH+1, self::MYPDF_HEIGHT_CELL, $fv, $border, 0, 'C', $fill);
            /* totale gg malattia*/
          //      $this->Cell(self::MYPDF_WIDTH+1, self::MYPDF_HEIGHT_CELL, $ml, $border, 0, 'C', $fill);

          //      $this->Cell(self::MYPDF_WIDTH+1, self::MYPDF_HEIGHT_CELL, $ml, $border, 0, 'C', $fill);
                $this->Cell(self::MYPDF_WIDTH+1, self::MYPDF_HEIGHT_CELL, $fis_oraria, $border, 0, 'C', $fill);


            $this->Ln();
            $fill = !$fill;
        }
        Prisma_Logger::logToFile("fillReport ok", true, 'report_pdf.txt') ;
    }
    
    /**
     * Inserisce annotazione nel Pdf
     * 
     * @param type $notes
     * @return type
     */
    public function fillNotes($notes) 
    {
        //$this->Ln();
        $this->AddPage();
        if(trim($notes) == '') return;
        $notes = explode('\r', trim($notes));
        if(count($notes) > 0) {
            $this ->SetFont('times', 'B',12);
            $this->Cell(5 ,  2 , 'ANNOTAZIONI' , 0 , 0, 'L', false); 

            $this->Ln();
            $this ->SetFont('times', '', 10 );
            foreach($notes as $note) {
                $this->Cell(5 ,  2 , trim($note) , 0 , 0, 'L', false); 
                $this->Ln();
            } 
        }
        $this->Ln();
        Prisma_Logger::logToFile("fillNotes ok", true, 'report_pdf.txt') ;
    }
    
    /**
     * Inserisce la legenda nel pdf
     * 
     * @param type $data
     */
    public function fillLegenda($data)
    {
        $this ->SetFont('times', 'B',12);
        $this->Cell(5 ,  2 , 'LEGENDA' , 0 , 0, 'L', false);

        $this ->SetFont('times', '',10);
        foreach ($data as $k => $text) {
            $this->Ln();

            $this->Cell(5  , 5, $text , 0 , 0, 'L', false);
        }
        Prisma_Logger::logToFile("fillLegenda ok", true, 'report_pdf.txt') ;
         
    }
    
    
}
