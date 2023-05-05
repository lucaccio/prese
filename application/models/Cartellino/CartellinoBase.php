<?php
/**
 * Created by PhpStorm.
 * User: Fabiola
 * Date: 04/04/2018
 * Time: 16:12
 */



class  Application_Model_Cartellino_CartellinoBase implements Application_Model_Cartellino_Cartellino
{


    protected $_uid;

    protected $_user;

    protected $_mensilita;




    /* struttura dati del Cartellino */
    protected $_data = array(
        'cartellino' => array(),
        'totale' => array(
            '_gg_senza_domenica' => 0,
            '_ore_totali'     => 0,
            'ore_lavorate'    => 0,
            'giorni_lavorati' => 0,
            'festivita'       => 0,
            'permessi'        => 0,
            'ferie'           => 0,
            'malattia'        => 0,
            'fis'        => 0,
            'fis_oraria'        => 0,
        )
    );

    /**
     * Application_Model_Cartellino_CartellinoBase constructor.
     * @param $user
     * @param $month
     */
    public function __construct($user, $mensilita)
    {
        $this->_mensilita = $mensilita;

        /* settaggio utente */
        if( $user instanceof Application_Model_User) {
            $this->_setUser($user);
        } else {
            $this->_uid = $user;
            $this->_setUserByID($user);
        }

        /* settaggio data */
        $this->_setDate($mensilita);

    }
 

    /**
     * Genera il contenitore base con i giorni del mese
     */
    public function genera() {
        $this->_generaArrayGiorni();
        return $this->_data;
    }

    /**
     * Carica l'utente e popolo l'array con la chiave user
     * @param $uid
     */
    private function _setUserByID($uid) {
        $userMapper = new Application_Model_UserMapper();
        $this->_user = $userMapper->find($uid);
        $this->_insertUser();
    }


    /**
     * @param Application_Model_User $user
     */
    private function _setUser(Application_Model_User $user) {
        $this->_user = $user;
        $this->_insertUser();

    }

    /**
     * @param Application_Model_User $user
     */
    private function _insertUser() {

        //Zend_Debug::dump($this->_user);

        $value = array(
            'id'      => $this->_user->getId(),
            'nome'    => $this->_user->getAnagrafe(array('nome_puntato' => true)),
           // 'sede_id' => $this->_user->getSede()->getSedeId(),
            'configs' => $this->_user->getConfig()
        );
        $value['sede_id'] = $this->_user->getConfig()['sede_lavoro'];
        $this->insert('user', $value);
    }



    /**
     *
     * Inserisco la chiave date nell'array e la popolo
     *
     * @param $mensilita
     */
    private function _setDate($mensilita) {

        //Prisma_Logger::logToFile("MESE: " . $mensilita->toString('Y-MMMM-d'));

        $this->_data['date'] = array(
            'anno' => $this->_mensilita->toString(Zend_Date::YEAR),
            'nome_mese' => $this->_mensilita->toString(Zend_Date::MONTH_NAME),
            'mese' => $this->_mensilita->toString('M'),
            'first_day_iso8610' => $this->_mensilita->toString('yyy-MM-dd'),
            'first' => $this->_mensilita->toString('d'),
            'last_day_iso8610'  => $this->_mensilita->addMonth(1)->subDay(1)->toString('yyy-MM-dd'),
            'last' => $this->_mensilita->toString(Zend_Date::MONTH_DAYS),
        );
    }

    /**
     * Inserisco in array la chiave giornaliera e la popolo con le date del mese
     *
     */
    private function _generaArrayGiorni() {

        $date = new Zend_Date($this->_data['date']['first_day_iso8610']);
        for($i = $this->_data['date']['first']; $i <= $this->_data['date']['last']; $i++) {
            $values[$i] = array(
                'iso8610' => $date->toString('yyy-MM-dd'),
                'giorno' => $date->toString(Zend_Date::WEEKDAY)
            );

            if($date->toString(Zend_Date::WEEKDAY_DIGIT) > 0) {
                $this->_data['totale']['_gg_senza_domenica']++;
            }

            $val[$i] = null;

            //@todo CARTELLINO FINALE
            $this->insert('cartellino', $val);

            $this->insert('giornaliera', $values);
            $date->addDay(1);
        }
    }


    /**
     *
     * @param $key
     * @param $value
     */
    private function insert($key, $value) {
        $this->_data[$key] = $value;
    }


    /**
     * @todo restituire la classe Totali
     * @param $uid
     */
    public function getTotaliUser($uid) {
        return new Application_Model_Cartellino_Totali($uid);
    }








}