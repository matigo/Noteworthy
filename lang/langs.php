<?php

/**
 * @author Jason F. Irwin
 * @copyright 2011
 * 
 * This file contains the primary pages interface which will serve as the
 *  basis for all the language files employed by the Midori Website
 * 
 * Change Log
 * ----------
 * 2011.04.15 - Created Interface (J2fi)
 * 
 */
interface lang_base {

    /**
     * Function Returns the Developer-Defined Language Name
     */
    public function getLangName();

    /**
     * Function Returns the Appropriate Language Strings in an Array
     */
    public function getStrings();

}

?>