<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * This file contains the primary interface which will serve as the
 *  basis for all the site files employed by the Noteworthy Websites
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Interface (J2fi)
 * 
 */
interface site_base {

    /**
     * Function Returns the Developer-Defined Language Name
     */
    public function getSiteName();

    /**
     * Function Returns the Appropriate Language Strings in an Array
     */
    public function getSettings();

}

?>