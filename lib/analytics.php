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
    }

    /** ********************************************************************** *
     *  Public Functions
     ** ********************************************************************** */
    function recordVisit() {
        return $this->_recordVisit();
    }

    public function getVisitorCount( $TodayOnly = false ) {
        return $this->_getVisitorCount( $TodayOnly );
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
                       'txnTable'   => getTableName( 'visitorTXN' ),
                       'PgRoot'     => $settings['PgRoot'],
                       'DataFile'   => 'core',
                      );

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

        $sqlStr = "INSERT INTO `$txnTable` (`DateStamp`, `SiteID`, `VisitURL`, " .
                                           "`VisitorIP4`, `VisitorIP6`, `UserAgent`, `ReferURL`, " .
                                           "`isAPI`, `isRSS`, `isOwner`) " .
                  "SELECT DATE_FORMAT(Now(), '%Y-%m-%d') as `DateStamp`, 0, '', '', '', '', '', 'N', 'N', 'N';";
        
    }

    /**
     *  Function Returns the Number of Visitors In the Last X Days
     *  Note: This Number is Cached, Cached, Cached!
     *        There are opportunities for the core value to be incorrect, but it's not
     *          important, and will be updated accordingly.
     */
    private function _getVisitorCount( $TodayOnly = false ) {
        $rVal = 0;
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
    	    $Replace = array( DB_NAME, $this->settings['txnTable'] );
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
            $ColName = "Tables_in_" . DB_NAME;
            foreach ( $rslt as $Row ) {
                if ( NoNull($Row[ $ColName ]) == $txnTable ) { return true; }
            }
        }
        
        // If We're Here, the Table Does Not Exist
        return false;
    }

}

?>