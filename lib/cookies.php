<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for Cookie Handling in Noteworhty
 */
require_once( LIB_DIR . '/functions.php');

class cookies extends Midori {
    var $cookies;

    function __construct() {
        $this->cookies = $this->_getCookies();
    }

    /**
     * Function Collects the Cookies, GET, and POST information and returns an array
     *      containing all of the values the Application will require.
     */
    function _getCookies() {
        $rVal = array();

        foreach( $_POST as $key=>$val ) {
            $rVal[ $key ] = $this->_CleanRequest($key, $val);
        }

        foreach( $_GET as $key=>$val ) {
            if ( !array_key_exists($key, $rVal) )
                $rVal[ $key ] = $this->_CleanRequest($key, $val);
        }

        foreach( $_COOKIE as $key=>$val ) {
            if ( !array_key_exists($key, $rVal) ) {
                if( $this->_validCookie( $key ) )
                    $rVal[ $key ] = $val;
            }
        }

        $missedData = $this->checkForMissingData();
        foreach( $missedData as $key=>$val ) {
            if ( !array_key_exists($key, $rVal) ) {
                $rVal[ $key ] = $this->_CleanRequest($key, $val);
            }
        }

        // Are we visiting a specific timeline or post?
        $subs = $this->_checkURL( NoNull($rVal['mpage']), NoNull($rVal['ppage']), NoNull($rVal['spage']) );
        if ( count($subs) > 0 ) {
            foreach( $subs as $key=>$val ) {
                $rVal[ $key ] = $val;
            }

            // Clear the pPage value (as it's no longer required)
            unset( $rVal['ppage'] );
        }

        // Populate Missing or Blank Array Values with Defaults
        $defaults = $this->_getCookieDefaults();
        foreach($defaults as $key=>$val) {
            if ( !array_key_exists($key, $rVal) ) {
                $rVal[ $key ] = $val;
            }
        }

        // Validate the Token (if it exists)
        if ( NoNull($rVal['token']) != "" ) {
            $rVal['token'] = $this->cleanToken( $rVal['token'] );
            
            $usrData = $this->_getUserData( $rVal['token'] );
            if ( is_array($usrData) ) {
	            foreach( $usrData as $kk=>$vv ) {
		            $rVal[ $kk ] = $vv;
	            }
            }

	        // Determine if the Admin Screen Should be Displayed
	        if ( $this->_doShowAdmin(NoNull($rVal['mpage']), $rVal['adminCode'], $rVal['token']) ) {
	        	$rVal['DispPg'] = 'admin';
	        }
        }


        // Save the Cookies
        $this->_saveCookies( $rVal );

        // Return the Cookies
        return $rVal;
    }

    private function _getUserData( $Token ) {
	    $rVal = array( 'isLoggedIn' => 'N',
	    			   'adminCode'  => getRandomString(12),
	    			  );

	    // Load the User Class if Appropriate
        if ( NoNull($Token) != '' ) {
	        require_once( LIB_DIR . '/user.php' );
	        $user = new User( $Token );

	        $rVal['isLoggedIn'] = BoolYN( $user->isLoggedIn() );
	        $rVal['adminCode'] = $user->AdminCode();
	        unset( $user );
        }

        // Return the Array
        return $rVal;
    }

    /**
     * Function Checks to See if the Token Value is Valid
     */
    private function isValidToken( $Token = '' ) {
        $rVal = false;
        
        if ( NoNull($Token) != '' ) {
	        require_once( LIB_DIR . '/user.php' );
	        $user = new User( $Token );

	        $rVal = $user->isLoggedIn();
	        unset( $user );
        }
        
        // Return the Boolean Response
        return $rVal;
    }

    /**
     * Function Returns a Token without the Preceeding Pound
     */
    private function cleanToken( $Token ) {
        return NoNull(str_replace( "#", "", $Token ));
    }

    /**
     * Function Reads the Request URI and Returns the Contents in an Array
     */
    private function checkForMissingData() {
        $rVal = array();
        $vals = explode( "&", substr( $_SERVER["REQUEST_URI"], strpos( $_SERVER["REQUEST_URI"], "?" ) + 1 ) );

        foreach ( $vals as $val ) {
            $keyval = explode( "=", $val );
            
            if ( is_array($keyval) ) {
	            $rVal[ $keyval[0] ] = $keyval[1];
            }
        }
        
        // Return an Array Containing the Missing Data
        return $rVal;
    }

