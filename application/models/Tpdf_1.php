<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tpdf
 *
 * @author Luca
 */
class Application_Model_Tpdf extends TCPDF {
     
     
    public function ColoredTable($header, $data, $dettagli, $note) {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        
        // Header
        $w = 7;
        $w2 = 50;
        $num_headers = count($header);
        $this->Cell($w2,7,'',1,0,'C',1);
        for($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($w, 7, $header[$i], 1, 0, 'C', 1);
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        
        // Data
        $fill = 0;
        
        $dataLenght = count($data);
        $counter = 0;
        
        /**
         * $name = nome utente
         * $row = array di giorni
         */
        foreach($data as $name => $row) {
            $counter++;
            $border = 'LRBT';
            if($dataLenght == $counter) {
                $border = 'LRBT';
            }
            
            $this->Cell($w2 ,3,$name,$border,0,'L',$fill);
            for($z = 1; $z <= count($row); $z++) {
                $this->Cell($w, 3, $row[$z], $border , 0, 'L', $fill);
            }
                       
            $this->Ln();
            $fill = !$fill;
        }
        
        
        $this->Ln();
        
        $this->Cell(5 ,  2 , 'LEGENDA:' , 0 , 0, 'L', false); 
        $this->Ln();
        foreach($dettagli as $f) {
           
            $sigla = $f->getSigla();
            if($sigla == 'PM') {
                $stampaSigla = '¹';
            } elseif($sigla == 'PS') {
                $stampaSigla = '²';
            } else {
                $stampaSigla = $sigla;
            }
            
            $text = $stampaSigla . ' = ' . $f->getDescrizioneAdmin();
            $len = strlen($text);
            
            //$this->Cell($len + 12  , 5, $text , 0 , 0, 'L', false);
             //$this->Ln();
            $this->Cell(1  , 5, $text , 0 , 0, 'L', false);
            $this->Ln();
         }
         
         $text = '³ Santo Patrono (lavorativo)';
         $this->Cell($len + 12  , 5, $text , 0 , 0, 'L', false);
         $this->Ln();
         $this->Ln();
         if(count($note) > 0) {
             $this->Cell(5 ,  2 , 'ANNOTAZIONI:' , 0 , 0, 'L', false); 
             $this->Ln();
            foreach($note as $n) {
                $this->Cell(5 ,  2 , trim($n) , 0 , 0, 'L', false); 
                $this->Ln();
            } 
        }
    }
 
    
    
    
    
}

 