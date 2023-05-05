<?php
/**
 * Description of Excel
 *
 * @author Luca
 */

require_once  APPLICATION_PATH . '/../library/PhpExcel/Classes/PHPExcel.php' ;

class Application_Model_Giornaliera_Excel implements Application_Model_Giornaliera_IGiornaliera
{
    
    const DEFAULT_PATH = 'reports';

    const DEFAULT_CELL_VALUE = "";

    protected $_xls = null;
    
    protected $_index;
    
    public function __construct( )
    {
       $this->_xls = new PHPExcel();

       // $this->setXls($xls);
       $this->setDefaultMetadata();
       return $this;
    }
    
    
    /**
     * 
     * @param PHPExcel $xls
     */
    public function setXls()
    {
        $this->_xls = new self();
    }
        
    /**
     * Restituisce un oggetto PHPExcel
     * 
     * @return PHPExcel $_xls 
     */
    public function getXls()
    {
        if( $this->_xls == null ) {
            $this->setXls();
        }
        return $this->_xls;
    }
    
    /**
     * Imposta alcuni dati di default per excel
     * 
     */
    public function setDefaultMetadata()
    {
        $xls = $this->getXls();
        $xls->getProperties()->setCreator('Prisma Investimenti Spa')
                    ->setTitle("Giornaliera")
                    ->setSubject("Giornaliera")
                    ->setDescription("Giornaliera mensile.")
        ;
    }
    
    /**
     * Stampa il titolo del report
     * 
     */
    public function setHeaderTitle($title)
    {
        $xls = $this->getXls();
        $xls->getActiveSheet()->setCellValue('A1' , $title);
        $styleArray = array(
            'font' => array(
                'bold' => true
            )
        );
        $xls->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
        $xls->getActiveSheet()->getColumnDimension('A') ->setAutoSize(false);
    }
    
    /**
     * Stampa i giorni del mese 
     *  
     * @param string $iso_8601
     * @param array $calendario
     */
    public function setHeaderCalendar($iso_8601, array $calendario)
    {
        $xls = $this->getXls();
        
        $xls->getActiveSheet()->setCellValue('A2' , $iso_8601 );
        $column = 'B'; 
        $row = 2;
        # stampo i giorni del mese
        foreach ($calendario as $day => $iso8610)
        {
            $date = new Zend_Date($iso8610);
            $xls->getActiveSheet()
                    ->setCellValue($column . $row , $date->toString('dd'))
                    ->getStyle($column . $row)
                    ->getFont()->setBold(true)
            ;
            $xls->getActiveSheet()
                    ->getStyle($column . $row)     
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ;
              
            $column++ ;
        }
    }

    /**
     * @param $totals
     */
    public function setHeaderTotals($totals) {
        try {
            $xls = $this->getXls();

            $xls->getActiveSheet()->setCellValue('AG2');
            $column = 'AH';
            $row = 2;
            foreach ($totals as $title  ) {
                $xls->getActiveSheet()
                    ->setCellValue($column . $row, $title)
                    ->getStyle($column . $row)
                    ->getFont()->setBold(true);
                $xls->getActiveSheet()
                    ->getStyle($column . $row)
                    ->getAlignment()
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $xls->getActiveSheet()->getColumnDimension($column) ->setWidth(3) ;
                $column++;
            }
        } catch(Exception $e) {
            Prisma_Logger::logToFile($e->getMessage());
        }

    }



    /**
     * Inserisce le annotazioni
     *
     * @param type $note
     */
    public function fillNotes($note) 
    {
        if(trim($note) == '') {
            return;
        }
        $xls = $this->getXls();
        $column = 'A' ;
        $space  = 2;
        $i = $xls->getActiveSheet()->getHighestRow() + $space;
        $notes = explode('\r', $note);
        $row = $i++;
            $xls->getActiveSheet() 
                ->setCellValue($column.$row , 'ANNOTAZIONI')
                ->getStyle($column . $row)->applyFromArray(array('font' => array('bold'=>true)))
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
            ;
        foreach($notes as $key => $txt) {
            $row = $i++;
            $xls->getActiveSheet()
                ->setCellValue($column.$row , $txt)
                ->getStyle($column . $row)
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
            ;    
        }
        
    }


