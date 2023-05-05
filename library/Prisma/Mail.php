<?php

/**
 *
 * Description of Mail
 *
 * 
 * 
 * @author Luca
 */
class Prisma_Mail extends Zend_Mail {
     
    
    public function setFrom($email, $name = null) 
    {
        $value = false;
        $sandbox = '';
        if (Zend_Registry::isRegistered('sandbox')) {
            $value = Zend_Registry::get('sandbox');
        }
        if(true == $value) {
            $sandbox = "[SANDBOX MODE]";
        }
        $name = $sandbox . ' ' . $name;
        parent::setFrom($email, $name);
    }
    
    
}

 
