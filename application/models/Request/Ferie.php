<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Ferie
 *
 * @author Luca
 */
class Application_Model_Request_Ferie extends Application_Model_Request_Abstract 
{
    
    const MIN_DAY = 1; 
    const MAX_DAY = 26; 
    
    
    
    public function isValid($values) 
    {
        $uid   = (int)$values['uid'];
        $start = new Zend_Date($values['start'],Zend_Date::ISO_8601);
        $stop  = new Zend_Date($values['stop'],Zend_Date::ISO_8601);
        
        
        $this->checkUser($uid);
        
        $this->checkDates($start, $stop);
        
        
        
        # @todo controllare se si ha diritto a ferie
        
        
        $mydate = new Application_Model_MyDate($start, $stop);
        
        # controllo eventuali assenze già imputate nel periodo
        if(!$this->_isEmployeeAvailable($uid, $mydate)) {
            $this->_error("Nel periodo scelto risulta assegnata qualche assenza");
            return false;
        }
        
        if(!$this->_daysAvailable($uid, $mydate)) {
            $this->_error("Nel periodo scelto non ci sono giorni lavorativi.");
            return false;
        }
        
               
        $options = array('tipo' => 'FERIE');
        return true;
        
    }
    
    
    /**
     * 
     * @return type
     */
    public function getMessage()
    {
        return $this->_error['message'];
    }
    
}

 
