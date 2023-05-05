<?php
/**
 * Classe per gestire i totali del cartellino di ogni utente
 * User: Luca
 * Date: 18/04/2018
 * Time: 16:22
 */

class Totali
{

    private $_uid;

    public function __construct($uid) {
            $this->_uid = $uid;
    }

    public function totaleOre() {
        return "zero";
    }

    public function totaleGiorni() {
        return "zero";
    }



}