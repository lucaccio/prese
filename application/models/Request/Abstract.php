


<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Abstract
 *
 * @author Luca
 */
abstract class Application_Model_Request_Abstract implements Application_Model_Request_Interface {
   
 
    const INVALID        = 'dateInvalid';
    const INVALID_DATE   = 'dateInvalidDate';
    const FALSEFORMAT    = 'dateFalseFormat';
    
    protected $_format =  'yyyy-MM-dd';  
   
    protected $_error = array();
    
    
    /**
     * 
     * @param type $date
     * @return boolean
     */
    protected function _validateDateFormat($date, $options = array())
    {
        $validator = new Zend_Validate_Date($options);
               
        $messageTemplates = array(
            self::INVALID        => "Tipo invalido. String, integer, array or Zend_Date expected",
            self::INVALID_DATE   => "'%value%' non sembra una data valida.",
            self::FALSEFORMAT    => "'%value%' non soddisfa il formato della data fornito'%format%'",
        );
        
        $validator->setMessages($messageTemplates );
               
        if ($validator->isValid($date)) {
            return true;
        } else {
            foreach ($validator->getMessages() as $messageId => $message) {
                echo "Attenzione:  $message\n";
            }
        }
    }
    
    /**
     * 
     * @param type $dayStart
     * @param type $dayStop
     * @return boolean
     */
    protected function _isValidDate($dayStart, $dayStop)
    {
        return  !$dayStart->isLater($dayStop) ; 
    }
    
    protected function _isEmployeeAvailable($uid, $dates) 
    {
        $AM = new Application_Model_AssenzeMapper();
        if( $AM->isAbsent($uid, $dates->getStart('Y-m-d'), $dates->getStop('Y-m-d')) )  {
            return false;
        }
        return true;
    }
    
    protected function _daysAvailable($uid, $dates)
    {
        $UM = new Application_Model_UserMapper();
        $user = $UM->find($uid);
        $days = $dates->countActualDays($user->getSede()->getSedeId());
        if(!$days) {
            return false;
        }
        return true; 
    }
    
    
    /**
     * Controllo la presenza dell'utente
     * 
     * @param type $uid
     */
    public function checkUser($uid)
    {
        $uid = (int)$uid;
        $manager = new Application_Model_UserMapper();
        if(!$manager->getUser($uid)) {
            throw new Exception("Utente con id {$uid} non trovato");
            return false;
        }
        return true;
    }
    
    /**
     * 
     */
    public function checkDates(Zend_Date $start, Zend_Date $stop)
    {
        # controllo del formato della data
        if( !$this->_validateDateFormat( $start->toString($this->_format) )  ||  !$this->_validateDateFormat( $stop->toString($this->_format) ) ) {
            throw new Exception("Errore nel formato delle date richieste");
            return false;
        }
        
        # controllo della coerenza della data
        if(!$this->_isValidDate($start, $stop))   {
            throw new Exception("La data di inizio \"{$start->toString($this->_format)}\" è successiva alla data di fine.");
            return false;
        }
        #return true;
    }
    
    
    
    
    /**
     * 
     * @param type $msg
     */
    protected function _error($msg)
    {
        $this->_error['message'] = __CLASS__ . ": $msg";
    }
    
    
    
}

