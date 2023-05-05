<?php

/*
 * NG Class
 * 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Events
 *
 * @author Luca
 */
interface Application_Model_Events_Interface 
{
    /**
     * NG function
     */
    public function findEventsByUserAndRangeOfDates($user, $day_start, $day_stop);
}
