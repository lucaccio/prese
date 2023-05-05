<?php


/*
 * Description of Tipologia
 *
 * @author Luca
 */
class Application_Model_Tipologia 
{
    
    
    /**
     *
     * @var type 
     */
    protected $_tipologia_id;
    
    /**
     *
     * @var type 
     */
    protected $_sigla;
    
    /**
     *
     * @var type 
     */
    protected $_class_name;
    
    
    /**
     *
     * @var type 
     */
    protected $_descrizione;
    
    /**
     *
     * @var type 
     */
    protected $_descrizione_admin;
    
    /**
     *
     * @var type 
     */
    protected $_ferie;
    
    /**
     *
     * @var type 
     */
    protected $_maternita;
    
    /**
     *
     * @var type 
     */
    protected $_allattamento;
    
    
    /**
     *
     * @var type 
     */
    protected $_malattia;
    
    /**
     *
     * @var type 
     */
    protected $_infortunio;
        
    /**
     *
     * @var type 
     */
    protected $_permesso;
    
    /**
     *
     * @var type 
     */
    protected $_patrono;
    
    /**
     *
     * @var type 
     */
    protected $_l_104;
    
    /**
     *
     * @var type 
     */
    protected $_aspettativa;
    
    
    /**
     *
     * @var type 
     */
    protected $_fulltime;
    
    /**
     *
     * @var type 
     */
    protected $_hidden;
    
    


    /**
     *
     * Mostro la voce in caso di inserimento multiplo
     * @var type 
     */
    protected $_multi_insert = false;

    /**
     * 
     */
    protected $_is_assenza_oraria = 0;




    /**
     * 
     * @param Zend_Db_Table_Row_Abstract $data
     * @return \Application_Model_Tipologia
     */
    public function __construct($data = null) {
        
        if(!$data instanceof Zend_Db_Table_Row_Abstract) {
            return;
        }
        
        /* 2014-04-24 */
        $this->setClassName($data->class_name);
        
        $this->setId($data->tipologia_id);
        $this->setSigla($data->sigla);
        $this->setDescrizione($data->descrizione);
        $this->setDescrizioneAdmin($data->descrizione_admin);
        $this->setFerie($data->ferie);
        $this->setMaternita($data->maternita);
        $this->setMalattia($data->malattia);
        $this->setInfortunio($data->infortunio);                    
        $this->setPermesso($data->permesso);
        $this->setPatrono($data->patrono);
        $this->setLegge104($data->l_104);
        $this->setAspettativa($data->aspettativa);
        $this->setFulltime($data->fulltime);
        $this->setHidden($data->hidden);
        $this->setAllattamento($data->allattamento);

        //13/04/2021
        $this->setMultiInsert($data->use_on_multi_insert) ;
        $this->setAssenzaOraria($data->assenza_oraria) ;

        return $this;
    }
    
    /**
     * 
     * @param type $name
     * @return type
     */    
    public function __get($name) {
        $method = 'get' . $name;
        return $this->$method();
    }
      
    public function setId($id)
    {
        $this->_tipologia_id = (int)$id;
        return $this;
    }
    
    public function setDescrizione($descrizione)
    {
        $this->_descrizione = (string) $descrizione;
        return $this;
    }
    
    public function setDescrizioneAdmin($descrizione)
    {
        $this->_descrizione_admin = (string) $descrizione;
        return $this;
    }
    
    public function setSigla($sigla)
    {
        $this->_sigla = (string) $sigla;
        return $this;
    }
    
    public function setFerie($data) {
        $this->_ferie = $data;
        return $this;
    }
    
    public function setMaternita($data) {
        $this->_maternita = $data;
        return $this;
    }
    
    public function setAllattamento($data) {
        $this->_allattamento = $data;
        return $this;
    }
    
    
    public function setMalattia($data) {
        $this->_malattia = $data;
        return $this;
    }
    
    public function setInfortunio($data) {
        $this->_infortunio = $data;
        return $this;
    }
    
    
    public function setPermesso($data) {
        $this->_permesso = $data;
        return $this;
    }
    
