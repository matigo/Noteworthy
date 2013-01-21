<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for the Manifest theme
 */
require_once(LIB_DIR . '/functions.php');

class theme_main implements themes {
    var $settings;

    function __construct( $data ) {
        $this->settings = $data;
        
        // Prep the Theme Settings
        prepThemeLocations( $this->settings['HomeURL'], dirname(__FILE__));
    }

    public function _getData() {
        return $this->_getHTML();
    }

    public function _getOverride() {
        return false;
    }

    /***********************************************************************
     *                          Internal Functions
     *
     *   The following code should only be called by the above functions
     ***********************************************************************/
    private function _getHTML() {
        $rVal = "A critical error has occurred. We're terribly sorry for this and are working on a fix.";

        $MainTheme = dirname(__FILE__) . '/desktop.php';
        if ( file_exists($MainTheme) ) {
            require_once( $MainTheme );
            $html = new miTheme( $this->settings );

            $rVal = $html->getHeader() .
                    $html->getContent() .
                    $html->getSuffix();   
        }

        // Return the HTML Formatted String
        return $rVal;
    }
}

?>
