<?php
 
/**
 * Description of Text
 *
 * @author luca
 */
class Application_Model_Giornaliera_Text   {
    
    /**
     * Handler del file aperto
     * 
     * @var type 
     */
    protected $_handler;
    
    
    /**
     * 
     * @param type $file
     * @param type $mode
     */
    public function __construct($file, $mode)
    {
        $this->_handler = fopen($file, $mode);
    }
 
    /**
     * 
     * @param array $report
     */
    public function fillReport(array $report)
    {
        $handler = $this->_handler;
        
        foreach($report as $k => $value) 
        {           
            $uid  = $value['uid'];
            $date = $value['date'];

            fwrite($handler, "==================\r\n");
            fwrite($handler, "Utente ID: {$uid}\r\n");
            fwrite($handler, "==================\r\n");
            foreach($date as $d => $h) {
                try {
                    fwrite($handler, "$d : $h  \r\n");
                }   catch (Exception $ex) {
                    Prisma_Logger::logToFile( $ex->getMessage() );
                }
            }
            Prisma_Logger::logToFile("write user $uid successful");
            fwrite($handler, "==================\r\n");
            fwrite($handler, "\r\n");
        }
        fclose($handler);
    }
    
    
    
}
