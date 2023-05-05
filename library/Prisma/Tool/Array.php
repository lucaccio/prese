<?php
 
/**
 * Description of Array
 *
 * @author Luca
 */
class Prisma_Tool_Array {
    
    /**
     * 
     * @param type $var
     * @return boolean
     */
    public static function isArray($var)
    {
         if (is_array($var))  
            return true ;
          else return false;
    }
    
    /**
     * 
     * @param type $var
     * @throws Exception
     */
    public static function validateArray($var)
    {
        // se il result isArray è false lo ! trasforma il result in vero
        // quidi lo if esegue l'exception se la condizione al suo interno è vera
        // ! (NOT) = true se NOT true
        if(!self::isArray($var)) {
            throw new Exception("Il valore dev'essere un array");
        }
        return true;
    }
    
}
