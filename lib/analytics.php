<?php

/**
 * @author Jason F. Irwin
 * @copyright 2013
 * 
 * Class contains the rules and methods called for the Application Analytics Package
 */
require_once( LIB_DIR . '/functions.php');

class Analytics {
    var $settings;

    function __construct( $settings ) {
        $this->settings = $this->_populateClass( $settings );

        // Ensure the Tables Exist
        $this->_checkTXNTableExists();
    }

    /** ********************************************************************** *
     *  Public Functions
     ** ********************************************************************** */
    public function recordVisit() {
        return $this->_recordVisit();
    }

    public function getVisitorCount() {
        return $this->_getVisitorCounts();
    }
    
    public function getVisitorPages( $Limit = 10 ) {
        return $this->_getVisitorPages( $Limit );
    }

    /** ********************************************************************** *
     *  Private Functions
     ** ********************************************************************** */
    /**
     *  Function Prepares the Class for Use
     */
    private function _populateClass( $settings ) {
        $rVal = array( 'days'       => nullInt($settings['days'], 7),
                       'UserID'     => nullInt($settings['UserID']),
                       'UserToken'  => NoNull($settings['token']),
                       'SiteID'     => NoNull($settings['SiteID']),
                       'Referrer'   => NoNull($_SERVER['HTTP_REFERER']),
                       'RequestURL' => NoNull($_SERVER["REQUEST_URI"]),
                       'PgRoot'     => $settings['PgRoot'],
                       'IPv4'       => '',
                       'IPv6'       => '',
                       'txnTable'   => getTableName( 'visitorTXN' ),
                       'DataFile'   => 'core',
                      );

        // Construct the Full URL
        for ( $i = 1; $i < 10; $i++ ) {
            if ( array_key_exists("PgSub$i", $settings) ) {
                $rVal["PgSub$i"] = $settings["PgSub$i"];
            }
        }

        // Determine the Browser Info
        $info = get_browser(null, true);
        $rVal['UserAgent'] = NoNull($_SERVER['HTTP_USER_AGENT'] );
        $rVal['BrowserVer'] = NoNull($info['version']);
        $rVal['Browser'] = NoNull($info['browser']);
        $rVal['Platform'] = NoNull($info['platform']);

        // Get the Visitor IP
        $IP = $this->_getVisitorIP();
        if ( strlen($IP) > 20 ) {
            $rVal['IPv6'] = $IP;
        } else {
            $rVal['IPv4'] = $IP;
        }

        // Return the Array of Data
        return $rVal;
    }

    /**
     *  Function Records the Visitor to the Database
     *  Note: If the visitor is the owner, then the record is marked with the appropriate User.ID
     *        Record is stored in the database, and the Site_X value is incremented by 1
     */
    private function _recordVisit() {
        $txnTable = $this->settings['txnTable'];
        $isRSS = BoolYN( $this->settings['PgRoot'] == 'rss' );
        $isAPI = BoolYN( $this->settings['PgRoot'] == 'api' );
        $isRES = BoolYN( $this->_isResource() );
        $rVal = false;

        $sqlStr = "INSERT INTO `$txnTable` (`DateStamp`, `SiteID`, `VisitURL`, `ReferURL`, " .
                                           "`VisitorIP4`, `VisitorIP6`, `UserAgent`, " .
                                           "`Browser`, `BrowserVer`, `Platform`, " .
                                           "`isAPI`, `isRSS`, `isResource`, `isOwner`) " .
                  "SELECT DATE_FORMAT(Now(), '%Y-%m-%d %H:00:00'), " .
                               nullInt($this->settings['SiteID']) . ", " .
                        " '" . sqlScrub($this->settings['RequestURL']) . "', " .
                        " '" . sqlScrub($this->settings['Referrer']) . "', " .
                        " '" . sqlScrub($this->settings['IPv4']) . "', " .
                        " '" . sqlScrub($this->settings['IPv6']) . "', " .
                        " '" . sqlScrub($this->settings['UserAgent']) . "', " .
                        " '" . sqlScrub($this->settings['Browser']) . "', " .
                        " '" . sqlScrub($this->settings['BrowserVer']) . "', " .
                        " '" . sqlScrub($this->settings['Platform']) . "', " .
                        " '$isRSS', '$isAPI', '$isRES', 'N';";

        // Record the Visit
        $rslt = doSQLExecute( $sqlStr );
        if ( $rslt > 0 ) { $rVal = true; }

        // Return the Boolean Response
        return $rVal;
    }

    /**
     *  Function Returns the Number of Visitors In the Last 24 Hours
     */
    private function _getVisitorCounts() {
        $txnTable = $this->settings['txnTable'];
        $rVal = array( 'PageViews' => 0,
                       'Visitors'  => 0,
                      );

        $sqlStr = "SELECT DATE_FORMAT(`DateStamp`, '%Y-%m-%d') as `DTS`, count(`id`) as `PageViews`, count(DISTINCT `VisitorIP4`) as `Visitors`" .
                  "  FROM `$txnTable`" .
                  " WHERE `isResource` = 'N' and `isAPI` = 'N'" .
                  "   and `isRSS` = 'N' and `isDeleted` = 'N'" .
                  "   and `DateStamp` >= DATE_FORMAT(DATE_SUB(Now(), INTERVAL 1 DAY), '%Y-%m-%d %H:00:00')" .
                  " GROUP BY `DTS`";
        $rslt = doSQLQuery( $sqlStr );
        if ( is_array($rslt) ) {
            foreach ( $rVal as $Key=>$Val ) {
                $rVal[ $Key ] = nullInt( $rslt[0][$Key] );
            }
        }

        // Return the Array
        return $rVal;
    }

