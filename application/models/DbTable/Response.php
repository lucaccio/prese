<?php



/**
 * Description of Response
 *
 * @author Luca
 */
class Application_Model_DbTable_Response extends Zend_Db_Table_Abstract {
 
    protected $_name = 'std_response';
    
    protected $_primary = 'id';
        
    public function __construct(){
        $this->_db = Zend_Registry::get('db');
    }
    
}