<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for Application Settings Data
 */
require_once(LIB_DIR . '/functions.php');

class Settings extends Midori {
    var $settings;

    function __construct( $settings ) {
        $this->settings = $settings;
    }

    /***********************************************************************
     *  Public Functions
     ***********************************************************************/
    /**
     *	Determine Which Sets of Data Need to be Updated based on the Content and
     *		run the necessary Functions. Return a Boolean When Done.
     */
    function update() {
	    $rVal = false;

	    switch ( NoNull( $this->settings['dataset']) ) {
	    	'settings':
	    		// Update the Database and Debug Settings
	    		$rVal = $this->_createDBFile();
	    		break;

	    	'evernote':
	    		// Update the Evernote Data (If Necessary)
	    		break;

		    'sites':
		    	// Update the Main Site Data
		    	break;

		    default:
		    	// Do Nothing
	    }

	    // Return a Boolean Response
	    return $rVal;
    }

    /***********************************************************************
     *  Private Functions
     ***********************************************************************/

    /**
     *	Function Records the Database Values if there are differences and returns
     *		a Boolean response
     */
    private function _createDBFile() {
	    $rVal = false;
	    
	    $DBType = nullInt( $this->settings['cmbDBType'] );
	    $DBIsOK = true;
	    $DBServ = NoNull( $this->settings['txtDBServ'], DB_SERV );
	    $DBName = NoNull( $this->settings['txtDBName'], DB_NAME );
	    $DBUser = NoNull( $this->settings['txtDBUser'], DB_USER );
	    $DBPass = NoNull( $this->settings['txtDBPass'], DB_PASS );

	    // Validate the MySQL Login (If Necessary)
	    if ( $DBType == 1 ) {
		    
	    }
	    
	    // Record the Data
	    if ( $DBIsOK ) {
		    
	    }
	    
	    // Return a Boolean Response
	    return $rVal;
    }

}

?>