    /**
     * @param array $report
     * @throws PHPExcel_Exception
     */
    public function fillReport(array $report)
    {


        $xls = $this->getXls();
        $i = 3;
        # stampo le giornaliere di ogni dipendente

        $rows = count($report);
        foreach($report as $key => $datiUtente) {
            //Prisma_Logger::logToFile($value);
            $i++;

            $row = $i;

            $column = 'A';

            $nome_utente = $datiUtente['user']['nome'];

            $xls->getActiveSheet()->setCellValue($column . $row , $nome_utente);
            //
            $xls->getActiveSheet()->getColumnDimension($column) ->setWidth(15) ;
            # coloro le righe in modo alternato
            if (($i % 2) == 0) {
                $xls->getActiveSheet()
                    ->getStyle($column.$row)->getFill()
                    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE8E5E5');
            }

            $cartellinoUtente = $datiUtente['cartellino'];

            $column++ ;
            foreach($cartellinoUtente as $giornoMese => $valoreGiornoMese) {

                if($valoreGiornoMese == null) {
                     $valoreGiornoMese = self::DEFAULT_CELL_VALUE;
                };

                $xls->getActiveSheet()
                    ->setCellValue($column.$row , $valoreGiornoMese)
                    ->getStyle($column . $row)
                    ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ;

                if(!is_numeric($valoreGiornoMese)) {
                    $xls->getActiveSheet()
                        ->getStyle($column . $row)
                        ->applyFromArray(array('font' => array('bold'=>true)))
                    ;
                }

                if (($i % 2) == 0) {
                    $xls->getActiveSheet()
                        ->getStyle($column.$row)->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFE8E5E5');
                }

                $xls->getActiveSheet()->getColumnDimension($column) ->setWidth(4.5) ;
                //setAutoSize(false);
                $column++ ;
            }

            /**
             * Inserimento totali Ferie / Permessi
             */
            $xls->getActiveSheet()->getColumnDimension('AF') ->setWidth(4.5) ;
            $xls->getActiveSheet()->getColumnDimension('AG') ->setWidth(3) ;
            $column = 'AH';

            $gg  = $datiUtente['totale']['giorni_lavorati'] ;
            $ore = $datiUtente['totale']['ore_lavorate'];
            $fe  = $datiUtente['totale']['ferie'];
            $p   = $datiUtente['totale']['permessi'];
            $fv  = $datiUtente['totale']['festivita'];
            $ml  = $datiUtente['totale']['malattia'];

            $totali = array($gg,$ore,$fe, $p, $fv, $ml);

            foreach( $totali as $type => $qta) {
                /*
                if(substr($type, 0,1) == "_") {
                    continue;
                }*/

                //Prisma_Logger::logToFile($type .' => '.$qta);

                $xls->getActiveSheet()
                    ->setCellValue($column.$row , $qta)
                    ->getStyle($column . $row)
                    ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                ;
                if (($i % 2) == 0) {
                    $xls->getActiveSheet()
                        ->getStyle($column.$row)->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFE8E5E5');
                }
                $xls->getActiveSheet()->getColumnDimension($column) ->setWidth(4.5) ;
                $column++ ;
            }
        }
    }





    /**
     * Stampo la legenda, due righe sotto l'ultimo dipendente
     * 
     * @param array $legenda
     */
    public function fillLegenda($legenda) 
    {
        $xls = $this->getXls();
        $column = 'A' ;
        $space  = 2;
        $i = $xls->getActiveSheet()->getHighestRow() + $space;
        $row = $i++;
        $xls->getActiveSheet()
            ->setCellValue($column.$row , "LEGENDA")
            ->getStyle($column . $row)->applyFromArray(array('font' => array('bold'=>true)))
            ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
        ;
                
        foreach($legenda as $leg) 
        {
            $row = $i++;
            $xls->getActiveSheet()
                ->setCellValue($column.$row , $leg)
                ->getStyle($column . $row)
                ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
            ;
        }
    }
          
    
    /**
     * Salvo su disco
     */
    public function save($filename)
    {
        $objWriter = PHPExcel_IOFactory::createWriter($this->getXls(), "Excel2007");
        $objWriter->setOffice2003Compatibility(true);
        $objWriter->save($filename);
    }


    /**
     * Download file
     * @param type $filename
     */
    public function download($filename)
    {
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($this->getXls(), "Excel2007");
        $objWriter->setOffice2003Compatibility(true);
        $objWriter->save('php://output');
    }
    
    /**
     * @deprecated
     * 
     */
    public function d()
    {
        /*
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($filename));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        exit; 
     
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        $writer = new PHPExcel_Writer_Excel7($this->getXls());
        $writer->save('php://output');
         
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=abc.xlsx");
        header("Pragma: no-cache");
        header ("Expires: 0");
        
        $objReader = new PHPExcel_Reader_Excel2007();
        $objPHPExcel = $objReader->load($filename);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");;
        header("Content-Disposition: attachment;filename=$filename");
        header("Content-Transfer-Encoding: binary ");
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel); 
        $objWriter->setOffice2003Compatibility(true);
     
        $objWriter->save('php://output');
        */
    }
}
