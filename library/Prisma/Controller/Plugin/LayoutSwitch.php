<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LayoutSwitch
 *
 * @author Luca
 */
class Prisma_Controller_Plugin_LayoutSwitch 
                    extends Zend_Controller_Plugin_Abstract 
{
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        parent::preDispatch($request);
        
        $i = Zend_Auth::getInstance()->getIdentity() ;
        if(!$i) { return false; }
        $lid = $i->level_id;
        $map = new Application_Model_LevelMapper();
        $o   = $map->find($lid);
        if(!$o) { return false; }
        if( strtolower($o->getDescrizione()) === "developer") {
            $lay = Zend_Layout::getMvcInstance();
            $lay->setLayout("developer");
        }
    }
    
}
