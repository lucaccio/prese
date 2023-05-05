<?php

/**
 * Description of UserMapper
 *
 * @author Luca
 */
class Application_Model_UserResiduiMapper extends Prisma_Mapper_Abstract {
    
    
    public function __construct($id = null) {
        $this->_class = 'Application_Model_DbTable_UsersResidui';
    }  
        
    
    /**
     * Carica le righe dei residui per un determinato anno e se non le trova le crea 
     * impostate a zero
     * 
     * @param type $uid
     * @param type $year
     * @param string $tipo
     * @return type
     */
    public function load($uid, $year, $tipo = null) {
        if($year == null) {
            $year = date('Y');
        }
        
        $stdTipo = array(
          'FERIE',
          'PERMESSO',
          'EX-FEST'
        );
        
        $stdValue = array(
            'user_id'    => $uid,
            'year'       => $year,
            'precedente' => 0.00,
            'maturato'   => 0.00,
            'goduto'     => 0.00,
            'totale'     => 0.00,
            'date_created'  => date('Y-m-d H:i:s')
        );
        
        $where[] = 'user_id = ' . $uid;
        $where[] = 'year = '    . $year;
        $rows = $this->getDbTable()->fetchAll($where);
        //$rows = 0;
        if( count($rows) > 0 ) {
            $rowset = $rows->toArray();
            $check = array();
            foreach($rowset as $k => $v) {
                $check[] = $v['tipo'];
            }
            foreach($stdTipo as $k => $tipo) {
                if(!in_array($tipo, $check)) {
                   $stdValue['tipo'] = $tipo;
                   $this->insert($stdValue);
                }
            }
            // return $this->getDbTable()->fetchAll($where);
        } else {
            foreach ($stdTipo as $k => $tipo) {
                $stdValue['tipo'] = $tipo;
                $this->insert($stdValue);
            }
            return $this->getDbTable()->fetchAll($where);
        }
        return $rows;
    }
    
    
    
    public function insert($data) {
        return $this->getDbTable()->insert($data);
    }
    
    public function update($data, $where) {
        return $this->getDbTable()->update($data, $where);
    }
    
    public function delete($where) {
        return $this->getDbTable()->delete($where);
    }
    
    /**
     * Cerco il residuo nella tabella; se la tabella non esiste la creo
     * @param type $id
     * @param type $options
     * @return type
     */
    public function findByUser($id, $options = null) {
        
        $rows = $this->getDbTable()->findByUser($id, $options);
        if($rows->count() == 1) {
            return $rows->current();
        } else {
            $y = date('Y');
            $this->load($id, $y);
            $rows = $this->getDbTable()->findByUser($id, $options);
            return $rows->current(); 
        }
        return false;
    }
    
    /**
     * 
     * @param type $options
     * @return type
     */
    public function fetchAll($options = null) {
        
        $where = null;
        if($options) {
            if(is_array($options)) {
                foreach($options as $k => $v) {
                    $where[] = $k . ' = ' . $v;
                    
                }
            } else {
                $where = $options;
            }
        }
        $rows = $this->getDbTable()->fetchAll($where);
        return $rows;
    }
    
    /**
     * Un find particolare
     * @param type $type
     * @param type $user_id
     * @param type $year
     * @return type
     */
    public function findTypeByUserAndYear($type, $user_id, $year) {
        return $this->getDbTable()->findTypeByUserAndYear($type, $user_id, $year);
    }
    
    /**
     * 
     * @param type $users
     * @param type $tipo
     * @return type
     */
    public function getResidui($u, $tipo = 'FERIE')
    {
            
        if(is_array($u)) {
            $collection = array();
            foreach($u as $k => $user) 
            {
                $collection[ $user->getId() ] = $this->check($user, $tipo);    
            }
            return $collection;
        } elseif(is_object($u)) {
            return $this->check($u, $tipo); 
        }
        throw new Exception('la variabile user deve essere un oggetto o una collezione di oggetti');
    }
    
    /**
     * 
     * @param type $user
     * @param type $tipo
     * @return type
     */
    public function check($user, $tipo )
    {
        // TODO aggiungere gli alti tipi di assenza
        switch($tipo)
        {
            case 'FERIE':
                $residuo =  $this->checkResiduiFerie($user);
                break;
            case 'PERMESSO':
                throw new Exception('da implementare ' .  __METHOD__ );
                break;
            default: 
                $residuo = $this->checkResiduiFerie($user);
                break;
        }
        return $residuo;
    }
    
    /**
     * 
     * @param type $uid
     */
    public function checkResiduiFerie($user)
    {
        $e = new Application_Model_DbTable_Eventi();
        $id = $user->getId();
        $y  = date('Y');
        $y0 = date('Y') - 1 ;
        $y1 = date('Y') + 1 ;

        $f = 0; $f0 = 0; $f1 = 0;

        $f   = $e->getNumAssenze($id, $y, FERIE)->totale ; 
        $f0  = $e->getNumAssenze($id, $y0 , FERIE)->totale ;
        $f1  = $e->getNumAssenze($id, $y1, FERIE)->totale ;

        $opt = array( 'tipo' => 'FERIE' );

        $precedente = $f0;
        $ferie =  $this->findByUser($id, $opt);

        if($ferie) { $precedente = $ferie->precedente; }
        
        $residuo = array(
                        'user'  => $user,
                        'prev'  => $precedente,
                        $y0 => $f0,
                        $y  => $f,
                        $y1 => $f1
                    );
        
        return $residuo;
    }
    
            
    
    
}

 
