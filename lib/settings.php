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
        $this->messages = getLangDefaults( $this->settings['DispLang'] );
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
	    $isGood = false;
	    $Errs = "";

	    switch ( NoNull($this->settings['dataset']) ) {
	    	case 'settings':
	    		// Update the Database and Debug Settings
		    	$isGood = $this->_createDBFile();
	    		break;

	    	case 'email':
	    		// Update the Email Settings
	    		$isGood = $this->_saveEmailConfig();
	    		break;

	    	case 'createdb':
	    		// Create the Database
	    		$isGood = $this->_createDBTables();
	    		break;

	    	case 'dashboard':
	    		// Update Some of the Dashboard Settings (?)
	    		break;

	    	case 'evernote':
	    		// Update the Evernote Data (If Necessary)
	    		break;

	    	case 'lists':
	    		// Update Some of the List Data
	    		break;

	    	case 'about':
	    		// Update the 'About Me' Data
	    		break;

		    case 'sites':
		    	// Update the Main Site Data
		    	$isGood = $this->_saveSiteData();
		    	break;

		    default:
		    	// Do Nothing
	    }

	    // Set the Return Message
    	if ( $isGood ) {
    		$rVal['isGood'] = BoolYN( $isGood );
	    	$rVal['Message'] = NoNull($this->messages['lblSetUpdGood'], "Successfully Updated Settings");
    	} else {
    		foreach ( $this->errors as $Key=>$Msg ) {
    			if ( $Errs != "" ) { $Errs .= "<br />\r\n"; }
	    		$Errs .= $Msg;
    		}
	    	$rVal['Message'] = $Errs;
    	}

	    // Return a Boolean Response
	    return $rVal;
    }

    /***********************************************************************
     *
     *
     *  Private Functions
     *
     *
     ***********************************************************************/

    /***********************************************************************
     *  Database
     ***********************************************************************/
    /**
     *	Function Records the Database Values if there are differences and returns
     *		a Boolean response
     */
    private function _createDBFile() {
	    $rVal = false;
	    
	    $DBType = nullInt( $this->settings['cmbStoreType'] );
	    $DBInfo = array();
	    $DBServ = NoNull( $this->settings['txtDBServ'], DB_SERV );
	    $DBName = NoNull( $this->settings['txtDBName'], DB_NAME );
	    $DBUser = NoNull( $this->settings['txtDBUser'], DB_USER );
	    $DBPass = NoNull( $this->settings['txtDBPass'], DB_PASS );
	    $isDebug = nullInt( $this->settings['cmbDebugMode'] );

	    // Validate the MySQL Login (If Necessary)
	    if ( $DBType == 1 ) {
		    $DBInfo = $this->_testSQLSettings( $DBServ, $DBName, $DBUser, $DBPass );
		    if ( !$DBInfo['LoginOK'] ) { $this->errors[] = NoNull($this->messages['lblSetUpdErr001'], "Invalid MySQL Settings"); }
	    }

	    // Record the Data
	    if ( $DBInfo['LoginOK'] ) {
		    $rVal = $this->_saveDBConfigData( $DBType, $DBServ, $DBName, $DBUser, $DBPass, $isDebug );
		    if ( !$rVal ) { $this->errors[] = NoNull($this->messages['lblSetUpdErr002'], "Could Not Save Configuration Data"); }
	    }

	    // Create the Tables if Necessary
	    if ( !$DBInfo['TableOK'] ) {
		    $rVal = $this->_createDBTables();
		    if ( !$rVal ) { $this->errors[] = NoNull($this->messages['lblSetUpdErr004'], "Could Not Populate Database"); }
	    }

	    // Return a Boolean Response
	    return $rVal;
    }

    /**
     *	Function Tests the SQL Login Data passed and Returns a Boolean Response
     */
    private function _testSQLSettings( $DBServ, $DBName, $DBUser, $DBPass ) {
    	$rVal = array( 'LoginOK' => false,
    				   'TableOK' => false,
    				  );
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
        	// Mark the Login as OK
        	$rVal['LoginOK'] = true;

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

        // Ensure the Database has 4 Valid Tables
        if ( $r == 4 ) {
	        $rVal['TableOK'] = true;
        }

        // Return the Status
        return $rVal;
    }

    /**
     *	Ensure the TableName is Valid
     *		Note: In a future version this should also check the Table Columns to ensure
     *			  they are up to date (or compatible) with the running version
     */
    private function _isValidSQLTable( $TableName ) {
	    $valid = array( 'Content', 'Meta', 'Type', 'SysParm' );
	    $rVal = false;

	    if ( in_array($TableName, $valid) ) { $rVal = true; }
	    
	    // Return the Boolean Status
	    return $rVal;
    }

    /**
     * Function Saves a Setting with a Specific Token to the Temp Directory
     */
    private function _saveDBConfigData( $DBType, $DBServ, $DBName, $DBUser, $DBPass, $isDebug) {
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

    /**
     *	Create the Tables
     *	Note: $doTruncate will (of course) first Truncate the Tables if they Exist
     */
    private function _createDBTables( $doTruncate = false ) {
    	$Actions = $this->_readSQLInstallScript( $doTruncate );
    	$DBName = DB_MAIN;
	    $rVal = false;

	    if ( $DBName != "" && is_array($Actions) ) {
		    foreach ( $Actions as $Key=>$sqlStr ) {
		    	$sqlDo = str_replace( "[DBNAME]", $DBName, $sqlStr );
			    doSQLExecute($sqlDo);
		    }
		    $rVal = true;
	    }

	    // Return the Success Value
	    return $rVal;
    }

    /**
     *	Read the SQL install.php file into an array
     */
    private function _readSQLInstallScript( $doTruncate = false ) {
    	$SQLFile = BASE_DIR . "/sql/install.sql";
	    $rVal = array();
	    $i = 0;

	    // Add the Table Truncation Lines (if Requested)
	    if ( $doTruncate ) {
		    $trunks = array( 'Type', 'Meta', 'Content', 'SysParm' );
		    foreach ( $trunks as $tbl ) {
			    $rVal[$i] = "TRUNCATE TABLE IF EXISTS `[DBNAME]`.`$tbl`;";
			    $i++;
		    }
	    }

	    // Add the Main Table Definitions & Populations
    	if ( file_exists($SQLFile) ) {
	    	$lines = file($SQLFile);

	    	foreach ( $lines as $line ) {
	    		$rVal[$i] .= $line;

	    		// If there is a Semi-Colon, The Line is Complete
	    		if ( strpos($line, ';') ) { $i++; }
	    	}

    	} else {
    		$rVal = false;
	    	$this->error[] = "SQL Installation File Missing!";
    	}

    	// Return the Array of SQL Strings
    	return $rVal;
    }

    /***********************************************************************
     *  Email
     ***********************************************************************/
    /**
     *	Function Records Email Settings to the appropriate Configuration File
     */
    private function _saveEmailConfig() {
	    $data = array( 'EmailOn'	 => NoNull($this->settings['cmbEmail'], 'N'),
	    			   'EmailServ'   => NoNull($this->settings['txtMailHost']),
		               'EmailPort'   => intval($this->settings['txtMailPort']),
		               'EmailUser'	 => NoNull($this->settings['txtMailUser']),
		               'EmailPass'	 => NoNull($this->settings['txtMailPass']),
		               'EmailSSL'	 => NoNull($this->settings['cmbMailSSL'], 'N'),
		               'EmailSendTo' => NoNull($this->settings['txtMailSendTo']),
		               'EmailReplyTo' => NoNull($this->settings['txtMailReply']),
		              );

		// Record the Data Accordingly
		foreach ( $data as $Key=>$Val ) {
			saveSetting( 'core', $Key, $Val );
		}

		// Return the Boolean Response
		return true;
    }

    /***********************************************************************
     *  Sites
     ***********************************************************************/
    /**
     *	Function Records Site Data to the appropriate Configuration File
     */
    private function _saveSiteData() {
    	$isDefault = ($this->settings['chkisDefault'] == "on") ? 'Y' : 'N';
    	$SiteID = nullInt( $this->settings['SiteID'] );
    	$RebuildCache = ( $this->settings['txtLocation'] != $this->settings['Location'] ) ? true : false;
    	$CacheToken = "Site_$SiteID";
	    $rVal = false;

	    $data = array('require_key'		=> 'Y',

		              'Location'        => $this->settings['txtLocation'],
		              'isDefault'       => $isDefault,

		              'SiteName'		=> $this->settings['txtSiteName'],
		              'SiteDescr'		=> $this->settings['txtSiteDescr'],
		              'SiteSEOTags'		=> $this->settings['txtSiteSEO'],

		              'doComments'		=> $this->settings['raComments'],
		              'doWebCron'		=> $this->settings['raWebCron'],
		              'DisqusID'     	=> $this->settings['txtDisqusID'],
		              'AkismetKey'		=> $this->settings['txtAkismetKey'],
		              'doTwitter'		=> $this->settings['raTwitter'],
		              'TwitName'		=> $this->settings['txtTwitName'],

		              'EN_ENABLED'		=> 'Y',
		              );

		// Record the Data Accordingly
		foreach ( $data as $Key=>$Val ) {
			saveSetting( $CacheToken, $Key, $Val );
		}

		if ( $RebuildCache ) {
			$rVal = scrubDIR( $this->settings['ContentDIR'] . '/cache' );
			$HomeURL = $this->settings['HomeURL'];
			$Pages = array( "/", "/archives/", "/rss/" );
			foreach ( $Pages as $Page ) {
				$cache = fopen($HomeURL . $Page, "r");
			}
		}

		// Return a Happy Boolean
		return true;
    }

}

?>