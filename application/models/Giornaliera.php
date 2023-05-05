<?php
/**
 * Description of Giornaliera
 *
 * @author Luca
 */
class Application_Model_Giornaliera /*extends Application_Model_Object*/
{
    
    const DEFAULT_TITLE = 'Prisma Investimenti - Giornaliera'; 
      
    protected $_legenda = array( 
            'AL = Allattamento',
            'AS = Aspettativa',
            'CP = Congedo di paternità',
            'CI = Cassa Integrazione',
            'CM = Congedo Marimoniale',
            'CO = Congedo Obbligatorio',
            'FE = Ferie',
            'FG = F.I.S Giornaliera',
            'FO = F.I.S Oraria',
            'LU = Permesso per Lutto',
            'ML = Malattia',
            'MA = Maternita',
            'PE = Permesso Elettorale',
            'PG = Permesso Giornaliero',
            'PR = Permesso (Legge 104)',
            //'¹  = Permesso (Mattina)',
            'PT = Patrono (non lavorativo)',
            'RI = Ricovero',
            'SW = Smart Working',
            '¹  = Festivita Lavorativa',
            '²  = Permesso Orario',
            '³  = F.I.S Oraria',
        );
    
    protected $_title = null;
    
    protected $_range_of_dates;
    
    protected $_month;
    
    protected $_year;
    
    protected $_list;
    
    
    /**
     * 
     * @param type $dates
     * @return \Application_Model_Giornaliera
     */
    public function __construct($date)
    {
        $this->_month = $date->toString("MMMM");
        $this->_year  = $date->toString("yyy");
        // Settaggio per l'header
        $lastDay = $date->get(Zend_Date::MONTH_DAYS);
        /* genero array di date: la chiave è il numero del giorno e il valore è nullo*/
        for($i = 1 ; $i<= $lastDay; $i++) {
            $date->setDay($i);
            $this->_range_of_dates[$i] = $date->toString("Y-MM-dd");
        }

        return $this;
    }


    /**
     * Permette l'inserimento di una legenda fornita da un client esterno
     *
     * @param array $data
     * @return  null
     * @throws Exception
     */
    public function setLegenda(array $data )
    {
        if(!Prisma_Tool_Array::isArray($data))
            throw new Exception(__METHOD__ . " necessita di un parametro array");
        $this->_legenda = $data;

    }


    /**
     *
     * @return array
     */
    public function getLegenda()
    {
        return $this->_legenda;
    }

    /**
     * Alias di Add
     *
     * @param array $value
     * @param bool $null_value
     * @return Application_Model_Giornaliera
     */
    public function append(array $value, $null_value = false)
    {
        return $this->add($value, $null_value);
    }

    /**
     * Aggiungo il cartellino di ciascun utente alla collezione
     * 
     * @param array $value
     * @return \Application_Model_Giornaliera
     */
    public function add(array $value, $null_value = false)
    {
        
        if( count($value) > 0) {
            $this->_list[] = $value;
        } else {
            if(true == $null_value) {
                $this->_list[] = $value;
            }
        }
        return $this;
    }


    /**
     * @param $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return null|string
     */
    public function getTitle() 
    {
        if( null === $this->_title ) {
            return self::DEFAULT_TITLE;
        }
        return $this->_title;
    }
         
    
    /**
     * 
     */
    public function toArray(){}
    
    
    /**
     * 
     */
    public function toJson()
    {}

    /**
     *
     * @param $path
     * @param $filename
     * @param $note
     * @return \Application_Model_Giornaliera_Excel
     */
    public function toExcel($path, $filename, $note)
    {
        Prisma_Logger::logToFile("attempting write excel file");
        try {
            # recupero i dati
            $report     = $this->_list;
            //$calendario = $this->getCalendar();
            // $iso_8601   = $this->getYear() . '-' . $this->getMonth() . '-01';

            $excel  = new Application_Model_Giornaliera_Excel() ;
            # titolo
            $excel->setHeaderTitle( $this->getTitle() );
            # datario
            $excel->setHeaderCalendar(ucfirst($this->_month) . ' ' . $this->_year, $this->_range_of_dates);

            //@todo intestatzione totali excel
            $excel->setHeaderTotals(array('GG','ORE','FE','P','FV','ML'));


            # inserisco le giornaliere di ogni dipendente
            $excel->fillReport($report);

            // inserisco eventuali annotazioni
            $excel->fillNotes($note);

            // inserisco la legenda
            $excel->fillLegenda($this->getLegenda());

            // salva su disco
            //$excel->save($path . $filename);

            // download file
            $excel->download( $filename );

            return $excel;
        } catch(Exception $e) {
            Prisma_Logger::logToFile($e->getMessage());

        }

    }
    
    
    /**
     * 
     * 
     * @param type $file
     * @param type $mode
     */
    public function toTxt($file, $mode)
    {
        Prisma_Logger::logToFile("attempting write...$file");
                
        switch ($mode) {
            case 'new':
                $mode = "w";
                break;
            case 'append':
                $mode = "a";
                break;
            default:
                $mode = "w";
                break;
        }
        try {
            $textFile = new Application_Model_Giornaliera_Text($file, $mode);
            $textFile->fillReport( $this->_list );
        } catch (Exception $ex) {
            Prisma_Logger::logToFile( $ex->getMessage() );
        }
    } /* fine writeFile */
    
    
    /**
     * 
     * Crea un file pdf con la giornaliera del mese
     * 
     * @param  type $path
     * @param  type $filename
     * @param  type $note
     * @return type
     */
    public function toPdf($path, $filename, $note) 
    {
        // lista dei cartellini degli utenti
        $report  = $this->_list;
        $options = array(
            'month' => ucfirst( $this->_month ),
            'year'  => $this->_year
        );
        
        try {
            $pdf = new Application_Model_Giornaliera_Pdf($options);
            
            $pdf->setHeaderCalendar(ucfirst($this->_month), $this->_range_of_dates);

            // inserisco la'arrey dei cartellini
            $pdf->fillReport($report);

            // inserisco eventuali annotazioni
            $pdf->fillNotes($note);

            // inserisco la legenda
            $pdf->fillLegenda($this->getLegenda());

            //$pdf->save($path . $filename);
            $pdf->download($filename);

        } catch(Exception $ex) {
            console_log('errore');
            console_log((json_encode($ex)));
            //Prisma_Logger::log($ex->getMessage);
        }
        return;
    }


    public function getList() {
        //Zend_Debug::dump($this->_list);
    }

}

