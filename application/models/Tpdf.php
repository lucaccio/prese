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
     
     
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false) {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
        return $this;
    }
    
    
    public function ColoredTable( $data ) {
         
         
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
        $this->SetFont('', 'B');
        
        // Header
        $w = 7;
        $w2 = 50;
        
        
        /*
        $num_headers = count($header);
        $this->Cell($w2,7,'',1,0,'C',1);
        for($i = 0; $i < $num_headers; ++$i) {
            $this->Cell($w, 7, $header[$i], 1, 0, 'C', 1);
        }
        */
        
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        
        // Data
        $fill = 0;
        
        $dataLenght = 31; //count($data);
        $counter = 0;
         
       
        /**
         * $key =  
         * $data = array( uid|date )
         */
        foreach($data as $key => $value) {
            Prisma_Logger::logToFile("array n: " . $key);
            $counter++;
            $border = 'LRBT';
            if($dataLenght == $counter) {
                $border = 'LRBT';
            }
            //print_r($value);
            //foreach ($value as  $v)  {
                if(isset($value['name'])) {
                     $name = $value['name'];
                }else {
                     $name = $value['uid'];
                }
                
                 $date = $value['date'];
                 // print_r($name);
                //  print_r($date);

                $this->Cell($w2, 3, $name, $border, 0, 'L', $fill);
                /*
                for($z = 1; $z <= count($row); $z++) {
                    $this->Cell($w, 3, $row[$z], $border , 0, 'L', $fill);
                }
                */      
                 
                foreach($date as $x => $y) {
                   // echo "$x $y";
                    $this->Cell($w, 3, $y, $border , 0, 'C', $fill);
                }
                
                 
           // }
            
            $this->Ln();
            $fill = !$fill;
        }
        
        
        $this->Ln();
        
        $this->Cell(5 ,  2 , 'LEGENDA:' , 0 , 0, 'L', false); 
        $this->Ln();
         
    }
 
    
    
    
    
}

 