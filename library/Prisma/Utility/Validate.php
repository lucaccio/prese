<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Validate
 *
 * @author Luca
 */
class Prisma_Utility_Validate {
    
    
    /**
     * Check for date format
     *
     * @param string $date Date to validate
     * @return boolean Validity is ok or not
     */
    public static function isDateFormat($date)
    {
        return (bool)preg_match('/^([0-9]{4})-((0?[0-9])|(1[0-2]))-((0?[0-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date);
    }
    
    /**
	 * Check for date validity
	 *
	 * @param string $date Date to validate
	 * @return boolean Validity is ok or not
	 */
	public static function isDate($date)
	{
		if (!preg_match('/^([0-9]{4})-((?:0?[0-9])|(?:1[0-2]))-((?:0?[0-9])|(?:[1-2][0-9])|(?:3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $matches))
			return false;
		return checkdate((int)$matches[2], (int)$matches[3], (int)$matches[1]);
	}
    
        /**
         * 
         * @param type $value
         * @return boolean
         */
        public static function isNull($value)
        {
            if(false == (bool)$value)  { 
                return true ;
            }
            return false;
        }
    
    /**
     * 
     * @param type $date
     * @param type $format
     * @return type
     */
    public static function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    
    
}

 