    public function setPatrono($data) {
        $this->_patrono = $data;
        return $this;
    }
    
    public function setLegge104($data) {
        $this->_l_104 = $data;
        return $this;
    }
    
    public function setFulltime($data) {
        $this->_fulltime = $data;
        return $this;
    }
       
    public function setAspettativa($data)
    {
        $this->_aspettativa = $data;
        return $this;
    }
    
    public function setHidden($data)
    {
        $this->_hidden = $data;
        return $this;
    }
    
    public function setClassName($class)
    {
        $this->_class_name = $class;
    }
    
    //vedi TipologiaMapper riga 91
    public function setMultiInsert($data)
    {
        $this->_multi_insert = $data;
        return $this;
    }
    
    /**
     * valuta se questo tipo di assenza è 
     * usabile a ore
     * 
     * @date 12/04/2021
     */
    public function isAssenzaOraria() {
        return $this->_is_assenza_oraria ; 
       
    }

    public function setAssenzaOraria($value) {
        //Prisma_Logger::logToFile("assenza oraria: ". $value);
     
        $this->_is_assenza_oraria  =  $value ;
        return $this;
    }

    
    /**
     * 
     * @return boolean
     */
    public function permesso() {
        if($this->getSigla() == 'PM' or $this->getSigla() == 'PS') {
            return true;
        }
        return false;
    }
    
    # 2013-06-03
    # new
    //public function 
    
    
    
    /**
     * 
     * @return boolean
     */
    public function ferie() {
        if($this->getSigla() == 'FE') {
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @return boolean
     */
    public function exfest() {
        if($this->getSigla() == 'EX') {
            return true;
        }
        return false;
    }
    
    /**
     * 
     */
    public function isFerie() {
        if($this->_ferie == 1) {
           return true;
        }
        return false;    
    }
    
    /**
     * 
     */
    public function isMaternita() {
        if($this->_maternita == 1) {
           return true;
        }
        return false;    
    }
    
    /**
     * 
     */
    public function isMalattia() {
        if($this->_malattia == 1) {
           return true;
        }
        return false;    
    }
    
    public function isAllattamento() {
        if($this->_allattamento == 1) {
           return true;
        }
        return false;    
    }
    
    
    
    /**
     * 
     */
    public function isInfortunio() {
        if($this->_infortunio == 1) {
           return true;
        }
        return false;    
    }
    
    
    /**
     * 
     */
    public function isPermesso() {
        if($this->_permesso == 1) {
           return true;
        }
        return false;
    }
    
    /**
     * 
     */
    public function isPatrono() {
        if($this->_patrono == 1) {
           return true;
        }
        return false;
    }
    
    /**
     * Permesso retribuito Legge 104
     * @return boolean
     */
    public function isLegge104() {
       if($this->_l_104 == 1) {
           return true;
        }
        return false; 
    }
    
    
    public function isAspettativa() 
    {
        if($this->_aspettativa == 1) {
           return true;
        }
        return false; 
    }
    
    public function isHidden()
    {
        if($this->_hidden == 1) {
           return true;
        }
        return false; 
    }
            
    
    /**
     * 
     * @return type
     */
    public function getId() {
        return $this->_tipologia_id;
    }
    
    /**
     * 
     * @return type
     */
    public function getSigla() {
        return $this->_sigla;
    }
    
    /**
     * 
     * @return type
     */
    public function getDescrizione() {
        return trim($this->_descrizione);
    }
    
    public function getDescrizioneAdmin() {
        return trim($this->_descrizione_admin);
    }
    
    /**
     * 
     * @return type
     */
    public function getFulltime() {
        return $this->_fulltime;
    }
    
    
    public function getTipo() {
        if($this->_ferie == 1) {
            return 'ferie';
        } elseif($this->_permesso == 1) {
            return 'permesso';
        }
    }
    
    /**
     * 
     */
    public function isMultiInsert() {
        return boolval($this->_multi_insert);
    }
    
}
 
