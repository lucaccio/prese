<?php
/**
 * Description of CliController
 *
 * php public/index.php action
 * 
 * 
 * @author Luca
 */
class CliController extends Zend_Controller_Action{
   
    
    public function init(){
        $this->_helper->_layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
    
    
    public function promemoriaAction() {
        
        $UM = new Application_Model_UserMapper();
        $AM = new Application_Model_AssenzeMapper();
        
        $d = '2013-12-29';
        
        $today = new Zend_Date($d);
        $today->addWeek(1);
        echo $week = $today->toString(Zend_Date::WEEK);
     }
    
    
    /**
     * 
     */
    public function creaAction() {
        
        $UM  = new Application_Model_UserMapper();
        $utentiAttivi = $UM->elencoUtenti(true);
        $types = array('FERIE', 'PERMESSO','EX-FEST');
        //echo "ATTENZIONE CONTROLLARE L'ANNO\n\r";
        
        $now = new Zend_Date();
        $day_of_year = $now->get(Zend_Date::DAY_OF_YEAR);
        if($day_of_year != 0) {
            echo "attenzione, non è capodanno...exit\n\r";
            return;
        }
        $year = date('Y');
        foreach ($utentiAttivi as $k => $user) {
            foreach($types as $k => $type) {
                 $residuo =  new Application_Model_Residuo();
                 $residuo->creaResiduoAnnuale($user, $year, $type);
                 
            }
        }
    }
    
    /**
     * Crea i residui per l'anno nuovo (da far partire a capodanno)
     * 
     */
    public function residuiAction()
    {
         
    }
    
    
    
}

 
