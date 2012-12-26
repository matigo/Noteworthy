<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for Data Handling in Noteworhty
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Class (J2fi)
 */
require_once( LIB_DIR . '/functions.php');

class data extends Midori {
    var $settings;

    function __construct() {
        
    }

    /** ********************************************************************** *
     *  Public Functions
     ** ********************************************************************** */

    /**
     * Function determines how to call the requested data (MySQL/NSW) and
     *	returns the appropriate data.
     *
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    public function dataRequest( $method, $variables ) {
	    
    }

    /**
     * Function determines where to store the supplied data (MySQL/NSW) and
     *  returns a Boolean response.
     *
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    public fucntion dataSend( $method, $variables ) {
	    
    }


    /** ********************************************************************** *
     *  Private Functions
     ** ********************************************************************** */
    
    /**
     * Function Returns a SQL String Based on the Method passed.
     *
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    private function _methodToSQL( $method ) {
	    
    }

}

?>