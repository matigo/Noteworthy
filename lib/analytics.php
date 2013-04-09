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
                       'txnTable'   => getTableName( 'VisitTXN' ),
                       'dtlTable'   => getTableName( 'VisitDTL' ),
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
        $dtlTable = $this->settings['dtlTable'];
        $isRES = BoolYN( $this->_isResource() );
        $rVal = false;

        // Construct the INSERT Statement and Record It
        $dsvID = "CONCAT(DATE_FORMAT(Now(), '%Y-%m-%d %h:00:00'), '-', " . 
                         nullInt($this->settings['SiteID']) .  ", '-', " . 
                        "'" . sqlScrub($this->settings['RequestURL']) . "')";
        $sqlStr = "INSERT INTO `$txnTable` (`dsvID`, `DateStamp`, `SiteID`, `VisitURL`, `Hits`, `isResource`, `UpdateDTS`) " .
                  "VALUES ( MD5($dsvID), DATE_FORMAT(Now(), '%Y-%m-%d %h:00:00'), " . nullInt($this->settings['SiteID']) . "," .
                          " '" . sqlScrub($this->settings['RequestURL']) . "'," .
                          " 1, '$isRES', Now() )" .
                  "ON DUPLICATE KEY UPDATE `Hits` = `Hits` + 1," .
                                         " `UpdateDTS` = Now();" .
                  "INSERT INTO `$dtlTable` (`SiteID`, `DateStamp`, `VisitURL`, `ReferURL`, `SearchQuery`, `isResource`, `isSearch`, `UpdateDTS`) " .
                  "VALUES ( " . nullInt($this->settings['SiteID']) . ", DATE_FORMAT(Now(), '%Y-%m-%d %h:00:00')," . 
                          " '" . sqlScrub($this->settings['RequestURL']) . "'," .
                          " '" . sqlScrub($this->settings['Referrer']) . "'," .
                          " '', '$isRES', 'N', Now() );";
        $rslt = doSQLExecute( $sqlStr );
        if ( $rslt > 0 ) { $rVal = true; }

        // Return the Boolean Response
        return $rVal;
    }

    /**
     *  Function Returns the Number of Visitors In the Last 24 Hours
     */
    private function _getVisitorCounts( $Days = 1 ) {
        $txnTable = $this->settings['txnTable'];
        $Days = nullInt( $Days, 1 );
        $rVal = 0;

        $sqlStr = "SELECT DATE_FORMAT(`DateStamp`, '%Y-%m-%d') as `DTS`, sum(`Hits`) as `PageViews`" .
                  "  FROM `$txnTable`" .
                  " WHERE `isResource` = 'N' and `isDeleted` = 'N'" .
                  "   and `DateStamp` >= DATE_FORMAT(DATE_SUB(Now(), INTERVAL $Days DAY), '%Y-%m-%d %H:00:00')" .
                  "   and `SiteID` = " . nullInt($this->settings['SiteID']) .
                  " GROUP BY `DTS`;";
        $rslt = doSQLQuery( $sqlStr );
        if ( is_array($rslt) ) {
            $rVal = nullInt( $rslt[0]['PageViews'] );
        }
        
        writeNote( "PageViews: $rVal" );

        // Return the Array
        return $rVal;
    }

    /**
     *  Function Returns the X Most Popular Pages in the Last 24 Hours
     */
    private function _getVisitorPages( $Limit = 10, $Days = 1 ) {
        $dtlTable = $this->settings['dtlTable'];
        $Days = nullInt( $Days, 1 );
        $rVal = array();

        $sqlStr = "SELECT `VisitURL`, sum(`Hits`) as `PageViews`" .
                  "  FROM `$dtlTable`" .
                  " WHERE `isDeleted` = 'N' and `isResource` = 'N'" .
                  "   and `isSearch` = 'N' and `SiteID` = " . nullInt($this->settings['SiteID']) .
                  "   and `DateStamp` >= DATE_FORMAT(DATE_SUB(Now(), INTERVAL $Days DAY), '%Y-%m-%d %H:00:00')" .
                  " GROUP BY `VisitURL`" .
                  " ORDER BY `PageViews` DESC" .
                  " LIMIT 0, $Limit";
        $rslt = doSQLQuery( $sqlStr );
        if ( is_array($rslt) ) {
            foreach ( $rslt as $Row ) {
                $rVal[ NoNull($Row['VisitURL']) ] = nullInt($Row['PageViews']);
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
            $sqlStr = $this->_readTXNTableDefinitions();
            if ( $sqlStr ) {
                $rslt = doSQLExecute( $sqlStr );
            }

            // Confirm the Table Exists, and Save the TXN Name if Appropriate
            if ( $this->_confirmTXNTables() ) {
                $rVal = saveSetting( $this->settings['DataFile'], 'txnTable', $txnTable );
                writeNote( "Saved Transaction Table Record: " . BoolYN($rVal) );
            }
        }

        // Return the Boolean Response
        return $rVal;
    }
    
    private function _readTXNTableDefinitions() {
        $SQLFile = BASE_DIR . "/sql/visittxn.sql";
        writeNote( "Reading SQL Scripts File: $SQLFile" );
        $rVal = "";

	    // Add the Main Table Definitions & Populations
    	if ( file_exists($SQLFile) ) {
    	    $Search = array('[DBNAME]', '[TXNTABLE]', '[DTLTABLE]' );
    	    $Replace = array( DB_MAIN, $this->settings['txnTable'], $this->settings['dtlTable'] );
	    	$lines = file($SQLFile);
	    	$rVal = "";

	    	foreach ( $lines as $line ) {
	    		$rVal .= str_replace( $Search, $Replace, NoNull($line) );
	    	}
    	} else {
        	$rVal = false;
    	}

    	// Return the SQL String Array or Unhappy Boolean
    	return $rVal;
    }

    private function _confirmTXNTables() {
        $txnTables = array( $this->settings['txnTable'],
                            $this->settings['dtlTable'],
                           );
        $rVal = false;
        $i = 0;

        $sqlStr = "SHOW TABLES;";
        $rslt = doSQLQuery( $sqlStr );
        if ( is_array($rslt) ) {
            $ColName = "Tables_in_" . DB_MAIN;
            foreach ( $rslt as $Row ) {
                if ( in_array( NoNull($Row[ $ColName ]), $txnTables ) ) { $i++; }
            }

            writeNote( "Found $i of " . count($txnTables) . " Statistics Tables" );

            // If the Number of Identified Tables Is Equal To the Tables Required ...
            if ( $i == count($txnTables) ) { $rVal = true; }
        }

        // Return the Boolean Response
        return $rVal;
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