    /**
     *  Function Returns the X Most Popular Pages in the Last 24 Hours
     */
    private function _getVisitorPages( $Limit = 10 ) {
        $txnTable = $this->settings['txnTable'];
        $rVal = array();

        $sqlStr = "SELECT `VisitURL`, count(`id`) as `Hits`" . 
                  "  FROM `$txnTable`" .
                  " WHERE `isResource` = 'N' and `isAPI` = 'N'" .
                  "   and `isRSS` = 'N' and `isDeleted` = 'N'" .
                  "   and `DateStamp` >= DATE_FORMAT(DATE_SUB(Now(), INTERVAL 1 DAY), '%Y-%m-%d %H:00:00')" .
                  " GROUP BY `VisitURL`" .
                  " ORDER BY `Hits` DESC, `VisitURL`" .
                  "  LIMIT 0, $Limit";
        $rslt = doSQLQuery( $sqlStr );
        if ( is_array($rslt) ) {
            foreach ( $rslt as $Row ) {
                $rVal[ NoNull($Row['VisitURL']) ] = nullInt($Row['Hits']);
            }
        }

        // Return the List
        return $rVal;
    }
    
    /** ********************************************************************** *
     *  TXN Table Check & Creation Functions
     ** ********************************************************************** */
    /**
     *  Function Confirms the Necessary TXN Table Exists
     */
    private function _checkTXNTableExists() {
        $curTable = readSetting( $this->settings['DataFile'], 'txnTable' );
        $txnTable = $this->settings['txnTable'];
        $rVal = false;

        // If the Transaction Table is Not the Same as Expected, then Create It
        if ( $curTable != $txnTable ) {
            $sqlStrs = $this->_readTXNTableDefinitions();
            if ( $sqlStrs ) {
                foreach ( $sqlStrs as $sqlStr ) {
                    $rslt = doSQLExecute( $sqlStr );
                }
            }

            // Confirm the Table Exists, and Save the TXN Name if Appropriate
            if ( $this->_confirmTXNTable() ) {
                $rVal = saveSetting( $this->settings['DataFile'], 'txnTable', $txnTable );
            }
        }

        // Return the Boolean Response
        return $rVal;
    }
    
    private function _readTXNTableDefinitions() {
        $SQLFile = BASE_DIR . "/sql/visittxn.sql";
        writeNote( "Reading SQL Scripts File: $SQLFile" );
        $rVal = array();

	    // Add the Main Table Definitions & Populations
    	if ( file_exists($SQLFile) ) {
    	    $Search = array('[DBNAME]', '[TXNTABLE]' );
    	    $Replace = array( DB_MAIN, $this->settings['txnTable'] );
	    	$lines = file($SQLFile);
	    	$rVal = "";

	    	foreach ( $lines as $line ) {
	    		$rVal[$i] .= str_replace( $Search, $Replace, NoNull($line) );

	    		// If there is a Semi-Colon, The Line is Complete
	    		if ( strpos($line, ';') ) { $i++; }
	    	}
    	} else {
        	$rVal = false;
    	}

    	// Return the SQL String Array or Unhappy Boolean
    	return $rVal;
    }

    private function _confirmTXNTable() {
        $txnTable = $this->settings['txnTable'];
        $sqlStr = "SHOW TABLES;";
        $rslt = doSQLQuery( $sqlStr );

        if ( is_array($rslt) ) {
            $ColName = "Tables_in_" . DB_MAIN;
            foreach ( $rslt as $Row ) {
                if ( NoNull($Row[ $ColName ]) == $txnTable ) { return true; }
            }
        }
        
        // If We're Here, the Table Does Not Exist
        return false;
    }

    /** ********************************************************************** *
     *  Initial Configuration Functions
     ** ********************************************************************** */
    /**
     *  Get the IP Address of the Visitor
     */
    private function _getVisitorIP() {
        $rVal = "";

        if ( isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ) {
            $rVal = $_SERVER["HTTP_CF_CONNECTING_IP"];
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $rVal = getenv('HTTP_X_FORWARDED_FOR');
            } else {
                $rVal = getenv('REMOTE_ADDR');
            }
        }

        // Return the IPv4 Address
        return NoNull($rVal);
    }
    
    /**
     *  Function Determines if the URL Being Hit is a Resource or Not
     */
    private function _isResource() {
        $Info = explode('.', $_SERVER["REQUEST_URI"]);
        $rVal = false;

        if ( count($Info) > 1 ) { $rVal = true; }

        // Return the Boolean Response
        return $rVal;
    }
}

?>