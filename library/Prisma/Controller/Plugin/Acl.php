<?php
/**
 * Description of Acl
 *
 * @author Luca
 */
class Prisma_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract 
{

     
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        
        if("cli" == PHP_SAPI) {
            return;
        }
        
        if(Zend_Registry::get("manutenzione") == true) {
                $request->setControllerName('manutenzione');
                $request->setActionName('index');
                $role = 'Guest';
        } else {
            if(Zend_Auth::getInstance()->hasIdentity()) {
            
                $identity =  Zend_Auth::getInstance()->getIdentity() ;
                 
                $level = new Application_Model_LevelMapper();
                $role = $level->findByLevelId($identity->level_id)->descrizione;

                // blocco operatore
                if((int)$identity->level_id === 1 ) {
                    $request->setControllerName('manutenzione');
                    $request->setActionName('index');
                }



                       
            } else {
                if( $request->isXmlHttpRequest() ) {
                    
                }else {
                    $request->setControllerName('auth');
                    $request->setActionName('login');
                    $role = 'Guest';
                }
            }
        }      
        $resource = $request->getControllerName();
        
        
        $acl = new Zend_Acl();
        
        /**
         * CREO I RUOLI
         * 
         */        
        $acl->addRole(new Zend_Acl_Role('Guest'))
            ->addRole(new Zend_Acl_Role('Operatore'))
            ->addRole(new Zend_Acl_Role('Sostituto'))
            ->addRole(new Zend_Acl_Role('Impiegato'))
            ->addRole(new Zend_Acl_Role('Amministratore')) 
            ->addRole(new Zend_Acl_Role('Developer'));
        
        /**
         * CREO LE RISORSE OVVERO I CONTROLLER DISPONIBILI
         * 
         */
        $acl->add(new Zend_Acl_Resource('index'))
            ->add(new Zend_Acl_Resource('presenze'))
            ->add(new Zend_Acl_Resource('calendario'))
            ->add(new Zend_Acl_Resource('auth'))
            ->add(new Zend_Acl_Resource('error'))
            ->add(new Zend_Acl_Resource('richieste'))
            ->add(new Zend_Acl_Resource('assenze'))
            ->add(new Zend_Acl_Resource('sostituzioni'))
            ->add(new Zend_Acl_Resource('tipologie'))
            ->add(new Zend_Acl_Resource('user'))  
            ->add(new Zend_Acl_Resource('primanota'))  
            ->add(new Zend_Acl_Resource('festivita'))  
            ->add(new Zend_Acl_Resource('sedi'))     
            ->add(new Zend_Acl_Resource('ferie'))     
            ->add(new Zend_Acl_Resource('budget'))        
            ->add(new Zend_Acl_Resource('contratti'))      
            ->add(new Zend_Acl_Resource('pdf'))     
            ->add(new Zend_Acl_Resource('strutture'))  
            ->add(new Zend_Acl_Resource('hotel'))     
            ->add(new Zend_Acl_Resource('email'))    
            ->add(new Zend_Acl_Resource('manutenzione'))   
            ->add(new Zend_Acl_Resource('residui'))
            ->add(new Zend_Acl_Resource('ajax'))
            ->add(new Zend_Acl_Resource('config'))     
            ->add(new Zend_Acl_Resource('giornaliera'))   
            ->add(new Zend_Acl_Resource('richiesta'))
            ->add(new Zend_Acl_Resource('giri'))
        ;        
        
        
        
        /**
         * DO PERMESSI AI RUOLI
         * 
         */
        $acl->allow('Guest', array('auth','error','index','manutenzione'))
                
         //   ->allow('Operatore', array('calendario', 'index', 'error', 'auth','ajax'))
         //   ->allow('Operatore', 'richieste', array('index','storico', 'stato', 'nuova', 'cancella', 'annullarichiesta' ,'manutenzione') )
         //   ->allow('Operatore', 'residui', array('view') )
        //    ->allow('Operatore', 'user', array('modifypassword', 'modifyemail') )
            //->allow('Operatore', 'ajax', array('ask-cancellation-request-accepted') ) 
                
          //  ->allow('Impiegato', array('calendario', 'index', 'error', 'auth','ajax'))
           // ->allow('Impiegato', 'richieste', array('index','storico', 'stato', 'nuova', 'cancella','manutenzione') )
          //  ->allow('Impiegato', 'residui', array('view') )
            //->allow('Impiegato', 'user', array('modifypassword', 'modifyemail') )
            //->allow('Impiegato', 'ajax', array('ask-cancellation-request-accepted') )  
                
            ->allow('Sostituto', array('error', 'richieste',  'primanota', 'calendario', 'index','ajax','richiesta'))
            ->allow('Sostituto', 'user',array('modifypassword', 'modifyemail'))
            ->allow('Sostituto', 'sostituzioni', array('elenco'))
            ->allow('Sostituto', 'residui', array('view') )
            ->allow('Sostituto', 'ajax', array('ask-cancellation-request-accepted') )

            ->allow('Amministratore') 
            ->allow('Developer');

        $acl->deny('Operatore');
        $acl->deny('Impiegato');
        $acl->deny('Sostituto');

        Zend_Registry::set('acl', $acl);
 
        // echo $request->getControllerName();
           
        if('auth'!= $request->getControllerName()) {
            if (!$acl->isAllowed($role, $resource, $request->getActionName())) {
                //echo 'non hai i permessi';
                $request->setControllerName('error');
                $request->setActionName('nopermission');
            }
        }
    }
}


