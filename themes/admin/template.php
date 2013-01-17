<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for the Manifest theme
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Class (J2fi)
 */
require_once(dirname(__FILE__) . '/conf/settings.php');
require_once(LIB_DIR . '/functions.php');

class theme_main implements themes {
    var $settings;

    function __construct( $data ) {
        $this->settings = $data;
    }

    public function _getData() {
        $html_out = "A critical error has occurred. We're terribly sorry for this and are working on a fix.";

        switch ( strtolower(NoNull($this->settings['PgRoot'])) ) {
            case 'atom':
            case 'rss':
                $html_out = $this->_getRSS();
                break;
            
            default:
                $html_out = $this->_getHTML();
        }

        // Return the HTML Data
        return $html_out;
    }

    public function _getOverride() {
        $rVal = 'Override ...';

        // Return the Override Link
        return $rVal;
    }

    /***********************************************************************
     *                          Internal Functions
     *
     *   The following code should only be called by the above functions
     ***********************************************************************/
    private function _getHTML() {
        $rVal = "";

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

    private function _getMessageStrings() {
        $rVal = getLangDefaults( $this->settings['DispLang'] );
        
        // Load the User-Specified Language Files for this theme
        $LangFile = dirname(__FILE__) . "/lang/" . strtolower($this->settings['DispLang']) . ".php";

        if ( file_exists($LangFile) ){
            require_once( $LangFile );
            $LangClass = 'theme_' . strtolower( $this->settings['DispLang'] );
            $Lang = new $LangClass();

            // Append the List of Strings to the End of the Messages Array
            //      and replace any existing ones that may need the update
            foreach( $Lang->getStrings() as $Key=>$Val ) {
                $rVal[ $Key ] = $Val;
            }

            // Kill the Class
            unset( $Lang );
        }

        // Return the Array of Language Items
        return $rVal;
    }
}

?>
