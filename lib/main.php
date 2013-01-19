<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for the Noteworthy Application
 */
require_once(THEME_DIR . '/themes.php');
if ( file_exists(CONF_DIR . '/config-db.php') ) {
	require_once(CONF_DIR . '/config-db.php');
}
require_once(CONF_DIR . '/config.php');
require_once(LANG_DIR . '/langs.php');
require_once(LIB_DIR . '/cookies.php');
require_once(LIB_DIR . '/globals.php');
require_once(LIB_DIR . '/functions.php');

class Midori {
    var $Settings;
    var $Content;
    var $Site;

	function __construct() {
		$GLOBALS['Perf']['app_s'] = getMicroTime();
        $sets = new cookies;
        $this->Settings = $sets->cookies;
        unset( $sets );

        if ( $this->_checkFolders() ) {
	        $this->Site = getSiteDetails();
        }
        
        // Add the Site Details to the General Settings
        if ( count($this->Site) > 0 ) {
            foreach( $this->Site as $key=>$val ) {
                $this->Settings[ $key ] = $val;
            }
        }
	}

    /* ********************************************************************* *
     *  Function determines what needs to be done and returns the 
     *      appropriate HTML Document.
     * ********************************************************************* */
    function load_page() {
        $Rsp = array('isGood'   => true,
                     'ErrCode'  => '',
                     'ErrMsg'   => '' 
                     );
        $ThemeLocation = $this->Site['Location'];
        $FormatType = 'html';

        switch ( $this->Settings['DispPg'] ) {
            case 'api':
                // Return Data in JSON Format
                $FormatType = 'json';
                require_once( LIB_DIR . '/api.php' );
                $api = new api( $this->Settings );
                $html_out = $api->performAction();

                // Return the Properly Formatted Result
                return $this->_formatResult($Rsp, $html_out, $FormatType);
                break;

            case 'rss':
            	$FormatType = 'xml';
            	require_once( LIB_DIR . '/content.php' );
            	$rss = new Content( $this->Settings, '', '' );
            	$xml = $rss->getRSS();

            	// Check for a WebCron Task (Results Will NOT Be Part of Return)
            	$this->_checkCronRequirement();

            	// Return the Properly Formatted Result
            	return $xml;
            	break;
            	
            case 'cron':
            	require_once( LIB_DIR . '/cron.php' );
            	$cron = new Cron( $this->Settings );
            	$cron->performFunctions();
            	break;

            case 'admin':
            	$ThemeLocation = 'admin';

            default:
            	$ThemeFile = THEME_DIR . "/$ThemeLocation/template.php";

            	// Check for a WebCron Task (Results Will NOT Be Part of Return)
            	$this->_checkCronRequirement();

                if ( file_exists( $ThemeFile ) ) {
                    require_once( $ThemeFile );
                    $HTML = new theme_main( $this->Settings );
                    $html_out = $HTML->_getData();

                } else {
                    // A Better Error Screen is Needed (What About Something like the Tumblr BRB?)
                    $html_out = "Houston ... we have a problem.";
                }
        }

        // Return the Fully Formatted HTML String
        return $html_out;
    }
    
    private function _checkFolders() {
	    $rVal = true;
	    $i = 0;
	    
	    $checkFolders = NoNull(readSetting('core', 'checkFolders'), 'N');
	    if ( !YNBool($checkFolders) ) {
		    if ( checkDIRExists( CONTENT_DIR ) ) { $i++; }
		    if ( checkDIRExists( TOKEN_DIR ) ) { $i++; }
		    if ( checkDIRExists( USERS_DIR ) ) { $i++; }
		    if ( checkDIRExists( LOG_DIR ) ) { $i++; }
		    if ( checkDIRExists( TMP_DIR ) ) { $i++; }

		    saveSetting( 'core', 'checkFolders', 'Y' );
	    }

		// Return the Boolean
		return $rVal;	    
    }

    private function _checkCronRequirement() {
    	$doCron = false;
    	if ( array_key_exists('doWebCron', $this->Settings) ) {
	    	$doCron = YNBool( $this->Settings['doWebCron'] );	    	
    	}

    	// Do we need to run a web cron job? Do so if necessary in an Asyncronous Call
    	if ( $doCron ) {
    		$CronURL = $this->Settings['HomeURL'] . '/cron/';
        	httpPostAsync( $CronURL, '');
    	}
    }
    
    private function _getRunTime() {
	    $precision = 6;
        $GLOBALS['Perf']['app_f'] = getMicroTime();
        $App = round(( $GLOBALS['Perf']['app_f'] - $GLOBALS['Perf']['app_s'] ), $precision);
        $SQL = nullInt( $GLOBALS['Perf']['queries'] );
        $Api = nullInt( $GLOBALS['Perf']['apiHits'] );

        $lblSecond = ( $App == 1 ) ? "Second" : "Seconds";
        $lblCalls  = ( $Api == 1 ) ? "Call"   : "Calls";
        $lblQuery  = ( $SQL == 1 ) ? "Query"  : "Queries";
        $rVal = "Result generated in roughly: $App $lblSecond, $Api API $lblCalls, $SQL SQL $lblQuery";

        // Reutrn the Run Time String
        return $rVal;
    }

    /**
     *	Function formats the result in the appropriate format and returns the data
     *
     *	Note: Removed the Return Value in the Header (200) to resolve Issue #26
     */
    private function _formatResult( $Response, $Data, $FormatType ) {
        $rVal = '';
        $appType = 'application/octet-stream';
        if ( !is_array( $Data ) ) {
            $Data = array('apiMessage'  => $Data);
        }

        $base = array( 'data' => $Data,
        			   'time' => $this->_getRunTime(),
        			   );

        switch (strtolower( $FormatType )) {
            case 'html':
                $appType = 'application/text; charset=UTF-8';
                exit( $Data );

            case 'json':
                $appType = 'application/json';
                $rVal = json_encode( $base );
                break;

            case 'xml':
                $rVal = arrayToXML( $base );
                break;

            default:
                $rVal = array('result'          => BoolYN($Response['isGood']),
                              'apiMessage'      => '',
                              'errorCode'       => '',
                              'errorMessage'    => '',
                              'data'            => $Data
                             );
        }

        // Return the Data in the Requested Format
        header("Content-Type: " . $appType);
        header("Content-Length: " . strlen($rVal));
        exit( $rVal );
    }

}

?>