    /**
     * Function Returns the Default Cookie Values
     */
    private function _getCookieDefaults() {
        if ( ENABLE_MULTILANG == 1 ) {
            $DispLang = ( substr( $_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2) == '' )
                                        ? DEFAULT_LANG
                                        : substr( $_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2);
        } else {
            $DispLang = DEFAULT_LANG;
        }
        
        $ReqURI = $_SERVER['REQUEST_URI'];
        if ( strpos($_SERVER['REQUEST_URI'], "?") ) {
	        $ReqURI = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], "?"));
        }
        
        // Return the Array of Defaults
        return array('DispPg'       => 'default',
                     'DispLang'     => strtoupper($DispLang),
                     'ReqURI'		=> NoNull($ReqURI),
                     'pftheme'      => '',
                     'TimeZone'     => 23,
                     'isLoggedIn'   => 'N',
                     'isAdmin'		=> 'N',
                     'token'        => getRandomString(16),
                     'GA_Account'   => '',
                     'isDebug'      => 'N',
                     'mpage'		=> '',
                     'spage'		=> '',
                     'ppage'		=> ''
                     );
    }

    private function _CleanRequest( $Key, $Value ) {
        $special = array('RefVisible');
        $rVal = '';

        if( in_array($Key, $special) ) {
            $rVal = implode(',', $Value);
        } else {
            $rVal = urldecode($Value);
        }

        //Return the Cleaned Request Value
        return $rVal;
    }

    /**
     * Function determines whether the Administration Panel should be displayed
     *		based on the mPage value passed and the level of security
     */
    private function _doShowAdmin( $mPage, $AdminCode = "", $token = "" ) {
	    $rVal = false;

	    // If there is no system in place, create one
	    if ( strtolower($mPage) == 'install' ) {
	    	$isDone = readSetting('core', 'installDone');
		    $rVal = YNBool(!$isDone);
	    }

	    // If the UserAccessID Matches the Access ID Passed, Grant Access
	    if ( $AdminCode == $mPage && $mPage != "" ) {
		    $rVal = true;
	    }

	    // Return the Boolean Response
	    return $rVal;
    }

    /**
     * Function Checks the mPage and sPage values and prepares the cookies
     *      accordingly
     */
    private function _checkURL( $mPage, $pPage, $sPage ) {
        $rVal = array();
        $filters = array( 'api',
        				  'rss',
        				  'cron',
                          'post'
                         );

        // Ensure we have both a Page Selection
        // Note: A value is not required as confirmation as these values will only
        //       arrive in pairs. Should a person be missing a value, they will be
        //       returned to their home timelines.
        if ( $mPage != "" ) {
            if ( in_array($mPage, $filters) ) {
                switch ( $mPage ) {
                    case 'api':
                        $rVal = array( 'DispPg' => 'api',
                                       'mpage'  => $pPage,
                                       'spage'  => $sPage
                                      );
                        break;

                    case 'cron':
                        $rVal = array( 'DispPg' => 'cron',
                                       'mpage'  => '',
                                       'spage'  => ''
                                      );
                        break;

                    case 'rss':
                        $rVal = array( 'DispPg' => 'rss',
                                       'mpage'  => $pPage,
                                       'spage'  => $sPage
                                      );
                        break;

                    default:
                        $rVal = array( $mPage => $sPage );
                }
            }
        }

        // Return the Array of Values
        return $rVal;
    }

    /**
     * Function Saves the Cookies to the Browser's Cache (If Cookies Enabled)
     */
    public function _saveCookies( $cookieVals ) {
        foreach( $cookieVals as $key=>$val ) {
            if( $this->_validCookie( $key ) ) {
                setcookie( $key, "$val", $this->_getCookieLifeSpan($key), "/" );
            }
        }
    }

    /**
     * Function returns the Expiration Timestamp for a given Cookie item.
     *  Note: "Immortal" items have a 2 week life span. Everything else
     *        relies on the COOKIE_EXPY value in /conf/config.php
     */
    private function _getCookieLifeSpan( $item ) {
        date_default_timezone_set( 'Asia/Tokyo' );
        $immortal = array('log', 'token');
        $temp = array('invite');
        $rVal = ( $this->logout ) ? time() - COOKIE_EXPY : time() + COOKIE_EXPY;

        if( in_array( $item, $immortal )) {
            $rVal = time() + 3600 * 24 * 14;            // Two Weeks
        }

        if( in_array( $item, $temp )) {
            $rVal = time() + 300;                       // Two Minutes
        }

        //Return the Expiration Time
        return $rVal;
    }

    /**
     * Function returns a boolean signifying whether the Cookie should
     *      be saved to the browser
     */
    private function _validCookie( $item ) {
        $include = array('token', 'dispFormat', 'DispLang', 'invite', 'addr');
        $rVal = false;

        if( in_array( $item, $include )) {
            return true;
        }

        //Return the Exclusion Status of the Item
        return $rVal;
    }

}

?>