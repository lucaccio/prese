<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Email
 *
 * @author Luca
 */
abstract class Application_Model_Email extends Zend_Mail {
              
            
    protected $_sandbox  = false;
        
    protected $_user_email;
    
    protected $_user_name;
    
    protected $_tipomail;
    
    protected $_request;
        
    protected $_obj;
    
    protected $_note;
    
    protected $_date;
    
    const DEVELOPER_DEFAULT_FROM = DEVELOPER;
    const DEVELOPER_DEFAULT_TO   = DEVELOPER;
    const DEVELOPER_DEFAULT_NAME = '[ SANDBOX MODE ]';
    
    
    
    const DEFAULT_FROM           = 'feriemanager@gmail.com';
    const DEFAULT_TO             = 'feriemanager@gmail.com';
    protected $_default_from_array     =  array('carlotta.prisma@gmail.com','maura.prisma@gmail.com')  ;
    const DEFAULT_NAME           = '[ Prisma Investimenti Spa - Amministrazione ]';
    
    const SEGRETERIA_MAIL        = 'feriemanager@gmail.com';
    
    const INFO_GIORNI          = "ATTENZIONE! I giorni accettati dall'amministrazione potrebbero non coincidere con quelli richiesti. Verificare nel calendario.";
    const INFO_SITO_MSG          = 'Verificare sempre sul sito http://62.149.161.214/feriemanager/';
    const DO_NOT_REPLY_MSG       = 'NON RISPONDERE A QUESTO INDIRIZZO EMAIL';
    
    
    /**
     * 
     * @global string $g_mail_to_developer
     * @return type
     * @throws Exception
     */
    public function __construct()
    {
        global $g_mail_to_developer;
        $this->_date = new Zend_Date();
        
        # invio email al developer a prescindere se è attiva la voce nel config
        if(ON === $g_mail_to_developer) {
            parent::addBcc(Application_Model_Email::DEVELOPER_DEFAULT_FROM);
        }
    }
    
    /**
     * 
     */
    public function send($transport = null) 
    {
        try {
            if($this->_sandbox) {
                $this->_clear();
            }
            parent::send($transport);
            Prisma_Logger::log('Messaggio inviato');
        } catch(Exception $e) {
            Prisma_Logger::log('Errore email: ' . $e->getMessage());
        }
    }
    
    /**
     * 
     * @param type $email
     * @param type $name
     */
    public function setUserEmail($email, $name = null)
    {
        $this->_user_email = $email;
        if($name) {
            $this->_user_name = $name;
        }
    }
    
    /**
     * 
     * @param type $sandbox
     */
    public function setSandbox($sandbox)
    {
        # @todo: fare una validazione del campo
        if($sandbox)
            $this->_sandbox = $sandbox;
    }
    
    /**
     * 
     * @param type $email
     * @param type $name
     */
    public function setFrom($email, $name = null) 
    {
        if($this->_sandbox)
            $name = Application_Model_Email::DEVELOPER_DEFAULT_NAME;
        parent::setFrom($email, $name);
        
    }
    
    protected function _clear() {
        if( Zend_Registry::isRegistered('sandbox') ) {
            $sandbox = Zend_Registry::get("sandbox");
        }
        $this->_sandbox = $sandbox;
        
        # se in modalità sandbox azzero tutto e invio fakemail al developer
        if(true == $this->_sandbox) {
            parent::clearDefaultFrom();
            parent::clearFrom();
            parent::clearDefaultReplyTo();
            parent::clearRecipients();
            parent::setDefaultFrom(Application_Model_Email::DEFAULT_FROM, Application_Model_Email::DEVELOPER_DEFAULT_NAME);
            parent::addTo(Application_Model_Email::DEVELOPER_DEFAULT_TO);
            Prisma_Logger::log('Setto tutti i parametri per l\'invio email in sandbox mode');
            return;
        } 
    }
    
    
}

 