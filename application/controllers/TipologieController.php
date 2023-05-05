<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TipologieController
 *
 * @author Zack
 */
class TipologieController extends Zend_Controller_Action{
    
    
    public function init() {
       // $this->_helper->viewRenderer->setNoRender();
    }
    
    public function indexAction() {
        $this->_helper->viewRenderer->setNoRender();
        $tipologia = new Application_Model_TipologiaMapper();
                       
        $data = array(
            'sigla' => 'MEI',
            'descrizione' => 'Maternitas'
            
        );
        
        try {
            $tipologia->save($data); 
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        //$this->view->tipologia = $tipologia->fetchAll();
    }
    
    /**
     * inserimento nuova tipologia
     * @2021-04-14
     */
    public function newAction() {
        $tipologia = new Application_Model_TipologiaMapper();            
    }
    
    /**
     * elenco tipologies
     * @2021-04-14
     */
    public function listAction() {
        $tipologia = new Application_Model_TipologiaMapper();  
        $list = $tipologia->fetchAll(null,"descrizione ASC"); 
        $this->view->list = $list;           
    }
    
    
    
    
    
}


