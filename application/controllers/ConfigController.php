<?php
/**
 * Description of ConfigController
 *
 * @author Luca
 */
class ConfigController 
        extends Prisma_Controller_Action 

{
    
    // impostazione pseudo variabili globali
    public function globalsAction()
    {
        $table  = new Application_Model_DbTable_Configuration();
        $rowset = $table->fetchAll();     
        $globals = array();
        foreach($rowset as $key => $value)
        {
            $globals[$key] = $value;
        }
        $this->view->globals = $globals;
    }
    
    
    
    
    /**
     * Aggiungo una risposta standard per i rifiuti
     * 
     */
    public function newResponseAction()
    {
        $rm = new Application_Model_ResponseMapper();
        if( $this->_request->isPost() ) {
            $description = trim($this->_request->getParam('description'));
            if('' !== $description) {
                $data = array('description' => $description);
                $rm->insert($data);
            }
        }
        $all = $rm->fetchAll();
        $this->view->all = $all;
    }
    
    /**
     * Cancella una risposta standard
     * 
     */
    public function deleteResponseAction()
    {
        $rm = new Application_Model_ResponseMapper();
        $id = (int)$this->_request->getParam('id');
        if( $this->_request->isDispatched() ) {
            Prisma_Logger::log('dispat');
            if($id > 0) {
                $rm->delete("id = {$id}");
            }
        }
        $this->_forward('new-response');
    }
    
    
    
    
}


