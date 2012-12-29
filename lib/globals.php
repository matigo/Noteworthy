<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * This is the Global Functions File that will be used throughout the Application
 */

    /**
     * Function checks a value and returns a numeric value
     *  Note: Non-Numeric Values will return 0
     */
    function nullInt($number, $default = 0) {
        $rVal = $default;
        
        if ( is_numeric($number) ) { $rVal = $number; }

        // Return the Numeric Value
        return $rVal;
    }

    /**
     * Function checks a value and returns a string
     */
    function NoNull( $string, $Default = "" ) {
        $rVal = $Default;

        if ( isset( $string ) ) {
            $rVal = ltrim(rtrim( $string ));
        }

        // Return the String Value
        return $rVal;
    }



    /**
     * Function Checks the Validity of a supplied URL and returns a cleaned string
     */
    function cleanURL( $URL ) {
        $rVal = ( preg_match('|^[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $URL) > 0 ) ? $URL : '';

        // Return the Cleaned URL Value
        return $rVal;
    }

    /**
     * Function Returns a Boolean Response based on the Enumerated
     *  Value Passed
     */
    function YNBool( $Val ) {
        return ( $Val == 'Y' ) ? true : false;
    }

    /**
     * Function Returns a YN Value based on the Boolean Passed
     */
    function BoolYN( $Val ) {
        return ( $Val ) ? 'Y' : 'N';
    }

?>