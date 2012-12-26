<?php

/**
 * @author Jason F. Irwin
 * @copyright 2010
 * 
 * This file contains the primary pages interface which will serve as the
 * basis for all the language files employed by the Midori Light Websites
 * 
 * Change Log
 * ----------
 * 2011.01.28 - Created File (J2fi)
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