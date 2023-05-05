<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Object
 *
 * @author Luca
 */
class Application_Model_Object 
{
     
    public function __destruct()
    {
        $this->print_me();
    }
    
    protected function print_me()
    {
        Prisma_Logger::log($this);
    }
    
    
}

 
