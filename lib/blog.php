<?php

/**
 * @author Jason F. Irwin
 * @copyright 2011
 * 
 * Class contains the rules and methods called for User Data
 * 
 * Change Log
 * ----------
 * 2011.04.19 - Created Class (J2fi)
 */
require_once(LIB_DIR . '/functions.php');

class Blog extends Midori {
    var $settings;
    var $messages;

    function __construct( $settings, $messages ) {
        $this->settings = $settings;
        $this->messages = $messages;
        
        //Populate the Class
        
    }

    /*********************************************************************** *
     *  Public Functions
     *********************************************************************** */
    /**
     * Function Returns an array containing the last $Count Posts for the site
     * 
     * Change Log
     * ----------
     * 2012.04.14 - Created Function (J2fi)
     */
    public function getLatestPosts( $Count = 10 ) {
        $rVal = array();
        
        if ( nullInt($Count) > 0 ) {
            
        }
        
        // Return the Array
        return $rVal;
    }


    /***********************************************************************
     *  Private Functions
     ***********************************************************************/
    /**
     * Function builds the base variables that will be used throughout the
     *      application
     * 
     * Change Log
     * ----------
     * 2011.04.28 - Created Function (J2fi)
     */
    private function _populateClass() {
        $rVal = array( 'id'          => '',
                       'displayName' => '',
                       'imageURL'    => '',
                       'created'     => '',
                       'isLoggedIn'  => 'Y' 
                      );

        // Return the Base Array
        return $rVal;
    }


}

?>