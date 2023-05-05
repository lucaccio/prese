<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Richiesta
 *
 * @author Luca
 */
class Application_Model_Email_Richiesta extends Application_Model_Email {
    
    
    protected $_subject_new      = 'Nuova richiesta n.%s del %s inserita da %s.';
     
    protected $_subject_accepted = 'Richiesta n.%s ( %s ) accettata in data %s.';
      
    protected $_subject_refused  = 'Richiesta n.%s  rifiutata in data %s.';

  
    
    
    /**
     * 
     * @param Application_Model_User $t_user
     * @param Application_Model_AssenzaObject $obj
     * @param string $note
     * @param boolean $sandbox
     */ 
    public function __construct($t_user, $obj, $note = null, $sandbox = false) 
    {
        $this->_obj         = $obj;
        $this->_note        = $note;
        
        
        parent::setUserEmail( $t_user->getEmail(), $t_user->getAnagrafe() );
        parent::setSandbox($sandbox);
        parent::__construct();
    }
    
    public function send($transport = null){ Prisma_Logger::log('funzione non richiamabile direttamente'); }
    
    
    # @todo: qui l'obj non è creabile perche non esiste ancora l'assenza
    protected function sendNew()
    {
        
    }
    
     
    
    public function sendAccepted()
    {
        $obj        = $this->_obj;
        $operatore  = $this->_user_name ;
        
        $messaggio = '';
        $messaggio .= self::INFO_GIORNI . "\n\n";
        $messaggio .= "Richiesta accettata per $operatore : \n\n";
        $messaggio .= "Tipo: " . $obj->getDescrizione() . "\n";
        $messaggio .= "Dal:  " . $obj->getDateStart()->toString('d/M/Y'). "\n";
        $messaggio .= "Al:   " . $obj->getDateStop()->toString('d/M/Y') . "\n";
        $messaggio .= "Giorni: " . $obj->getGiorni() . "\n";
             
        $messaggio .= self::INFO_SITO_MSG . "\n\n";
        $messaggio .= self::DO_NOT_REPLY_MSG;
        
        
        parent::setSubject( sprintf($this->_subject_accepted, $obj->getRichiesta()->getId(), $obj->getDescrizione(), $this->_date) );
        parent::setFrom(parent::DEFAULT_FROM, self::DEFAULT_NAME);
        parent::addTo($this->_user_email);
        parent::setBodyText($messaggio);
        parent::send();
    }
    
     
    public function sendRefused()
    {
        $obj        = $this->_obj;
        $operatore  = $this->_user_name ;
        $messaggio  = "Richiesta rifiutata per $operatore : \n\n";
        $messaggio .= "Tipo: " . $obj->getTipologia()->getDescrizione()  . "\n";
        $messaggio .= "Dal:  " . $obj->getDateStart()->toString('d/M/Y'). "\n";
        $messaggio .= "Al:   " . $obj->getDateStop()->toString('d/M/Y') . "\n";
        $messaggio .= "Giorni: " . $obj->getGiorni() . "\n\n";
        $messaggio .= "Note: "   . $obj->getNoteAdmin() . "\n\n";   
        $messaggio .= self::INFO_SITO_MSG . "\n\n";
        $messaggio .= self::DO_NOT_REPLY_MSG;
        
        parent::setSubject( sprintf($this->_subject_refused, $obj->getRichiesta()->getId(), $this->_date) ); 
        parent::setFrom(parent::DEFAULT_FROM, self::DEFAULT_NAME);
        parent::addTo($this->_user_email);
        parent::setBodyText($messaggio);
        parent::send();
    }
    
    # @todo: email queu;
    private function _emailQueue()
    {
        #parent::emailqueue;
    }
    
    
}

 
