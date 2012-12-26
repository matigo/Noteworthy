<?php

/**
 * @author Jason F. Irwin
 * @copyright 2011
 * 
 * This is the Global Functions File that will be used throughout the
 *  Midori Application, but not by Themes or Plugins
 * 
 * Change Log
 * ----------
 * 2011.04.15 - Adapted File from PhotoStore 2.0 (J2fi)
 */

    /**
     * Function checks a value and returns a numeric value
     *  Note: Non-Numeric Values will return 0
     * 
     * Change Log
     * ----------
     * 2011.04.15 - Created Function (J2fi)
     */
    function nullInt($number, $default = 0) {
        $rVal = $default;
        
        if ( is_numeric($number) ) { $rVal = $number; }

        // Return the Numeric Value
        return $rVal;
    }

    /**
     * Function checks a value and returns a string
     * 
     * Change Log
     * ----------
     * 2011.04.15 - Created Function (J2fi)
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
     * Function Checks the Validity of a supplied URL and returns a cleaned
     *      string
     * 
     * Change Log
     * ----------
     * 2011.04.15 - Created Function (J2fi)
     */
    function cleanURL( $URL ) {
        $rVal = ( preg_match('|^[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $URL) > 0 ) ? $URL : '';

        // Return the Cleaned URL Value
        return $rVal;
    }

    /**
     * Function Returns a Boolean Response based on the Enumerated
     *  Value Passed
     * 
     * Change Log
     * ----------
     * 2011.04.15 - Created Function (J2fi)
     */
    function YNBool( $Val ) {
        return ( $Val == 'Y' ) ? true : false;
    }

    /**
     * Function Returns a YN Value based on the Boolean Passed
     * 
     * Change Log
     * ----------
     * 2011.04.15 - Created Function (J2fi)
     */
    function BoolYN( $Val ) {
        return ( $Val ) ? 'Y' : 'N';
    }

?>