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
        $this->settings['PgSub1'] = 'performUpdate';
        $this->settings['isActive'] = readSetting($this->settings['TokenName'], 'isActive');
    }

    /** ********************************************************************** *
     *  Public Functions
     ** ********************************************************************** */
    function performFunctions() {
    	writeNote( "Cron::performFunctions()" );
    	$rVal = "Nothing To Be Done";

	    if ( $this->_canDoUpdate() ) {
	    	$rVal = array();

		    // Update the Twitter Feed (Done Every 5 Minutes)
		    $TwitName = $this->settings['TwitName'];
		    if ( $TwitName != "" ) {
			    writeNote( "Collecting Tweets for: $TwitName" );
			    require_once( LIB_DIR . '/twitter.php' );
			    $twt = new Twitter();
			    $rVal['LastTweet'] = $twt->doUpdate();
			    unset( $twt );
		    }

		    // Update the Evernote Posts (If Necessary)
		    if ( $this->_canUpdateService("Evernote") ) {
		    	writeNote( "Perform Evernote Update" );
				require_once( LIB_DIR . '/evernote/main.php' );
				$eNote = new evernote( $this->settings );
				$rVal['EverNote'] = $eNote->performAction();
		    }

		    // Update the Long-Loading Pages
		    $this->_updateFlatFiles();

		    // Mark the Process as Complete!
		    $this->_markCronDone();
	    }

	    // Record the Results if Necessary
	    if ( is_array($rVal) ) {
		    foreach( $rVal as $Key=>$Val ) {
			    writeNote( "Cron Result: [$Key] => $Val" );
		    }
	    } else {
		    writeNote( "Cron Result: $rVal" );
	    }
	    // Return the Value
	    return $rVal;
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

	    // If the Cron is Still Marked as Active after 4 CronTime Occurrences, Reset the Thing
	    // This is a very kludgy way to handle a cron task that failed half-way through without
	    // reporting the error
	    if ( $isActive && $Elapsed > ($CronTime * 4) ) {
		    $isActive = false;
	    }

	    // If the Elapsed Time is Greater than the Cron Interval, Return True
	    if ( !$isActive && $Elapsed > intval($CronTime) ) {
	    	saveSetting($this->settings['TokenName'], 'isActive', 'Y' );
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
    	$CronTime = nullInt(readSetting($this->settings['TokenName'], 'iVal_' .$Service), 1800);
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
	    saveSetting($this->settings['TokenName'], 'isActive', 'N' );
    }

    /**
     *	Function Loads the Long-Loading Pages Once so they're cached
     */
    private function _updateFlatFiles() {
	    $Pages = array( $this->settings['HomeURL'] . '/',
	    				$this->settings['HomeURL'] . '/archives/',
	    				$this->settings['HomeURL'] . '/rss/',
	    			   );

	    // Cycle Through the Pages
	    foreach( $Pages as $Page ) {
		    $tmp = file_get_contents($Page);
	    }
    }
}

?>