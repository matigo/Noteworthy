<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for "Cron" Handling in Noteworhty
 */
require_once( LIB_DIR . '/functions.php');

class Cron {
    var $settings;

    function __construct( $Settings ) {
        $this->settings = $Settings;

        // Set the Remainder of the Settings Tokens
        $this->settings['TokenName'] = 'cron';
        $this->settings['spage'] = 'performUpdate';
        $this->settings['isActive'] = readSetting($this->settings['TokenName'], 'isActive');
    }

    /** ********************************************************************** *
     *  Public Functions
     ** ********************************************************************** */
    function performFunctions() {
    	$rVal = "Nothing To Be Done";

	    if ( $this->_canDoUpdate() ) {
	    	$rVal = array();
	    	
	    	print_r( "Starting Update!\r\n" );

		    // Update the Twitter Feed (Done Every 5 Minutes)
		    $TwitName = readSetting('core', 'TwitUserName');
		    if ( $TwitName != "" ) {
	    		print_r( "Performing Twitter Update\r\n" );
			    require_once( LIB_DIR . '/twitter.php' );
			    $twt = new Twitter();
			    $rVal['LastTweet'] = $twt->doUpdate();
			    unset( $twt );
			    print_r( "Twitter Update Complete.\r\n" );
		    }

		    // Update the Evernote Posts (If Necessary)
		    if ( $this->_canUpdateService("Evernote") ) {
		    	print_r( "Performing Evernote Update\r\n" );
				require_once( LIB_DIR . '/evernote/main.php' );
				$eNote = new evernote( $this->settings );
				$rVal = $eNote->performAction();
				print_r( "Evernote Update Complete\r\n" );
		    }

		    // Update the Long-Loading Pages
		    print_r( "Updating Flat Files\r\n" );
		    $this->_updateFlatFiles();
		    print_r( "Flat Files Updated.\r\n" );

		    // Mark the Process as Complete!
		    $this->_markCronDone();
	    }
	    
	    // Return the Value
	    print_r( $rVal );
    }

    /** ********************************************************************** *
     *  Private Functions
     ** ********************************************************************** */
    /**
     *	Function Returns a Boolean Response Stating Whether the Cron can be run
     */
    private function _canDoUpdate() {
    	$LastCron = nullInt(readSetting($this->settings['TokenName'], 'LastCron'));
    	$CronTime = nullInt(readSetting($this->settings['TokenName'], 'Interval'), 300);
    	$isActive = YNBool(readSetting($this->settings['TokenName'], 'isActive'));
    	$Elapsed =  time() - intval($LastCron);
	    $rVal = false;

	    // If the Elapsed Time is Greater than the Cron Interval, Return True
	    if ( !$isActive && $Elapsed > intval($CronTime) ) {
	    	saveSetting($this->settings['TokenName'], 'isActive', BoolYN(true) );
	    	$rVal = true;
	    }

	    // Return the Boolean Value
	    return $rVal;
    }
    
    /**
     *	Function Determines if a Service Can be Updated and Returns a Boolean Response
     *	Note: The Service is also Updated to be marked as "Updated"
     *		  The Default CronTime is 1 Earth Hour
     */
    private function _canUpdateService( $Service ) {
    	$LastCron = nullInt(readSetting($this->settings['TokenName'], 'Last_' . $Service));
    	$CronTime = nullInt(readSetting($this->settings['TokenName'], 'iVal_' .$Service), 3600);
    	$Elapsed =  time() - intval($LastCron);
	    $rVal = false;

	    // If the Elapsed Time is Greater than the Cron Interval, Return True
	    if ( $Elapsed > intval($CronTime) ) {
	    	saveSetting($this->settings['TokenName'], 'Last_' . $Service, time() );
	    	$rVal = true;
	    }

	    // Return the Boolean Value
	    return $rVal;
    }

    /**
     *	Function Marks an Active Cron as Complete
     */
    private function _markCronDone() {
	    saveSetting($this->settings['TokenName'], 'LastCron', time() );
	    saveSetting($this->settings['TokenName'], 'isActive', BoolYN(false) );
    }

    /**
     *	Function Loads the Long-Loading Pages Once so they're cached
     */
    private function _updateFlatFiles() {
	    $Pages = array( $this->settings['HomeURL'] . '/',
	    				$this->settings['HomeURL'] . '/archives/',
	    			   );

	    // Cycle Through the Pages
	    foreach( $Pages as $Page ) {
	    	print_r( "Updated: $Page \r\n" );
		    $tmp = file_get_contents($Page);
	    }
    }
}

?>