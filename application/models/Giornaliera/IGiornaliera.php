<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * Interfaccia per generare giornaliere
 * 
 * @author Luca
 */
interface Application_Model_Giornaliera_IGiornaliera {
    
    public function setHeaderTitle($title);
    
    public function setHeaderCalendar($string, array $dates);
        
   
    
    public function download($filename) ;
    
    public function save($path);
    
       
    public function fillReport(array $report);
    public function fillNotes($note);
    public function fillLegenda($legenda);
}
