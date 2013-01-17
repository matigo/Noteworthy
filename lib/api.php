<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for API Handling in Noteworhty
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
     */
    public function performAction() {
	    $rVal = array('data'   => false,
					  'errors' => 'Invalid Request',
					  'isGood' => 'N',
					  );

	    // Ensure the Basic Requirements are Met, and Perform the Requested Action(s)
		if ( $this->_canProceed() ) {
		    switch ( NoNull($this->settings['PgRoot'], $this->settings['PgSub1']) ) {
		    	case 'account':
		    		$rVal = "";
		    		break;

		    	case 'akismet':
		    		$rVal = array('isGood' => "N" );
		    		switch( NoNull($this->settings['PgSub1']) ) {
			    		case 'validate':
			    			require_once( LIB_DIR . '/akismet.php' );
			    			$siteURL = NoNull($this->settings['txtHomeURL']);
			    			$apiKey = NoNull($this->settings['txtAkismetKey']);

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

		    		switch ( NoNull($this->settings['PgSub1']) ) {
			    		case 'listPosts':
			    			$rVal['posts'] = $content->getCompletePostsList();
			    			$rVal['isGood'] = "Y";
			    			break;
			    		
			    		default:
			    			// Do Nothing
		    		}
		    		break;

		    	case 'dump':
		    		if ( DEBUG_ENABLED > 0 ) {
			    		$rVal = $this->settings;			    		
		    		}
		    		break;

		    	case 'email':
		    		$canEmail = YNBool( readSetting('core', 'EmailOn') );
		    		if ( $canEmail ) {
			    		require_once( LIB_DIR . '/email.php' );
			    		$mail = new Email( $this->settings );
			    		$rVal = $mail->perform();			    		
		    		} else {
			    		$rVal['errors'] = "Email Is Not Enabled";
		    		}
		    		break;

		    	case 'settings':
		    		require_once( LIB_DIR . '/settings.php' );
		    		$conf = new Settings( $this->settings );
		    		$rVal = $conf->update();
		    		break;

		    	case 'users':
		    		require_once( LIB_DIR . '/user.php' );
		    		$usr = new User( $this->settings['token'] );
		    		switch ( NoNull( $this->settings['PgSub1']) ) {
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
		    	
		    	default:
		    		// Do Nothing
		    }
		} else {
			$rVal['Message'] = "Invalid Noteworthy API Key";
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
     */
    private function _canProceed() {
	    $doPage = NoNull($this->settings['PgRoot'], $this->settings['PgSub1']);
	    $rVal = false;

	    if ( BoolYN($this->settings['require_key']) ) {
		    if ( NoNull($this->settings['api_key']) == NoNull($this->settings['accessKey']) ) {
			    $rVal = true;
		    }
		    // I don't like this, but emails should be sent without an API Access Key
		    if ( $doPage == 'email' ) { $rVal = true; }

	    } else {
		    $rVal = true;
	    }

	    // Return a Boolean
	    return $rVal;
    }

}

?>