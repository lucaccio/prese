<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Residui
 *
 * @author Luca
 */
class Application_Form_Residui extends Zend_Form {
     
    
    
    public function __construct($options = null) {
        
        
        $year = new Zend_Form_Element_Select('year-select');
        $options = array();
        $current =  date('Y') ;
        
        for($i =  2011 ; $i <= $current; $i++) {
            $options[$i] = $i;
        }
        $year->addMultiOptions($options)
                ->setLabel('Anno di riferimento:')
                ->setValue($current);
                
        
        #precedente
        $p = new Zend_Form_Element_Text('precedente');
        $p->setLabel('Precedente')
                ->addFilter('StringTrim')
                ->addValidator(new Zend_Validate_Float());
                
                
        #maturate
        $m = new Zend_Form_Element_Text('maturato');
        $m->setLabel('Maturato')
                ->addFilter('StringTrim')
                ->addValidator(new Zend_Validate_Float());
        #goduto
        $g = new Zend_Form_Element_Text('goduto');
        $g->setLabel('Goduto')
                ->addFilter('StringTrim')
                ->addValidator(new Zend_Validate_Float());
        #totale
        $t = new Zend_Form_Element_Text('totale');
        $t->setLabel('Totale')
                ->addFilter('StringTrim')
                ->addValidator(new Zend_Validate_Float());
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Salva');
        
        
        $this->addElement($year);
        $this->addElement($p);
        $this->addElement($m);
        $this->addElement($g);
        $this->addElement($t);
        $this->addElement($submit);
        
        
        parent::__construct($options);
    }
    
    
    
}

 
