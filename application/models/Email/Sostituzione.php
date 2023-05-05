<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sostituzione
 *
 * @author Luca
 */
class Application_Model_Email_Sostituzione extends Application_Model_Email {
  
    
    protected $_subject_new        = 'Nuova sostituzione assegnata in data %s';
    
    protected $_subject_cancelled  = 'Sostituzione cancellata in data %s';
      
    protected $_subject_promemoria = 'Promemoria sostituzioni';
    
    public function __construct($t_email, $obj, $note = null, $sandbox = false) 
    {
        $this->_obj        = $obj;
        $this->_note       = $note;
        parent::setUserEmail($t_email);
        parent::setSandbox($sandbox);
        parent::__construct();
    }
     
    
    /**
     * Invio email di nuova sostituzione
     */
    public function sendNew()
    {
        $obj = $this->_obj;
        $operatore =   $obj->getSostituto()->getAnagrafe() ;
        $messaggio  = "Dettagli della sostituzione per $operatore : \n\n";
        $messaggio .= "Tipo: " . $obj->getDescrizione() . "\n";
        $messaggio .= "Dal:  " . $obj->getDateStart()->toString('d/M/Y'). "\n";
        $messaggio .= "Al:   " . $obj->getDateStop()->toString('d/M/Y') . "\n";
        $messaggio .= "Giorni: " . $obj->getGiorni() . "\n";
        $messaggio .= "Sede: " . $obj->getLocalita() . "\n\n";
        $messaggio .= "Note: " . ucfirst(strip_tags(stripslashes($this->_note))). "\n\n";
        
        
        $infoBudget = "Info Budget: \n";
        $infoBudget .= "La sostituzione di giorni 1 prevede un budget a disposizione di max Euro 25,00 (Euro 15 per il pasto; Euro 10 per altre spese)\n";
        $infoBudget .= "La sostituzione di giorni 2 o piu', prevede un budget a disposizione di max Euro 40,00 al giorno (Euro 15 per ogni pasto,max 2 al giorno; Euro 10 per altre spese)\n";
        $infoBudget .= "Le spese per benzina ed eventuale pernottamento sono da considerarsi a parte.\n\n";
        
        $messaggio .= utf8_encode($infoBudget);
        
        
        $messaggio .= self::INFO_SITO_MSG . "\n\n";
        $messaggio .= self::DO_NOT_REPLY_MSG;
        
        parent::setFrom(self::DEFAULT_FROM, self::DEFAULT_NAME);
        parent::addTo($this->_user_email);
        parent::setSubject( sprintf($this->_subject_new, $this->_date) );
        parent::setBodyText($messaggio);
        parent::send();
    }
    
    /**
     * Invio email di sostituzione cancellata 
     */
    public function sendCanceled()
    {
        $obj = $this->_obj;
        
        $operatore =   $obj->getSostituto()->getAnagrafe() ;
        $messaggio  = "Una sostituzione precedentemente programmata per $operatore e' stata cancellata: \n\n";
        
        $messaggio .= "Dettagli:\n";
        $messaggio .= "Richiesta n. " . $obj->getRichiesta()->getId() . "\n";
        $messaggio .= "Persona da sostituire: " . $obj->getUser()->getAnagrafe() . "\n";
        $messaggio .= "Tipo: " . $obj->getDescrizione() . "\n";
        $messaggio .= "Dal:  " . $obj->getDateStart()->toString('d/M/Y'). "\n";
        $messaggio .= "Al:   " . $obj->getDateStop()->toString('d/M/Y') . "\n";
        $messaggio .= "Giorni: " . $obj->getGiorni() . "\n";
        
        $localita = trim( $obj->getLocalita() ) ;
        if($localita == '') {
            $localita = " Non risulta nessuna sede per questa sostituzione ";
        }
        $messaggio .= "Sede: " . $localita . "\n\n";
        
        #$messaggio .= "Note: " . ucfirst(strip_tags(stripslashes($this->note))). "\n\n";
        $messaggio .= self::INFO_SITO_MSG . "\n\n";
        $messaggio .= self::DO_NOT_REPLY_MSG;
        
        parent::setFrom(self::DEFAULT_FROM, self::DEFAULT_NAME);
        parent::addTo($this->_user_email);
        parent::setSubject(sprintf($this->_subject_cancelled, $this->_date));
        parent::setBodyText($messaggio);
        parent::send();
    }
    
    /**
     * @ todo: inviare una sola email al sostituto 
     * con l"elenco delle sostituzioni per la settimana successiva
     */
    public function sendPromemoria()
    {
        $obj = $this->_obj;
        $operatore =   $obj->getSostituto()->getAnagrafe() ;
        $messaggio  = "Promemoria sostituzioni per $operatore : \n\n";
        
        foreach($obj as $k => $v) {
            $messaggio .= "-------------------------------\n";
            $messaggio .= "Tipo: " . $obj->getDescrizione() . "\n";
            $messaggio .= "Dal:  " . $obj->getDateStart()->toString('d/M/Y'). "\n";
            $messaggio .= "Al:   " . $obj->getDateStop()->toString('d/M/Y') . "\n";
            $messaggio .= "Giorni: " . $obj->getGiorni() . "\n";
            $messaggio .= "Sede: " . $obj->getLocalita() . "\n\n";
            $messaggio .= "-------------------------------\n\n";
        }
        
        
        $messaggio .= self::INFO_SITO_MSG . "\n\n";
        $messaggio .= self::DO_NOT_REPLY_MSG;
        
        parent::setFrom(self::DEFAULT_FROM, self::DEFAULT_NAME);
        parent::addTo($this->_user_email);
        parent::setSubject($this->_subject_new);
        parent::setBodyText($messaggio);
        parent::send();
    }
    
    
    
}

 