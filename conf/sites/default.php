<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the Default Site Settings for Noteworthy
 *
 * Note: If there is only ever one website hosted with this software, it will not be
 *			necessary to create a separate site record. That said, it will be more
 *			future-proof should you decide to make more sites in the future.
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Class (J2fi)
 */

class site implements site_base {
    var $values;

    function __construct( $Custom = array() ) {
        $this->values = $this->_fillSettings( $Custom );
    }

    public function getSiteName() {
        return $this->values['site_name'];
    }
    
    public function getSettings() {
        return $this->values;
    }

    /**
     * Function Fills the Class Values Array With Defaults, Presets, and Custom Values
     *
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    private function _fillSettings( $Custom ) {
    	// Prepare the Default Values
        $rVal = array('URL'             => $_SERVER['SERVER_NAME'],
                      'HomeURL'         => 'http://' . $_SERVER['SERVER_NAME'],

                      'api_url'         => 'http://' . $_SERVER['SERVER_NAME'] . '/api/',
                      'api_port'        => 80,
                      'api_key'         => '6e44bf108720d00a83fcb363a65296569153d500',
                      'require_key'		=> 'Y',

                      'ContentDIR'      => BASE_DIR . '/content/default',
                      'DisqusID'     	=> '',
                      'AkismetKey'		=> '',

                      'SiteID'			=> 0,
                      'SiteName'		=> 'Ambling Down the Path',
                      'SiteDescr'		=> 'A Quick &amp; Dirty Noteworthy-Powered Website',
                      'SiteSEOTags'		=> 'Noteworthy',

                      'ThemeName'       => 'Manifest',
                      'Location'        => 'manifest',
                      'isDefault'       => true,

                      'EN_ENABLED'		=> 'N',
                      'EN_SANDBOX'		=> 'Y',
                      'EN_TOKEN_EXPY'	=> 0,
                      );

        // Add any Custom Labels that are Required
        if ( count($Custom) > 0 ) {
            foreach( $Custom as $key=>$val ) {
                $rVal[ "[$key]" ] = $val;
            }
        }

        // Return the Completed Array
        return $rVal;
    }
}

?>
