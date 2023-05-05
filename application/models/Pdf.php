<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pdf
 *
 * @author Luca
 */
class Application_Model_Pdf extends FPDF {
    
   
    public function __construct() {
        parent::FPDF('l');
    }
    
    
    public function BuildTable($title, $header, $data, $footer) {

         

        $this->SetFillColor(255,0,0);

        $this->SetTextColor(255);

        $this->SetDrawColor(128,0,0);

        $this->SetLineWidth(.3);

        $this->SetFont('','B');
        
        $this->SetFontSize('9');
        //Header

         

         
            //$w[$i];
            //
            $w = 7;
            $w2 = 60;
        
            $this->Cell($w2,7,$title,1,0,'C',1);
            $this->Ln();
            
        $this->Cell($w2,7,'',1,0,'C',1);
        for($i=0;$i<count($header);$i++)

        
        //larghezza, altezza, testo, bordo(si,no),posizione, align, sfondo(si,no)
        
        $this->Cell($w,6,$header[$i],1,0,'C',1);
        
        //interruzione di linea
        $this->Ln();

        

        $this->SetFillColor(175);

        $this->SetTextColor(0);

        $this->SetFont('');

 

        $fill = true; // alterna colore righe
        $this->SetFillColor(205);
        foreach($data as $name => $row) {
            
            //print_r($row);
            $this->Cell($w2 ,4,$name,'LR',0,'L',$fill);
            for($ii = 1; $ii <= count($row); $ii++) {
                $this->Cell($w ,4,$row[$ii],'LR',0,'L',$fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
         
         $this->Ln();
         
         //stampo la legenda relativa ai tipi di assenza
         foreach($footer as $f) {
             $this->Cell($w ,4,$f->getSigla() . ' = ' . $f->getDescrizione() , 0 ,0,'L',$fill);
             $this->Ln();
         }
        
        
    }
   
     /**
     *  Viene richiamato in automatico
     */
    public function Footer() {
        // Va a 1.5 cm dal fondo della pagina
        $this->SetY(-15);
        // Seleziona Arial corsivo 8
        $this->SetFont('Arial','I',8);
        // Stampa la ragione sociale centrata
        $this->Cell(0,10,'Prisma Investimenti Spa' ,0,0,'C');
    }
   
   
   
   
   
   
   
}//fine classe
 
