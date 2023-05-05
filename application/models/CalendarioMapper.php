<?php
/**
 * Description of CalendarioMApper
 *
 * @author Luca
 */
class Application_Model_CalendarioMapper extends Prisma_Mapper_Abstract{

    public function init() {}
       
    public function __construct() {
        $this->_eventiMapper = new Application_Model_EventiMapper();
    }
       
    public function findAllByDate($users) {
        return $this->_eventiMapper->findAllByDate($users);
    }
    
    
    
    
    
    
    
}


