<?php
/**
 * 
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initLogger()
    {
        $writer = new Zend_Log_Writer_Firebug();
        $logger = new Zend_Log($writer);

        Zend_Registry::set('logger', $logger);
    }
    
   protected function _initRouter() {
        
        if(PHP_SAPI == "cli") {
            $this->bootstrap( 'FrontController' );
            $front = $this->getResource( 'FrontController' );
            $front->setParam('disableOutputBuffering', true);
            //$front->setRouter( new Cli_Router() );
            $front->setRequest( new Zend_Controller_Request_Simple() );
        }
    }    
        
    protected function _initDefault(){
               
        if(PHP_SAPI == "cli") {
            $host    = gethostname();
            $address = gethostbyname($host);
        } else {
            $address = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
        }
        
        $broadcast = '192.168.1';
        $rest      = substr($address, 0, 9) ;
        
        if(strcmp($broadcast, $rest) == 0 || $address == '127.0.0.1') {
            $sandbox           = true;
            $sendmail          = true;
            $sendmailDeveloper = true;
            $sendmailAdmin     = false;
        } else {
            $sandbox = false;
            $sendmail = true;
            $sendmailDeveloper = true;
            $sendmailAdmin = true;
        }
        
        Zend_Registry::set("sandbox", $sandbox);
        Zend_Registry::set("email_send", $sendmail); 
        Zend_Registry::set("sendmail", $sendmail);
        Zend_Registry::set("sendmailDeveloper", $sendmailDeveloper);
        Zend_Registry::set("sendmailAdmin", $sendmailAdmin);
        //$writer = new Zend_Log_Writer_Firebug();
        //$logger = new Zend_Log($writer);
        //Zend_Registry::set("logger", $logger);

        $now = new Zend_Date();
        $stopManutenzione = new Zend_Date('2013-10-14 09:15:00');
        $manutenzione = false;
        if( $now->getTimestamp() < $stopManutenzione->getTimestamp() ) {
            if(!$sandbox)
                $manutenzione = true;
        }
        if($manutenzione)
            Prisma_Logger::log("status::in manutenzione") ;
        Zend_Registry::set("manutenzione", $manutenzione);
         
    }
  
   
    protected function _initMail()
    {
        $tr = new Zend_Mail_Transport_Smtp('smtp.gmail.com', array(
            'auth' => 'login',
            'username' => 'feriemanager@gmail.com',
            'password' => 'prisma2013!',
            'ssl' => 'ssl',
            'port' => 465)
        );
        Zend_Registry::set('google', $tr);
        Zend_Mail::setDefaultTransport($tr); 
    }        
        
    protected function _initDb()
    {
        $this->bootstrap('multidb');
        $resource = $this->getPluginResource('multidb');
        if(Zend_Registry::get("sandbox")) {
            $db = $resource->getDb('db2');
            Zend_Registry::set("db", $db);
        } else {
            $db = $resource->getDb('db1');
            Zend_Registry::set("db", $db);
            
        }
        //$profiler = $db->getProfiler()->setEnabled( false );
        //Zend_Registry::set("profiler", $profiler);
    }
    
    
    protected function _initView()
    {
       Zend_Layout::startMvc();
    }
    
    /**
     * 
     */
    protected function _initNavigation()
    {
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');    
             
        // create container from array
        //27/04/2021
        $navAdmin     = new Zend_Navigation(Zend_Registry::get('navAdmin'));
        $navOperatore = new Zend_Navigation(Zend_Registry::get('navUser'));
        $navSostituto = new Zend_Navigation(Zend_Registry::get('navSost'));
        //Zend_Registry::set('Zend_Navigation', $navigation);
        //$view->navigation($navigation);     
        // $layout->topMenu = $navigation;
        // $layout->loggedInSideMenu = $sideMenuContainer;
        $layout->operatore = $navOperatore;
        $layout->sostituto = $navSostituto;
        $layout->impiegato = $navOperatore;
        $layout->amministratore = $navAdmin;        
    }
    

    protected function _initError () {        
      
      $this->bootstrap( 'FrontController' );
      $front = $this->getResource( 'FrontController' );
      $front->registerPlugin( new Zend_Controller_Plugin_ErrorHandler() );
      $error = $front->getPlugin ('Zend_Controller_Plugin_ErrorHandler');
      //$error->setErrorHandlerController('index');     
        
       // $error->setErrorHandlerController('error');

        if (PHP_SAPI == 'cli') {
             $error->setErrorHandlerController ('error');
             $error->setErrorHandlerAction ('cli');
        }
    }
    
    
    
    
    
    
    
}

 
