<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for API Handling in Noteworhty
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Class (J2fi)
 */
require_once( LIB_DIR . '/functions.php');

class api extends Midori {
    var $settings;
    var $messages;

    function __construct( $settings ) {
        $this->settings = $settings;
        $this->messages = getLangDefaults( $this->settings['DispLang'] );
    }

    /** ********************************************************************** *
     *  Public Functions
     ** ********************************************************************** */

    /**
     * Function performs the requested Method Activity and Returns the Results
     *		in an array.
     *
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    public function performAction() {
	    $rVal = "Invalid Request";

	    // Ensure the Basic Requirements are Met, and Perform the Requested Action(s)
		if ( $this->_canProceed() ) {
		    switch ( NoNull($this->settings['mpage'], $this->settings['spage']) ) {
		    	case 'account':
		    		$rVal = "";
		    		break;

		    	case 'akismet':
		    		$rVal = array('isGood' => "N" );
		    		switch( NoNull($this->settings['spage']) ) {
			    		case 'validate':
			    			require_once( LIB_DIR . '/akismet.php' );
			    			$siteURL = NoNull($this->settings['siteurl']);
			    			$apiKey = NoNull($this->settings['akismet-id']);

			    			writeNote( "Checking Akismet Key - URL: $siteURL | Akismet Key: $apiKey" );
			    			$akismet = new Akismet($siteURL, $apiKey);
			    			$rVal = array( "isGood" => BoolYN($akismet->isKeyValid()) );
							break;
		    		}
		    		break;
		    	
		    	case 'content':
		    		require_once( LIB_DIR . '/content.php' );
			    	$content = new Content( $this->settings, $this->messages, dirname(__FILE__) );
		    		$rVal = array('isGood' => "N" );

		    		switch ( NoNull($this->settings['spage']) ) {
			    		case 'listPosts':
			    			$rVal['posts'] = $content->getCompletePostsList();
			    			$rVal['isGood'] = "Y";
			    			break;
			    		
			    		default:
			    			// Do Nothing
		    		}
		    		break;

		    	case 'dump':
		    		$rVal = $this->settings;
		    		break;
		    		
		    	case 'settings':
		    		require_once( LIB_DIR . '/settings.php' );
		    		$conf = new Settings( $this->settings );
		    		$rVal = $conf->update();
		    		break;
		    	
		    	case 'users':
		    		require_once( LIB_DIR . '/user.php' );
		    		$usr = new User( $this->settings['token'] );
		    		switch ( NoNull( $this->settings['spage']) ) {
			    		case 'login':
			    			$rVal = $usr->authAccount( $this->settings['email'], $this->settings['route'], $this->settings['token'] );
			    			break;
			    		
			    		default:
			    			// Do Nothing
		    		}
		    		break;

		    	case 'evernote':
		    		require_once( LIB_DIR . '/evernote/main.php' );
		    		$eNote = new evernote( $this->settings );
		    		$rVal = $eNote->performAction();
		    		break;
		    }
		}

	    // Return the Array
	    return $rVal;
    }

    /** ********************************************************************** *
     *  Private Functions
     ** ********************************************************************** */
    /**
     * Function Checks to Ensure the Request Can Proceed
     *	Definition: Is API Key required? Yes? Do we have it? Yes? Matches?
     *
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    private function _canProceed() {
	    $rVal = false;
	    
	    if ( boolYN($this->settings['require_key']) ) {
		    if ( NoNull($this->settings['api_key']) == NoNull($this->settings['accessKey']) ) {
			    $rVal = true;
		    }
	    } else {
		    $rVal = true;
	    }

	    // Return a Boolean
	    return $rVal;
    }

    /**
     * Function Returns a SQL String Based on the Method passed.
     *
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
     private function _evernoteValidate() {
	     
     }

}

?>