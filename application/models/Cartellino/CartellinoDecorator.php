<?php
/**
 * Created by PhpStorm.
 * User: Fabiola
 * Date: 04/04/2018
 * Time: 16:14
 */

abstract class Application_Model_Cartellino_CartellinoDecorator
                implements Application_Model_Cartellino_Cartellino
{

    /* Application_Model_Cartellino_Cartellino */
    protected $_cartellino;

    protected $_data;

    public function __construct(Application_Model_Cartellino_Cartellino $cartellino) {
        $this->_cartellino = $cartellino;
    }

	
    /**
    *
    */
	public function genera() {
		 return $this->_cartellino->genera();
	}



	public function get() {
	    return $this->_cartellino;
    }


    public function getGiornaliera()
    {
        // TODO: Implement getGiornaliera() method.
        return $this->_data;
    }

    public function show() {
	    return $this->_data;
    }


    protected function insert($key, $value) {
        $this->_data[$key] = $value;
    }





}