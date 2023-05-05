<?php
/**
 * Description of Logger
 *
 * @author Luca
 */

require_once 'Zend/Log/Writer/Firebug.php';

class Prisma_Logger {
     
    
    
    protected static $_instance = null;
    
    protected static $_int = 0;
     
    
    public function __construct() {
        self::clearCounter();
         
    }
    
    public static function getCounter()
    {
        return  self::$_int;
    }
    
    /**
     * 30/12/2019
     * 
     * Php Console.log
     * Utilizzo di console.log con PHP
     * 
     * https://stackoverflow.com/questions/4323411/how-can-i-write-to-the-console-in-php
     * https://bueltge.de/einfaches-php-debugging-in-browser-console/
     */
    public static function console($data) {
        $output = $data;
        if (is_array($output))
            $output = implode(',',  $output) ;
        echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
    }



    /**
     * 
     * @global type $g_enable_logger
     * @param type $msg
     * @param type $priority
     * @param type $checkEnv
     * @return type
     */
    public static function log($msg , $priority = Zend_Log::INFO, $checkEnv = true)
    {
        global $g_enable_logger;
        if(!$g_enable_logger) {
            return;
        }
        $writer = new Zend_Log_Writer_Firebug();
        $logger = new Zend_Log($writer);
        Zend_Registry::set("logger", $logger);
        $logger->log($msg, $priority);
        
    }
    
    /**
     * 
     * @param type $msg
     * @param type $visible
     * @param string $file
     * @return type
     */
    public static function logToFile($msg,  $visible = true, $file = null, $level = 6)
    {

        global $g_enable_logger;
        if(!$g_enable_logger) {
            return;
        }
        $callerClass    = debug_backtrace()[1]['class'];
        $callerFunction =  debug_backtrace()[1]['function'];

        // disabilito il log
        if($visible == false) { return; }
        
        self::clearCounter();
        $path =  realpath(APPLICATION_PATH . '/../log/') ;
    
        if(!file_exists($path)) {            
            mkdir($path  , 0777, true);
        }

        if(!$file) { 
            $date = date("Y-m-d");
            $file = "fm_" . $date . ".log"; 
        }
        $filePath = $path . "/" . $file;
        if(!file_exists($filePath)) {
            touch($filePath, 0777, true);
        }
        $streamWriter = new Zend_Log_Writer_Stream($filePath, 'a+');
        
        $logger       = new Zend_Log();
        $logger->addWriter($streamWriter);
        
        if(is_array($msg)) {
           self::arrayRecursive($msg, $logger);
        } else {
           ++self::$_int ;
           
         //  $logger->log("Logger generato da $callerClass->$callerFunction", $level);
           $logger->log("[$callerClass->$callerFunction] $msg", $level);
        }
        return self::$_int;
    }
    
    /**
     * 
     * @param array $data
     */
    protected static function arrayRecursive($data, $logger)
    {
        foreach($data as $k => $value) {
            if(is_array($value) ){
                self::arrayRecursive($value,$logger);
            } else {
                // if (value == obj) value -> toString 
                ++self::$_int ;
                $logger->log($k .' : ' . $value, Zend_Log::INFO);
            }
        }
    }
    
    
    # non usata 
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public static function getQuery() 
    {
        /* 
        global $g_enable_profiler;
        if(!$g_enable_profiler) {
            Prisma_Logger::log("Abilitare la variabile globale per il profiler.");
            return;
        }
        */    
        $profiler = Zend_Registry::get('profiler');
        if(!$profiler->getEnabled()) {
            Prisma_Logger::log("Abilitare il profiler per visualizzare le query.");
            return;
        }
        $query        = $profiler->getLastQueryProfile();
        $totalTime    = $profiler->getTotalElapsedSecs();
        $queryString  = $query->getQuery();
        $msg = "Query: $queryString :: Time: $totalTime";
        
        
        self::log( $msg );
    }
    
    protected static function clearCounter()
    {
        self::$_int = 0 ;
    }
    
}

 
