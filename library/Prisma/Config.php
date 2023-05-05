<?php
/**
 * Description of Application 
 *
 * @author Luca
 */
class Prisma_Config {

    /**
     *
     */
     //static protected $_config_path = APPLICATION_PATH . '/configs/' ;

    /**
     *
     */
    static protected $_default_ini = 'application.ini';

    /**
     * 
     * @return string
     */
    public static function getConfig() 
    {
        $host = self::setHostname();
        $ip   = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
        $ini_file = self::ipToConfig($ip);
        $default_ini = APPLICATION_PATH .DS.'configs'.DS.'application.ini';
        $custom_ini  = APPLICATION_PATH .DS.'configs'.DS.$ini_file ;

        $file = file_exists($custom_ini) ? $custom_ini : $default_ini;
        $file = basename($file);
        define('INI_FILE', $file);
        return $file;


        /*
        //$host = $_SERVER['SERVER_NAME'];
        $host = gethostname();
        $host = (substr($host, 0, 3) == 'www') ? substr($host, 4) : $host;
        $host = self::_addressToHostName($host);
        $defaultConfig = APPLICATION_PATH .DS.'configs'.DS.'application.ini';
        $hostConfig    = APPLICATION_PATH .DS.'configs'.DS.$host.'.ini';
        $file = file_exists($hostConfig) ? $hostConfig : $defaultConfig;
        $file = basename($file);
        return $file;
        */

    }

    /**
     * @return string
     */
    protected static function setHostname() {
        $host = gethostname();
        define('HOSTNAME', $host);
        return $host;
    }

    /**
     * @param $ip
     * @return string
     */
    protected static function ipToConfig($ip) {
        if( ($ip == '127.0.0.1') ) {
            defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');
            return 'locale.ini';
        }
        if( ($ip == '192.168.1.30') ) {
            defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');
            return 'locale.ini';
        }
        if($ip == '62.149.161.214') {
            defined('APPLICATION_ENV')
            || define('APPLICATION_ENV', 'development');
            return 'config_aruba';
        }
        return self::$_default_ini  ;
     }

    /**
     * stampa info sul nome host e nome ambiente di sistema
     * @param type $host
     */
    public static function printServer($host) {
        Prisma_Logger::log("Host::" . $host . " Enviroment::" . APPLICATION_ENV);  
    }
    
    
    
}

 
