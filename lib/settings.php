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
    var $messages;
    var $errors;

    function __construct( $settings ) {
        $this->settings = $settings;
        $this->errors = array();
        
        // Load the User-Specified Language Files for this theme
        $LangFile = dirname(__FILE__) . "/lang/" . strtolower($this->settings['DispLang']) . ".php";

        if ( file_exists($LangFile) ){
            require_once( $LangFile );
            $LangClass = 'theme_' . strtolower( $this->settings['DispLang'] );
            $Lang = new $LangClass();

            // Append the List of Strings to the End of the Messages Array
            //      and replace any existing ones that may need the update
            foreach( $Lang->getStrings() as $Key=>$Val ) {
                $this->messages[ $Key ] = $Val;
            }

            // Kill the Class
            unset( $Lang );
        }
    }

    /***********************************************************************
     *  Public Functions
     ***********************************************************************/
    /**
     *	Determine Which Sets of Data Need to be Updated based on the Content and
     *		run the necessary Functions. Return a Boolean When Done.
     */
    function update() {
	    $rVal = array( 'isGood'	 => false,
	    			   'Message' => '[lblUnkErr]',
	    			  );
	    $Errs = "";

	    switch ( NoNull($this->settings['dataset']) ) {
	    	case 'settings':
	    		// Update the Database and Debug Settings
		    	$isGood = $this->_createDBFile();
		    	$rVal['isGood'] = BoolYN( $isGood );

		    	if ( $isGood ) {
			    	$rVal['Message'] = NoNull($this->messages['lblSetUpdGood'], "Successfully Updated Settings");
		    	} else {
		    		foreach ( $this->errors as $Key=>$Msg ) {
		    			if ( $Errs != "" ) { $Errs .= "<br />\r\n"; }
			    		$Errs .= $Msg;
		    		}
			    	$rVal['Message'] = $Errs;
		    	}
	    		break;
	    	
	    	case 'dashboard':
	    		break;

	    	case 'evernote':
	    		// Update the Evernote Data (If Necessary)
	    		break;

		    case 'sites':
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
	    
	    $DBType = nullInt( $this->settings['cmbStoreType'] );
	    $DBIsOK = true;
	    $DBServ = NoNull( $this->settings['txtDBServ'], DB_SERV );
	    $DBName = NoNull( $this->settings['txtDBName'], DB_NAME );
	    $DBUser = NoNull( $this->settings['txtDBUser'], DB_USER );
	    $DBPass = NoNull( $this->settings['txtDBPass'], DB_PASS );
	    $isDebug = nullInt( $this->settings['cmbDebugMode'] );

	    // Validate the MySQL Login (If Necessary)
	    if ( $DBType == 1 ) {
		    $DBIsOK = $this->_testSQLSettings( $DBServ, $DBName, $DBUser, $DBPass );
		    if ( !$DBIsOK ) { $this->errors[] = NoNull($this->messages['lblSetUpdErr001'], "Invalid MySQL Settings"); }
	    }

	    // Record the Data
	    if ( $DBIsOK ) {
		    $rVal = $this->_saveConfigData( $DBType, $DBServ, $DBName, $DBUser, $DBPass, $isDebug );
		    if ( !$rVal ) { $this->errors[] = NoNull($this->messages['lblSetUpdErr002'], "Could Not Save Configuration Data"); }
	    }

	    // Return a Boolean Response
	    return $rVal;
    }


    private function _testSQLSettings( $DBServ, $DBName, $DBUser, $DBPass ) {
	    $rVal = false;
	    $r = 0;

	    if ( $DBServ == "" || $DBName == "" || $DBUser == "" || $DBPass == "" ) {
		    return $rVal;
	    }

	    // Test the Connection
	    $sqlStr = "SHOW TABLES;";
        $db = mysql_connect($DBServ, $DBUser, $DBPass);
        $selected = mysql_select_db($DBName, $db);
        $utf8 = mysql_query("SET NAMES " . DB_CHARSET);
        $result = mysql_query($sqlStr);

        if ( $result ) {
            // Read the Result into an Array
            $ColName = "Tables_in_$DBName";
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            	if ( $this->_isValidSQLTable( NoNull($row[$ColName]) ) ) {
	            	$r++;
            	}
            }

            // Close the MySQL Connection
            mysql_close( $db );
        }

        // Ensure the Database has 3 Valid Tables
        if ( $r == 3 ) { $rVal = true; }

        // Return the Status
        return $rVal;
    }

    /**
     *	Ensure the TableName is Valid
     *		Note: In a future version this should also check the Table Columns to ensure
     *			  they are up to date (or compatible) with the running version
     */
    private function _isValidSQLTable( $TableName ) {
	    $valid = array( 'Content', 'Meta', 'Type' );
	    $rVal = false;

	    if ( in_array($TableName, $valid) ) { $rVal = true; }
	    
	    // Return the Boolean Status
	    return $rVal;
    }

    /**
     * Function Saves a Setting with a Specific Token to the Temp Directory
     */
    private function _saveConfigData( $DBType, $DBServ, $DBName, $DBUser, $DBPass, $isDebug) {
    	// Perform some VERY basic Validation here
    	if ( $isDebug < 0 || $isDebug > 1 ) { $isDebug = 0; }
    	if ( $DBType < 1 || $DBType > 2 ) { $DBType = 2; }

	    // Check to see if the Settings File Exists or Not
	    if ( checkDIRExists( CONF_DIR ) ) {
		    $ConfFile = CONF_DIR . '/config-db.php';
		    $ConfData = "<?php \r\n" .
		    			"\r\n" .
		    			"   /** ************************************* **\r\n" .
		    			"    *  DO NOT CHANGE THESE SETTINGS MANUALLY  *\r\n" .
		    			"    *  These Settings are Controlled Through  *\r\n" .
		    			"    *  Your Noteworthy Admin / Settings Page  *\r\n" .
		    			"    ** ************************************* **/\r\n" .
		    			"define('DB_SERV', '$DBServ');\r\n" .
		    			"define('DB_MAIN', '$DBName');\r\n" .
		    			"define('DB_USER', '$DBUser');\r\n" .
		    			"define('DB_PASS', '$DBPass');\r\n" .
		    			"define('DB_CHARSET', 'utf8');\r\n" .
		    			"define('DB_COLLATE', 'UTF8_UNICODE_CI');\r\n" .
		    			"define('DB_TYPE', $DBType);\r\n" .
		    			"define('DEBUG_ENABLED', $isDebug);\r\n" .
		    			"\r\n" .
		    			"?>";

		    // Write the File to the Configuration Folder
		    $fh = fopen($ConfFile, 'w+');
		    fwrite($fh, $ConfData);
		    fclose($fh);
	    }

	    // Return a Happy Boolean
	    return true;
    }
}

?>