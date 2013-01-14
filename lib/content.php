<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for Content Data
 */
require_once(LIB_DIR . '/functions.php');

class Content extends Midori {
    var $settings;
    var $messages;

    function __construct( $Settings, $Messages, $ThemeLoc = '' ) {
        $this->settings = $Settings;
        $this->settings['ThemeLoc'] = $ThemeLoc;
        $this->settings['LastContentID'] = $this->_getLastContentID();
        
        if ( is_array($Messages) ) {
	        $this->messages = $Messages;
        } else {
	        $this->messages = getLangDefaults( $this->settings['DispLang'] );
        }
    }

    /***********************************************************************
     *  Public Functions
     ***********************************************************************/
    function getContent( $Results = 25, $doOverride = false ) {
    	$rVal = $this->_readCachedHTML( $this->_getReadableURI() );
    	if ( !$rVal ) { $rVal = $this->_getContent( $Results, $doOverride ); }

    	// Return the Content
        return $rVal;
    }
    
    function getSiteLinks() {
	    return $this->_getSiteLinks();
    }

    function getPageTitle( $PostURL = "" ) {
        return $this->_getPageTitle( $PostURL );
    }
    
    function getPagePagination() {
	    return $this->_getPagePagination();
    }
    
    function getRawArchives( $Results = 9999 ) {
    	$CacheFile = 'archives_raw_' . $Results;
    	$rVal = $this->_readCachedHTML( $CacheFile );
    	if ( !$rVal ) {
    		$rVal = $this->_getRawArchives( $Results );
    		$this->_saveCachedHTML( $CacheFile, $rVal );
    	}
	    return $rVal;
    }

    function getMonthlyArchives() {
    	$CacheFile = 'archives_mo';
    	$rVal = $this->_readCachedHTML( $CacheFile );
    	if ( !$rVal ) {
    		$rVal = $this->_getArchiveList( $CacheFile );
    	}
	    return $rVal;
    }
    
    function getTweetsList( $Results = 25 ) {
	    $CacheFile = "tweets_$Results";
	    $rVal = $this->_readCachedHTML( $CacheFile, true );
    	if ( !$rVal ) {
    		$rVal = $this->_getTweetsList( $Results );
    	}
	    return $rVal;
    }
    
    function getTagsList( $IncludeAll = false ) {
    	$CacheFile = strtolower( 'tags_' . BoolYN($IncludeAll) );
    	$rVal = $this->_readCachedHTML( $CacheFile );
    	if ( !$rVal ) {
	    	$rVal = $this->_getTagsList( $IncludeAll );
    	}
	    return $rVal;
    }

    function getPostList( $PostCount = 15, $OnThisDay = false ) {
    	$CacheSuffix = ( $OnThisDay ) ? "_" . date("ymd") : "";
    	$CacheFile = 'posts_' . $PostCount . $CacheSuffix;

    	$rVal = $this->_readCachedHTML( $CacheFile );
    	if ( !$rVal ) {
	    	$rVal = $this->_getPostList( $PostCount, $OnThisDay );
    		$this->_saveCachedHTML( $CacheFile, $rVal );
    	}
	    return $rVal;
    }

    function getValidPostYears() {
	    $CacheFile = 'valids';
    	$rVal = $this->_readCachedHTML( $CacheFile );
    	if ( !$rVal ) {
	    	$rVal = $this->_getValidPostYears();
    		$this->_saveCachedHTML( $CacheFile, $rVal );
    	}
	    return $rVal;
    }

    function getReadableURI() {
	    return $this->_getReadableURI();
    }
    
    function getCompletePostsList() {
	    return $this->_getCompletePostsList();
    }

    function getRSS() {
    	$CacheFile = 'rss';
    	$rVal = $this->_readCachedHTML( $CacheFile );
    	if ( !$rVal ) {
    		$rVal = $this->_getRSS();
    		$this->_saveCachedHTML( $CacheFile, $rVal );
    	}
	    return $rVal;
    }

    function saveCacheHTML( $HTML, $FileName = "", $UseCurrentID = false ) {
	    $CacheFile = ( $FileName == "" ) ? $this->_getReadableURI() : $FileName;
	    return $this->_saveCachedHTML( $CacheFile, $HTML, $UseCurrentID );
    }

    /***********************************************************************
     *  Private Functions
     ***********************************************************************/
    /**
     *	Function Returns the Most Recent X Tweets
     */
    private function _getTweetsList( $Results = 25 ) {
    	$PageNo = 1;
    	if ( array_key_exists('Page', $this->settings) ) {
		    $PageNo = nullInt($this->settings['Page'], 1);
    	}
    	$rVal = false;
    	
    	$StartNo = intval($PageNo) - 1;
    	if ( $StartNo < 0 ) { $StartNo = 0; }
    	$sqlStr = "SELECT c.`Value` as `Tweet`, c.`CreateDTS`, UNIX_TIMESTAMP(c.`CreateDTS`) as `CreateUTS`, c.`PostURL`" .
    			  "  FROM `Content` c" .
    			  " WHERE c.`isReplaced` = 'N' and c.`TypeCd` = 'TWEET'" .
    			  "   and c.`CreateDTS` <= Now()" .
    			  " ORDER BY c.`CreateDTS` DESC" .
    			  " LIMIT $StartNo, $Results";
    	$rslt = doSQLQuery( $sqlStr );
    	if ( is_array($rslt) ) {
    		$rVal = $rslt;
    	}

    	// Return the Result
    	return $rVal;
    }

    /**
     *	Function Returns the Site Links Assigned in the Administration Panel
     */
    private function _getSiteLinks() {
    	$SocItems = array('SocName', 'SocLink', 'SocShow');
    	$SiteID = 0;
    	$rVal = array();
    	if ( array_key_exists('SiteID', $this->settings) ) {
	    	$SiteID = nullInt( $this->settings['SiteID'] );
    	}
    	$SiteToken = "Site_$SiteID";

    	for ( $i = 1; $i<= 5; $i++ ) {
    		$KeySuffix = str_pad((int) $i, 2, "0", STR_PAD_LEFT);
    		$doShow = $this->settings["SocShow$KeySuffix"];
    		
    		if ( $doShow == "Y" ) {
    			$Name = $this->settings["SocName$KeySuffix"];
	    		$rVal[ $Name ] = $this->settings["SocLink$KeySuffix"];
    		}
    	}

    	// Return the Array
    	return $rVal;
    }

    private function _getCompletePostsList() {
	    $RecordTotal = 0;
	    $Results = 25;
	    $PageNo = nullInt($this->settings['Page'], 1);
	    $rVal = false;

	    switch ( DB_TYPE ) {
		    case 1:
		    	// MySQL
		    	$StartNo = ($PageNo - 1) * $Results;
		    	$sqlStr = $this->_getAppropriateSQLQuery( '', 'WITHGAPS-COUNT' );
		    	$meta = doSQLQuery( $sqlStr );
		    	if ( is_array($meta) ) {
			    	foreach( $meta as $Key=>$Row ) {
				    	$RecordTotal = nullInt( $Row['Records'] );
			    	}
		    	}

		    	// Collect the Records
		    	$sqlStr = $this->_getAppropriateSQLQuery( '', 'WITHGAPS', $StartNo, $Results );
		    	$data = doSQLQuery( $sqlStr );
		    	if ( is_array($data) ) {
		    		$rVal = array();
			    	foreach( $data as $Key=>$Row ) {
			    		$Row['MetaRecords'] = intval($Row['MetaRecords']);
			    		$Row['PostLength'] = intval($Row['PostLength']);
			    		$Row['CreateDTS'] = intval($Row['CreateDTS']);
			    		$Row['UpdateDTS'] = intval($Row['UpdateDTS']);
			    		$Row['id'] = intval($Row['id']);
			    		$Row['RecordTotal'] = intval($RecordTotal);
			    		$Row['PageNo'] = intval($PageNo);
			    		$Row['Results'] = intval($Results);
				    	$rVal[ $Key ] = $Row;
			    	}
		    	}
		    	break;

		    case 2:
		    	// This Hasn't Been Coded, Yet
		    	break;

		    default:
		    	// Do Nothing (Yet)
		}

		// Return the Results
		return $rVal;
    }

    /**
     *	Function Returns a Readable URL, which consists of the location and PageID
     */
    private function _getReadableURI() {
    	$rVal = "";

    	switch ( $this->settings['ReqURI'] ) {
	    	case '/':
	    		$rVal = 'home';
	    		break;

	    	default:
	    		// Massage the Request URI
	    		// -- If It's Numeric (An Archive Page), Keep It Numeric
	    		// -- If It's Not, Then Replace Slashes with Underscores
	    		if ( is_numeric(str_replace('/', '', $this->settings['ReqURI'])) ) {
		    		$rVal = str_replace('/', '', $this->settings['ReqURI']);
	    		} else {
		    		$rVal = str_replace('/', '_', $this->settings['ReqURI']);
	    		}
	    		if ( startsWith($rVal, '_') ) { $rVal = substr($rVal, 1); }
    	}
    	
    	// Append the Page Number if Applicable (Greater Than 1)
    	$PageNo = 1;
    	if ( array_key_exists('Page', $this->settings) ) {
	    	$PageNo = nullInt($this->settings['Page']);
    	}
    	if ( $PageNo > 1 ) { $rVal .= "_$PageNo"; }

	    // Return the Readable URI
	    return $rVal;
    }
    
    private function _getPagePagination() {
    	$PostURL = sqlScrub( $this->settings['ReqURI'] );
    	$PageNo = 1;
    	$UnixTS = 0;
    	if ( array_key_exists('Page', $this->settings) ) {
	    	$PageNo = nullInt($this->settings['Page']);
    	}
    	$rVal = array();
	    
	    // Don't Continue if the PostURL Is Blank
	    if ( $PostURL == "" ) { return $rVal; }
	    
	    $sqlStr = "SELECT UNIX_TIMESTAMP(c.`CreateDTS`) as `UnixTS` FROM `Content` c, `Meta` m" .
	    		  " WHERE c.`id` = m.`ContentID` and m.`TypeCd` = 'POST-URL'" .
	    		  "   and c.`isReplaced` = 'N' and c.`CreateDTS` <= Now()" .
	    		  "   and m.`Value` LIKE '%$PostURL%'" .
	    		  " LIMIT 0, 1;";
	    $rslt = doSQLQuery( $sqlStr );
	    if ( is_array($rslt) ) {
		    $UnixTS = intval( $rslt[0]['UnixTS'] );
	    }

	    // If we have a Unix Timestamp, Run a Second Query
	    if ( $UnixTS > 0 ) {
	    	unset( $rslt );
		    $sqlStr = "SELECT 'Prev' as `IDX`, a.`id`, a.`Title`, a.`CreateDTS`, a.`Value`" .
		    		  "  FROM (SELECT c.`id`, c.`Title`, c.`CreateDTS`, m.`Value`" .
		    		  		"	 FROM `Content` c, `Meta` m" .
		    		  		"   WHERE m.`ContentID` = c.`id` and c.`isReplaced` = 'N'" .
		    		  		"	  and c.`TypeCd` = 'POST' and m.`TypeCd` = 'POST-URL'" .
		    		  		"	  and c.`CreateDTS` <= FROM_UNIXTIME($UnixTS)" .
		    		  		"	  and m.`Value` NOT LIKE '%$PostURL%'" .
		    		  		"   ORDER BY `CreateDTS` DESC" .
		    		  		"   LIMIT 0, 1) a" .
		    		  " UNION ALL " .
		    		  "SELECT 'Next' as `IDX`, b.`id`, b.`Title`, b.`CreateDTS`, b.`Value`" .
		    		  "  FROM (SELECT c.`id`, c.`Title`, c.`CreateDTS`, m.`Value`" .
		    		  		"	 FROM `Content` c, `Meta` m" .
		    		  		"   WHERE m.`ContentID` = c.`id` and c.`isReplaced` = 'N'" .
		    		  		"	  and c.`TypeCd` = 'POST' and m.`TypeCd` = 'POST-URL'" .
		    		  		"	  and c.`CreateDTS` >= FROM_UNIXTIME($UnixTS)" .
		    		  		"	  and m.`Value` NOT LIKE '%$PostURL%'" .
		    		  		"   ORDER BY `CreateDTS`" .
		    		  		"   LIMIT 0, 1) b";
		    $rslt = doSQLQuery( $sqlStr );
		    if ( is_array($rslt) ) {
			    foreach( $rslt as $Key=>$Row ) {
		    		$rVal[ $Row['IDX'] ] = array( "id"		  => $Row['id'],
					    					      "Title"	  => $Row['Title'],
					    					      "CreateDTS" => $Row['CreateDTS'],
					    					      "PostURL"	  => $Row['Value']
				    					     );
			    }
		    }
	    }

	    // Return the Array
	    return $rVal;
    }

    /**
     *	Function Returns just the Title of the Post, or an Empty String
     */
    private function _getPageTitle( $PostURL = "" ) {
	    if ( $PostURL == "" ) { $PostURL = $this->settings['ReqURI']; }
	    $rVal = "";
	    $URL = sqlScrub( $PostURL );

		$sqlStr = "SELECT c.`Title` FROM `Content` c" .
				  " WHERE c.`isReplaced` = 'N' and c.`TypeCd` = 'POST'" .
				  "   and c.`PostURL` LIKE '%$URL%'" .
				  " LIMIT 0, 1;";
		$rslt = doSQLQuery( $sqlStr );
		if ( is_array($rslt) ) {
			if ( NoNull($rslt[0]['Title']) != "" ) {
				$rVal = NoNull($rslt[0]['Title']);
			}
		}

		// Return the Title or an Empty String
		return $rVal;
    }

    private function _getArchiveList( $Type = 'All' ) {
	    $rVal = array();
	    $TotalPosts = 0;
	    $Items = 0;
	    
	    switch ( $Type ) {
		    case 'archives_mo':
		    	$sqlStr = "SELECT DATE_FORMAT(c.`CreateDTS`, '%Y') as `DTYear`," .
		    					" DATE_FORMAT(c.`CreateDTS`, '%m') as `DTMonth`," .
		    					" count(`id`) as `PostCount`" .
		    			  "  FROM `Content` c" .
		    			  " WHERE c.`isReplaced` = 'N' and c.`TypeCd` = 'POST'" .
		    			  "   and c.`CreateDTS` <= Now()" .
		    			  " GROUP BY `DTYear` DESC, `DTMonth` DESC" .
		    			  " ORDER BY `DTYear` DESC, `DTMonth` DESC;";
		    	$rslt = doSQLQuery( $sqlStr );
		    	if ( is_array($rslt) ) {
			    	foreach ( $rslt as $Key=>$Row ) {
			    		$rVal[ $Items ] = array( "Year"		=> $Row['DTYear'],
					    					     "Month"	=> $Row['DTMonth'],
					    					     "Posts"	=> $Row['PostCount']
					    					     );
					    $Items++;
			    	}
		    	}
		    	break;

		    default:
		    	// Return the Full Listing (All)
	    }
	    
	    // Return the Array
	    return $rVal;
    }
    
    private function _getRawArchives( $Results ) {
	    $rVal = "";
	    $i = 0;

	    $sqlStr = $this->_getAppropriateSQLQuery( '/' );
	    $rslt = doSQLQuery( $sqlStr );
	    if ( is_array($rslt) ) {
		    foreach ( $rslt as $Key=>$Row ) {
		    	if ( $i < $Results ) {
		    		$DateStr = date("F jS, Y", $Row['ENTRY-UNIX']);
		    		$PostURL = str_replace("[HOMEURL]", $this->settings['HomeURL'], $Row['POST-URL']);
			    	$rVal .= "<li><a href=\"$PostURL\" rel=\"bookmark\">" . NoNull($Row['TITLE']) . "</a>" .
							   "<div class=\"postDate\">" . 
							     "<abbr class=\"published\" title=\"" . NoNull($Row['POST-GUID']) . "\">$DateStr</abbr>" .
							   "</div>" .
							 "</li>";
			    	$i++;
		    	}
		    }
	    }

	    // Return the HTML Results
	    return $rVal;
    }
    
    /**
     *	Function Returns an Array of Years for Valid Content (Used in isValidPage() )
     */
    private function _getValidPostYears() {
	    $rVal = array();
	    
	    $sqlStr = "SELECT DISTINCT DATE_FORMAT(c.`CreateDTS`, '%Y') as `Prefix` FROM `Content` c" .
	    		  " WHERE c.`isReplaced` = 'N'" .
	    		  " ORDER BY `Prefix`";
	    $rslt = doSQLQuery( $sqlStr );
	    if ( is_array($rslt) ) {
		    foreach ( $rslt as $Key=>$Row ) {
			    $rVal[] = NoNull($Row['Prefix']);
		    }
	    }

	    // Return the Array
	    return $rVal;
    }

    /**
     *	Function Returns an Array of the X Most Recent Posts. If OnThisDay is TRUE then
     *		only posts written on this day are returned
     */
    private function _getPostList( $PostCount = 15, $OnThisDay = false ) {
    	$SQLotd = ( $OnThisDay ) ? "and c.`PostURL` LIKE CONCAT('%', DATE_FORMAT(Now(), '/%m/%d/'), '%')" : "";
	    $rVal = array();

	    $sqlStr = "SELECT c.`guid`, c.`Title`, c.`PostURL` as `URL` FROM `Content` c" .
	    		  " WHERE c.`isReplaced` = 'N' and c.`CreateDTS` <= Now()" .
	    		  "   and c.`TypeCd` = 'POST' $SQLotd" .
	    		  " ORDER BY c.`CreateDTS` DESC" .
	    		  " LIMIT 0, $PostCount";
	    $rslt = doSQLQuery( $sqlStr );
	    if ( is_array($rslt) ) {
		    foreach ( $rslt as $Key=>$Row ) {
			    $rVal[ $Key ] = array( "TITLE"	=> NoNull($Row['Title']),
			    					   "POST-URL"	=> NoNull($Row['URL']),
			    					   "POST-GUID"	=> NoNull($Row['guid']),
			    					  );
		    }
	    }

		// Return the Array
		return $rVal;
    }

    /**
     * Function Returns a List of Tags and the Number of Posts Associated with each
     *	IF - IncludeAll is True, then Tags with 0 Posts are Returned
     */
    private function _getTagsList( $IncludeAll = false ) {
    	$MaxEntryTS = $this->_getLastContentID();
	    $rVal = false;

	    if ( !$rVal ) {
		    switch ( DB_TYPE ) {
			    case 1:
			    	// MySQL
			    	$sqlStr = "SELECT m.`Value` as `TagName`, count(c.`id`) as `PostCount`" .
			    			  "  FROM `Content` c, `Meta` m" .
			    			  " WHERE m.`ContentID` = c.`id` and c.`isReplaced` = 'N'" .
			    			  "   and c.`TypeCd` = 'POST' and m.`TypeCd` = 'POST-TAG'" .
			    			  "   and c.`CreateDTS` <= Now()" .
			    			  " GROUP BY m.`Value`" .
			    			  " ORDER BY `PostCount` DESC";
				    $rslt = doSQLQuery( $sqlStr );
				    if ( is_array($rslt) ) {
						foreach ( $rslt as $Key=>$Row ) {
							if ( nullInt($Row['PostCount']) > 0 || $IncludeAll ) {
								$rVal[ NoNull($Row['TagName']) ] = nullInt($Row['PostCount']);
							}
						}

						// Record the Last Post's Create TimeStamp with the Recordset
						$rVal['lastCreateTS'] = $MaxEntryTS;
				    }

				    // Save the Data to the Cache
				    //$this->_recordCachedTagsList( $rVal, $MaxEntryTS );
			    	break;

			    case 2:
			    	// ToDo: Write Non-MySQL Retrieval Code
			    	break;

			    default:
			    	// API Retrieval -- We Shouldn't Be Here
		    }
	    }

	    // Return the Tags List
	    return $rVal;
    }

	/**
	 *	Function Determines What Type of Query is Required based on the Request URL and
	 *		well ... returns the proper Query statement.
	 */
	private function _getAppropriateSQLQuery( $ReqURL, $QType = "POSTS", $PageNo = 0, $Results = 25, $doOverride = false ) {
		$doExcerpt = YNBool($this->settings['RSSExcerpt']);
		$Segments = explode('/', $ReqURL);
		$TypeFilter = "POST-URL";
		$PostFilter = sqlScrub($ReqURL);
		$rVal = "";

		switch ( strtolower($Segments[1]) ) {
			case 'archives':
			case 'archive':
				$PageNo = 0;
				$Results = ( $doOverride ) ? ($Results * 2) : 9999;
				$PostFilter = '%';
				break;
			
			case 'search':
				$QType = ($QType == 'RECORDCOUNT') ? $QType : "SEARCH";
				$Results = 25;
				$PostFilter = '%' . sqlScrub($this->settings['s']) . '%';
				break;
			
			case 'tags':
			case 'tag':
				$TypeFilter = "POST-TAG";
				$PostFilter = '%' . sqlScrub($Segments[2]) . '%';
				break;

			default:
				// Return the Post Query
		}
		
		// Is this a Monthly View?
		if ( is_numeric($Segments[1]) && is_numeric($Segments[2]) && NoNull($Segments[3]) == "" ) {
			$QType = 'MONTHLY';
			$DateStr = nullInt($Segments[1]) . '-' . nullInt($Segments[2]);
			$StartDT = date( 'Y-m', strtotime("$DateStr first day") );
			$EndDT = date( 'Y-m-d', strtotime("$DateStr next month last day") );
			$PostFilter = " '$StartDT-01 00:00:00' AND '$EndDT 23:59:59'";
		}

		if ( $PostFilter == "/" ) {
			$QType = 'HOME';
			$doExcerpt = true;
		}
		
		switch ( strtoupper($QType) ) {
			case 'RECORDCOUNT':
	    	case 'TOTALCOUNT':
	    		$rVal = "SELECT count(c.`guid`) as `Records` FROM `Content` c, `Meta` m" .
	    				" WHERE m.`ContentID` = c.`id` and c.`isReplaced` = 'N'" .
	    				"   and c.`TypeCd` = 'POST' and m.`TypeCd` = '$TypeFilter'" .
	    				"   and c.`CreateDTS` <= Now() and c.`Value` LIKE '$PostFilter'";
	    		break;
	    	
	    	case 'WITHGAPS':
	    		$rVal = "SELECT c.`id`, c.`guid`, c.`Title`, c.`PostURL`," .
	    					  " UNIX_TIMESTAMP(c.`CreateDTS`) as `CreateDTS`," .
	    					  " UNIX_TIMESTAMP(c.`UpdateDTS`) as `UpdateDTS`," .
	    					  " LENGTH(c.`Value`) as `PostLength`," .
	    					  " (SELECT count(m.`id`) FROM `Meta` m WHERE c.`id` = m.`ContentID`) as `MetaRecords`" .
	    				"  FROM `Content` c" .
	    				" WHERE c.`TypeCd` = 'POST' and c.`isReplaced` = 'N'" .
	    				" ORDER BY c.`CreateDTS` DESC" .
	    				" LIMIT $PageNo, $Results;";
	    		break;

	    	case 'WITHGAPS-COUNT':
	    		$rVal = "SELECT count(c.`guid`) as `Records` FROM `Content` c" .
	    				" WHERE c.`TypeCd` = 'POST' and c.`isReplaced` = 'N'";
	    		break;

	    	case 'HOME':
	    	case 'RSS':
	    		$ContentStr = "c.`Value`";
	    		if ( $doExcerpt ) {
		    		$ContentStr = "substr(c.`Value`, locate('<p>', c.`Value`), locate('</p>', c.`Value`) + 3)";
	    		}
	    		$rVal = "SELECT c.`id` as `POST-ID`, c.`guid` as `POST-GUID`, c.`Title` as `TITLE`, c.`PostURL` as `POST-URL`, " . 
	    					  " c.`PostAuthor` as `POST-AUTHOR`," .
	    					  " UNIX_TIMESTAMP(c.`EntryDTS`) as `ENTRY-UNIX`, UNIX_TIMESTAMP(c.`CreateDTS`) as `DATE-UNIX`," .
	    					  " UNIX_TIMESTAMP(c.`UpdateDTS`) as `UPDATE-UNIX`, $ContentStr as `CONTENT`" .
	    				"  FROM `Content` c" .
	    				" WHERE c.`isReplaced` = 'N' and c.`TypeCd` = 'POST'" .
	    				"   and c.`CreateDTS` <= Now()" .
	    				" ORDER BY c.`CreateDTS` DESC" .
	    				" LIMIT $PageNo, $Results";
	    		break;

	    	case 'MONTHLY':
	    		$rVal = "SELECT c.`id` as `POST-ID`, c.`guid` as `POST-GUID`, c.`Title` as `TITLE`, c.`TypeCd` as `TYPE-CODE`," .
		    				  " UNIX_TIMESTAMP(c.`EntryDTS`) as `ENTRY-UNIX`," .
		    				  " UNIX_TIMESTAMP(c.`CreateDTS`) as `DATE-UNIX`," .
		    				  " UNIX_TIMESTAMP(c.`UpdateDTS`) as `UPDATE-UNIX`," .
		    				  " CASE c.`TypeCd` WHEN 'TWEET' THEN c.`Value`" .
		    				  				  " ELSE substr(c.`Value`, locate('<p>', c.`Value`), locate('</p>', c.`Value`) + 3) END as `CONTENT`," .
		    				  " c.`PostURL`, c.`PostAuthor`, c.`CreateDTS`, c.`UpdateDTS`, tmp.`Results`" .
	    				"  FROM `Content` c," .
	    					  " (SELECT count(t.`id`) as `Results` FROM `Content` t" .
	    					  "   WHERE t.`isReplaced` = 'N' and t.`TypeCd` = 'POST' and t.`CreateDTS` BETWEEN $PostFilter) tmp" .
	    				" WHERE c.`isReplaced` = 'N' and c.`TypeCd` = 'POST' and c.`CreateDTS` BETWEEN $PostFilter" .
	    				" ORDER BY c.`CreateDTS`" .
	    				" LIMIT $PageNo, $Results";
	    		break;

	    	case 'SEARCH':
	    		$rVal = "SELECT c.`id` as `POST-ID`, c.`guid` as `POST-GUID`, c.`Title` as `TITLE`, c.`TypeCd` as `TYPE-CODE`," .
	    					  " UNIX_TIMESTAMP(c.`EntryDTS`) as `ENTRY-UNIX`, UNIX_TIMESTAMP(c.`CreateDTS`) as `DATE-UNIX`," .
	    					  " UNIX_TIMESTAMP(c.`UpdateDTS`) as `UPDATE-UNIX`," .
	    					  " CASE c.`TypeCd` WHEN 'TWEET' THEN c.`Value`" .
	    					  				  " ELSE substr(c.`Value`, locate('<p>', c.`Value`), locate('</p>', c.`Value`) + 3) END as `CONTENT`," .
	    					  " c.`PostURL`, c.`PostAuthor`, c.`CreateDTS`, c.`UpdateDTS`, tmp.`Results`" .
	    				"  FROM `Content` c," .
	    				"	    (SELECT count(n.`id`) as `Results` FROM `Content` n" .
	    						" WHERE n.`isReplaced` = 'N' and n.`CreateDTS` <= Now()" .
	    						"   and MATCH (n.`Title`, n.`Value`, n.`PostURL`) AGAINST ('$PostFilter')) tmp" .
	    				" WHERE c.`isReplaced` = 'N' and c.`CreateDTS` <= Now()" .
	    				"   and MATCH (c.`Title`, c.`Value`, c.`PostURL`) AGAINST ('$PostFilter')" .
	    				" ORDER BY c.`CreateDTS` DESC" .
	    				" LIMIT $PageNo, $Results";
	    		break;

			default:
		    	$rVal = "SELECT c.`id` as `POST-ID`, c.`guid` as `POST-GUID`, c.`Title` as `TITLE`, c.`PostURL` as `POST-URL`, " .
		    				  " UNIX_TIMESTAMP(c.`EntryDTS`) as `ENTRY-UNIX`, UNIX_TIMESTAMP(c.`CreateDTS`) as `DATE-UNIX`," .
		    				  " UNIX_TIMESTAMP(c.`UpdateDTS`) as `UPDATE-UNIX`, c.`PostAuthor` as `POST-AUTHOR`," .
		    				  " substr(c.`Value`, locate('<p>', c.`Value`), locate('</p>', c.`Value`) + 3) as `CONTENT`" .
		    			" FROM `Content` c" .
		    			" WHERE c.`isReplaced` = 'N' and c.`TypeCd` = 'POST'" .
		    			"   and c.`CreateDTS` <= Now() and c.`PostURL` LIKE '%$PostFilter%'" .
		    			" ORDER BY c.`CreateDTS` DESC" .
		    			" LIMIT $PageNo, $Results;";
		}

		// Return the SQL Query
		return $rVal;
	}

	/**
	 *	Function Returns the Type of Content Result We Have
	 */
	private function _determineContentType( $rslt ) {
		if ( count($rslt) == 1 ) {
			$rVal = 'blog';
		} else {
			$rVal = 'search';
			if ( $this->_isLanding() ) {
				$rVal = 'blog';
			}
			if ( $this->_isArchive() ) {
				$rVal = 'archives';
			}
		}

		// Return the Content Type
		return $rVal;
	}

    /**
     * Function Checks if a URL is Good and Performs the Following Activities:
     *	IF - URL is Good -> Returns Content (Cached or Fresh)
     *		 URL is Incomplete (1 Result) -> Forwarded to Best-Matching URL
     *		 URL is Incomplete (2+ Results) -> Show List of Possible Matches
     */
    private function _getContent( $Results = 25, $doOverride = false ) {
	    $ReqURL = sqlScrub( $this->settings['ReqURI'] );
	    $MaxEntryTS = $this->_getLastContentID();
	    $RecordTotal = $RecordCount = $Records = 1;
	    $Resource = 'content-404.html';
	    $Content = array();
	    $rVal = false;

	    switch ( DB_TYPE ) {
		    case 1:
		    	// MySQL
		    	$PageNo = (nullInt($this->settings['Page'], 1) - 1) * $Results;
		    	$sqlStr = $this->_getAppropriateSQLQuery( $ReqURL, '', $PageNo, $Results, $doOverride );
		    	$rslt = doSQLQuery( $sqlStr );

		    	/*
		    	print_r( "SQL Query: <br />\r\n" );
		    	print_r( $sqlStr );
		    	print_r( "<br />\r\n" );
		    	print_r( "Result: <br />\r\n" );
		    	print_r( $rslt );
		    	die();
		    	*/

		    	if ( is_array($rslt) ) {
		    		if ( count($rslt) == 1 ) {
	    				// One Result Found
	    				$Resource = 'content-blog.html';

				    	// Check to see if the Cached Content needs to be Updated or Not
	    				// -- This is done by ensuring the EntryDTS is Older than the cached
	    				//	  file. As comments are controlled by Disqus, the cache should
	    				//	  always be newer than the EntryDTS value.
				        $Content = $this->_collectCachedContent( NoNull($rslt[0]['guid']), nullInt($rslt[0]['EntryTS']) );
				        if ( !$Content ) {
					        // Collect the Full Content Value
					        $PostIDs = NoNull( $rslt[0]['POST-ID'] );
					        $sqlStr = "SELECT `TypeCd`, `Value` FROM `Content`" .
					        		  " WHERE $PostIDs IN (`id`, `ParentID`);";
					        $meta = doSQLQuery( $sqlStr );
				    		if ( is_array($meta) ) {
				    			foreach( $meta as $Key=>$Row ) {
					    			switch ( $Row['TypeCd'] ) {
						    			case 'POST-FOOTER':
						    				$rslt[0]['POST-FOOTER'] = NoNull($Row['Value']);
						    				break;

						    			case 'POST':
						    				$rslt[0]['CONTENT'] = $this->_cleanContent( $Row['Value'] );
						    				break;

						    			default:
						    				// Not Sure What to Do Here
					    			}
				    			}
				    		}

				    		// Enter in the Default Post Author if One Does Not Exist
				    		if ( $rslt[0]['POST-AUTHOR'] == "" ) {
					    		$rslt[0]['POST-AUTHOR'] = NoNull($this->settings['DEFAULT-POST-AUTHOR']);
				    		}

				    		// Collect the PostMeta
				    		$PostMeta = array();
				    		$sqlStr = "SELECT m.`id`, m.`ContentID`, m.`TypeCd`, m.`Value` FROM `Meta` m" .
				    				  " WHERE m.`TypeCd` IN ('POST-TAG', 'POST-GPS-LAT', 'POST-GPS-LNG')" .
				    				  "   and m.`ContentID` IN ($PostIDs)" .
				    				  " ORDER BY m.`ContentID`, m.`TypeCd`, m.`Value`";
				    		$meta = doSQLQuery( $sqlStr );
				    		if ( is_array($meta) ) {
					    		foreach ( $meta as $Key=>$Row ) {
						    		$PostMeta[ nullInt( $Key ) ] = array( "ContentID"	=> nullInt( $Row['ContentID'] ),
						    											  "TypeCd"		=> NoNull( $Row['TypeCd'] ),
						    											  "Value"		=> NoNull( $Row['Value'] )
						    											 );
					    		}
				    		}
	
				    		// Construct the Search Result for the Theme
				    		$rVal[ $Records ] = $this->_buildMultiReturnArray( $rslt[0], $PostMeta );
				        }

		    		} else {
			    		// Multiple Results Found (Home Page or Search Page)
			    		// This is really inelegant
			    		$Resource = 'content-search.html';
			    		if ( $this->_isLanding() ) {
				    		$Resource = 'content-blog.html';
			    		}
			    		if ( $this->_isArchive() ) {
				    		$Resource = 'content-archives.html';
				    		$rBody = $rMon = "";

				    		// Construct the Content <ul>
				    		foreach ( $rslt as $Key=>$Row ) {
				    			if ( $doOverride ) {
						    		$DateStr = date("F jS, Y", $Row['DATE-UNIX']);
							    	$rBody .= "<li><a href=\"" . $Row['POST-URL'] . "\" rel=\"bookmark\">" . NoNull($Row['TITLE']) . "</a>" .
											    "<div class=\"postDate\">" . 
											      "<abbr class=\"published\" title=\"" . NoNull($Row['POST-GUID']) . "\">$DateStr</abbr>" .
											    "</div>" .
											  "</li>";

				    			} else {
					    			$timestamp = nullInt($Row['DATE-UNIX']);
					    			if ( $rMon != "[lblMonth" . date('m', $timestamp) . "] " . date('Y', $timestamp) ) {
						    			$rMon = "[lblMonth" . date('m', $timestamp) . "] " . date('Y', $timestamp);
						    			if ( $rBody != "" ) { $rBody .= "</li></ul>\r\n"; }
						    			$rBody .= '<li><span class="[ARCHIVE-CLASS-YEAR-MONTH]">' . $rMon . '</span>' . "\r\n" .
						    					  '<ul class="[ARCHIVE-CLASS-MONTH]">' . "\r\n";
					    			}
						    		$rBody .= '<li>' . date('d', $timestamp) . ': <a href="' . $Row['POST-URL'] . '" title="' . $Row['TITLE'] . '">' . $Row['TITLE'] . '</a></li>' . "\r\n";
				    			}
				    		}
				    		// Close off the Non-Overridden (Default) List
				    		if ( !$doOverride ) {
					    		if ( $rBody != "" ) { $rBody .= "</li></ul>\r\n"; }					    		
				    		}

				    		// Write the Content to the Return Array
				    		$rVal[1]['[ARCHIVE-LIST]'] = $rBody;

			    		} else {
				    		$PostIDs = "";
				    		foreach ( $rslt as $Key=>$Row ) {
					    		$PostIDs .= NoNull($Row['POST-ID']) . ", ";
				    		}
				    		$PostIDs .= "0";

				    		// Collect the PostMeta
				    		$PostMeta = array();
				    		$sqlStr = "SELECT m.`id`, m.`ContentID`, m.`TypeCd`, m.`Value` FROM `Meta` m" .
				    				  " WHERE m.`TypeCd` IN ('POST-TAG', 'POST-GPS-LAT', 'POST-GPS-LNG')" .
				    				  "   and m.`ContentID` IN ($PostIDs)" .
				    				  " ORDER BY m.`ContentID`, m.`TypeCd`, m.`Value`";
				    		$meta = doSQLQuery( $sqlStr );
				    		if ( is_array($meta) ) {
					    		foreach ( $meta as $Key=>$Row ) {
						    		$PostMeta[ nullInt( $Key ) ] = array( "ContentID"	=> nullInt( $Row['ContentID'] ),
						    											  "TypeCd"		=> NoNull( $Row['TypeCd'] ),
						    											  "Value"		=> NoNull( $Row['Value'] )
						    											 );
					    		}
				    		}

				    		// Construct the Search Result for the Theme
				    		foreach ( $rslt as $Key=>$Post ) {
					    		$rVal[ $Records ] = $this->_buildMultiReturnArray( $Post, $PostMeta );
					    		$Records++;
				    		}

				    		// Determine the Total Number of Records
				    		if ( is_array($rCnt) ) {
					    		$RecordCount = nullInt( $rslt[0]['Results'] );
				    		}
			    		}
		    		}

		    		// Fill in the Blanks
		    		$rVal['Records'] = count($rVal);
		    		$rVal['RecordTotal'] = $RecordCount;
		    		$rVal['RecordCount'] = $RecordCount;
		    		$rVal['Resource'] = $Resource;

		    		// If there Are 0 Results (rslt is NOT an array) Then 404
		    	}
		    	break;
		    
		    case 2:
		    	// ToDo: Write Non-MySQL Retrieval Code
		    	break;
		    
		    default:
		    	// API Retrieval -- We Shouldn't Be Here
	    }

        // Return the Data
        return $rVal;
    }

    /**
     *	Function Ensures all Necessary Replacable Data Exists
     */
    private function _cleanContent( $Content ) {
	    $ReplStr = array('[MEDIA_URL]'	=> $this->settings['HomeURL'] . '/content/default',
	    				 '[HOMEURL]'	=> $this->settings['HomeURL'],
	    				 );
	    $rVal = $Content;

	    // Replace the Values
        if ( count($ReplStr) > 0 ) {
            $Search = array_keys( $ReplStr );
            $Replace = array_values( $ReplStr );

            // Perform the Search/Replace Actions
            $rVal = str_replace( $Search, $Replace, $Content );
        }

        // Return the Cleaned Content
        return $rVal;
    }

    /**
     * Function returns a Boolean Response stating whether we are on the landing page
     *		or not.
     */
    private function _isLanding() {
    	$ReqURL = str_replace("/", "", $this->settings['ReqURI'] );
	    $rVal = false;

	    // If the Request URL is Blank or Numberic "201212" Return True
	    if ( $ReqURL == "" || is_numeric($ReqURL) ) { $rVal = true; }
	    
	    // If Were Looking at Tags Return True
	    if ( strpos("  " . $this->settings['ReqURI'], '/tags/') > 0 ) { $rVal = true; }

	    // Return the Boolean Response
	    return $rVal;
    }

    private function _isArchive() {
    	$ReqURL = str_replace("/", "", $this->settings['ReqURI'] );
	    $rVal = false;

	    // If Were Looking at Archives Return True
	    if ( strpos("  " . $this->settings['ReqURI'], '/archives/') > 0 ) { $rVal = true; }

	    // Return the Boolean Response
	    return $rVal;
    }

    private function _getBasicPostArray( $guid = "" ) {
	    $rVal = array('[HOMEURL]'		=> $this->settings['HomeURL'],
                      '[SITEURL]'		=> $this->settings['URL'],
	    			  '[POST-URL]'		=> '',
	    			  '[POST-ID]'		=> '',
	    			  '[POST-GUID]'		=> $guid,
	    			  
	    			  '[TITLE]'			=> '',
	    			  '[DATE-UTC]'		=> '',
	    			  '[DATE-STR]'		=> '',
	    			  '[DATE-UNIX]'		=> '',
	    			  '[DATE-TMSTR]'	=> '',
	    			  '[UPDATE-UTC]'	=> '',
	    			  '[UPDATE-STR]'	=> '',
	    			  '[UPDATE-UNIX]'	=> '',
	    			  '[UPDATE-TMSTR]'	=> '',
	    			  '[POST-AUTHOR]'	=> '',
	    			  '[CONTENT]'		=> '',
	    			  '[COMMENTS]'		=> '',
	    			  '[POST-FOOTER]'	=> '',
	    			  '[POST-TAG]'		=> '',
	    			  '[POST-GEO]'		=> '',

	    			  '[POST-SOCIAL]'	=> '',
	    			  '[TWEET-NAME]'	=> '',

	    			  '[DIV-CLASS]'		=> '',

					  '[APPINFO]'		=> APP_NAME . " | " . APP_VER,
					  '[APP_VER]'		=> APP_VER,
					  '[GENERATOR]'		=> GENERATOR,
	    			  );

	    // Return the Basic Post Array
	    return $rVal;
    }
    
    private function _buildMultiReturnArray( $PostContent, $PostMeta ) {
	    $rVal = $this->_getBasicPostArray( $guid );

	    // Record the PostContent to the Array
	    foreach ( $PostContent as $Key=>$Val ) {
	    	switch ( $Key ) {
	    		case 'CONTENT':
	    			$rVal['[CONTENT]'] = $this->_cleanContent( $PostContent['CONTENT'] );
	    			break;

		    	case 'POST-URL':
		    		$rVal[ "[$Key]" ] = str_replace("[HOMEURL]", $this->settings['HomeURL'], $Val);
		    		break;
		    	
		    	case 'DATE-UNIX':
					$rVal['[DATE-UTC]'] = date("c", $PostContent['DATE-UNIX'] );
					$rVal['[DATE-STR]'] = date("F j, Y", $PostContent['DATE-UNIX'] );
					$rVal['[DATE-TMSTR]'] = date("g:i A", $PostContent['DATE-UNIX']);
		    		$rVal[ "[$Key]" ] = NoNull( $Val );
					break;

				case 'UPDATE-UNIX':
					$rVal['[UPDATE-UTC]'] = date("c", $PostContent['UPDATE-UNIX'] );
					$rVal['[UPDATE-STR]'] = date("F j, Y", $PostContent['UPDATE-UNIX'] );
					$rVal['[UPDATE-TMSTR]'] = date("g:i A", $PostContent['UPDATE-UNIX']);
		    		$rVal[ "[$Key]" ] = NoNull( $Val );
					break;

		    	default:
		    		$rVal[ "[$Key]" ] = NoNull( $Val );
		    }
	    }

	    // Add the Meta Information
	    $PostTag = $PostFoot = "";
	    $GeoTag = $GeoLat = $GeoLng = "";
	    foreach ( $PostMeta as $Key=>$Row) {
		    if ( $Row['ContentID'] == $PostContent['POST-ID'] ) {
		    	switch ( $Row['TypeCd'] ) {
			    	case 'POST-TAG':
			    		if ( $PostTag ) { $PostTag .= ", "; }
					    $PostTag .= '<a href="' . $this->settings['HomeURL'] . '/tags/' . urlencode( strtolower($Row['Value']) ) . '/" title="' . $Row['Value'] . '">' .
					    			$Row['Value'] . 
					    			'</a>';
					    break;

					case 'POST-GPS-LAT':
						$GeoLat = $Row['Value'];
						break;

					case 'POST-GPS-LNG':
						$GeoLng = $Row['Value'];
						break;

					default:
						// Do Nothing -- Unhandled TypeCd
		    	}
		    }
	    }

	    // Construct the GeoTag (If Necessary)
	    if ( $GeoLat != "" && $GeoLng != "" ) {
		    $GeoTag = $GeoLat . ',' . $GeoLng;
	    }

	    // Save the Information
	    $rVal['[POST-TAG]'] = NoNull($PostTag);
	    $rVal['[POST-GEO]'] = NoNull($GeoTag);

	    // Return the Array of Data
	    return $rVal;
    }

    /**
     * Function Returns an Array of Content Data in the Appropriate Format for the Front-End
     */
    private function _collectPostContent( $guid ) {
    	$rVal = $this->_getBasicPostArray( $guid );

	    // Collect the Information from the Database
	    $sqlStr = "SELECT c.`id`, c.`guid`, c.`Title`, c.`Value` as `PostContent`, m.`Value` as `PostURL`," .
	    				" (SELECT f.`Value` FROM `Meta` f WHERE f.`TypeCd` = 'POST-FOOTER' and f.`ContentID` = c.`id`) as `PostFooter`," .
	    				" UNIX_TIMESTAMP(c.`CreateDTS`) as `CreateTS`," .
	    				" UNIX_TIMESTAMP(c.`UpdateDTS`) as `UpdateTS`," .
	    				" UNIX_TIMESTAMP(c.`EntryDTS`) as `EntryTS`" .
	    		  "  FROM `Content` c, `Meta` m" .
	    		  " WHERE m.`ContentID` = c.`id` and c.`isReplaced` = 'N'" .
	    		  "   and m.`TypeCd` = 'POST-URL' and c.`guid` = '$guid'";
	    $rslt = doSQLQuery( $sqlStr );
	    if ( is_array($rslt) ) {
			foreach ( $rslt as $Key=>$Row ) {
				$rVal['TITLE'] = NoNull( $Row['Title'] );
				$rVal['POST-ID'] = NoNull( $Row['id'] );
				$rVal['POST-URL'] = NoNull( $Row['PostURL'] );
				$rVal['POST-GUID'] = NoNull( $Row['guid'] );
				$rVal['CONTENT'] = NoNull( $Row['PostContent'] );
				$rVal['POST-FOOTER'] = NoNull( $Row['PostFooter'] );

				$rVal['DATE-UTC'] = date("Y-m-d", $Row['CreateTS'] ) . "T" . date("G:i", $Row['CreateTS'] ) . "+9:00";
				$rVal['DATE-STR'] = date("F j, Y", $Row['CreateTS'] );
				$rVal['DATE-TMSTR'] = date("g:i A", $Row['CreateTS']);
				$rVal['DATE-UNIX'] = nullInt($Row['CreateTS']);
				$rVal['UPDATE-UTC'] = date("Y-m-d", $Row['UpdateTS'] ) . "T" . date("G:i", $Row['UpdateTS'] ) . "+9:00";
				$rVal['UPDATE-STR'] = date("F j, Y", $Row['UpdateTS'] );
				$rVal['UPDATE-TMSTR'] = date("g:i A", $Row['UpdateTS']);
				$rVal['UPDATE-UNIX'] = nullInt($Row['UpdateTS']);
			}
	    }

	    // Return the Array of Content (Even if Empty)
	    return $rVal;
    }

    /**
     *	Function Retrieves the Cached Content from storage
     */
    private function _collectCachedContent( $guid, $PostAge ) {
    	$CacheFile = $this->settings['ContentDIR'] . "/cache/$guid.static";
	    $rVal = false;

	    if ( file_exists($CacheFile) ) {
	    	$FileAge = filemtime($CacheFile);
	    	if ( $FileAge <= $PostAge || $PostAge <= 0 ) {
		    	$data = utf8_decode( file_get_contents($CacheFile) );
		    	$rVal = unserialize( $data );
			}
        }
        
        // Return the Data (if Applicable)
        return $rVal;
    }
    
    /**
     *	Function Records the Cached Content to Storage
     */
    private function _recordCachedContent( $data ) {
    	$guid = $data['guid'];
    	if ( $guid == "" ) { return false; }
	    $CacheFile = $this->settings['ContentDIR'] . "/cache/$guid.static";
	    $rVal = false;

	    // Check to see if the Settings File Exists or Not
	    if ( checkDIRExists( $this->settings['ContentDIR'] . "/cache" ) ) {
		    $Content = unserialize($data);

		    // Write the File to the Cache Folder
		    $fh = fopen($CacheFile, 'w');
		    fwrite($fh, serialize($Content));
		    fclose($fh);

		    // Set the Happy Return Boolean
		    return $rVal;
	    }
	    
	    // Return the Boolean Response
	    return $rVal;
    }

    /**
     * Function Returns the Last Content.id Value for TypeCd = 'POST'
     *	- This is used mainly to ensure the cache is sufficiently up to date
     */
    private function _getLastContentID( $isTweet = false ) {
	    $rVal = 0;

	    // If We Don't Already Have the LastIDs, Go Get Them
	    if ( !array_key_exists('LastPostID', $this->settings) ) {
		    switch ( DB_TYPE ) {
			    case 1:
			    	// MySQL
			    	// Format: First 6 Digits Represent the number of posts in the database
			    	//		   Latter Digits Represent the Highest Unix Timestamp of Published Posts
			    	// Note:   Tweets are Refreshed every minute for relative counter consistency
			    	$sqlStr = "SELECT `TypeCd`, CONCAT(RIGHT(CONCAT('000000', count(`guid`)), 6), " .
			    									  "CASE `TypeCd` WHEN 'TWEET' THEN UNIX_TIMESTAMP(DATE_FORMAT(NOW(), '%Y-%m-%d %h:%i')) " .
			    									  "ELSE UNIX_TIMESTAMP(max(`CreateDTS`)) END) as `LastID`" .
			    			  "  FROM `Content`" .
			    			  " WHERE `isReplaced` = 'N' and `CreateDTS` <= Now() and `TypeCd` NOT IN ('POST-FOOTER')" .
			    			  " GROUP BY `TypeCd`";
			    	$rslt = doSQLQuery( $sqlStr );
				    if ( is_array($rslt) ) {
						foreach ( $rslt as $Key=>$Row ) {
							switch ( NoNull($Row['TypeCd']) ) {
								case 'TWEET':
									$this->settings['LastTweetID'] = NoNull( $Row['LastID'] );
									break;
								
								case 'POST':
									$this->settings['LastPostID'] = NoNull( $Row['LastID'] );
									break;
								
								default:
									// We Shouldn't Be Here
							}
						}
				    }
				    break;

			    case 2:
			    	// Local Storage
			    	$rVal = NoNull( readSetting('core', 'maxCreateTS') );
			    	break;

			    default:
			    	// API Retrieval -- We Shouldn't Be Here
		    }
	    }

	    // Get the Appropriate Content ID
	    if ( $isTweet ) {
		    $rVal = $this->settings['LastTweetID'];
	    } else {
		    $rVal = $this->settings['LastPostID'];
	    }

	    // Return the ID
	    return $rVal;
    }

    /***********************************************************************
     *  RSS Functions
     ***********************************************************************/
    private function _getRSS() {
	    $Template = $this->_buildRSSTemplate();
	    $ReplStr = array('[MEDIA_URL]'	 => $this->settings['HomeURL'] . '/content/default',
	    				 '[RSSID]'		 => getRSSIDString( nullInt($this->settings['SiteID']) ),
	    				 '[ENTRIES]'	 => "",
	    				 '[APPNAME]'	 => APP_NAME,
	    				 '[APPVER]'		 => APP_VER,
	    				 '[LAST-UPDATE]' => date("c"),
	    				 );
	    $LastUpdate = 0;
	    $rVal = "";

	    // Copy the Strings to the Replacement Array
	    foreach( $this->messages as $Key=>$Val ) {
		    $ReplStr['[' . strtoupper($Key) . ']'] = $Val;
	    }
	    foreach( $this->settings as $Key=>$Val ) {
		    $ReplStr['[' . strtoupper($Key) . ']'] = $Val;
	    }
	    $ReplStr['[COPYRIGHT]'] .= " " . date("Y");

	    $sqlStr = $this->_getAppropriateSQLQuery( '', 'RSS' );
	    $data = doSQLQuery( $sqlStr );
	    if ( is_array($data) ) {
	    	// Collect the PostIDs and Read In any Footnotes that May exist
	    	$PostIDs = "0";
	    	foreach( $data as $Key=>$Post ) {
		    	$PostIDs .= ", " . $Post['POST-ID'];
	    	}

	    	// If We Have a List of PostIDs (Not Including 0), Collect the Footers (If Available)
	    	if ( strlen($PostIDs) > 1 ) {
		    	$sqlStr = "SELECT `ParentID`, `Value` FROM `Content`" .
		    			  " WHERE `isReplaced` = 'N' and `TypeCd` = 'POST-FOOTER'" .
		    			  "   and `ParentID` IN ($PostIDs)" .
		    			  " ORDER BY `CreateDTS` DESC";
		    	$meta = doSQLQuery( $sqlStr );
		    	if ( is_array($meta) ) {
		    		foreach ( $data as $Key=>$Post ) {
		    			$Post['POST-FOOTER'] = "";
				    	foreach ( $meta as $K=>$Row ) {
					    	// Check for a Matched ID
					    	if ( intval($Post['POST-ID']) == intval($Row['ParentID']) ) {
						    	$data[$Key]['POST-FOOTER'] = NoNull($Row['Value']);
						    	break;
					    	}
				    	}
		    		}
		    	}

		    	// Release the Array
		    	unset($meta);
	    	}

	    	// Construct the RSS Entries
		    foreach( $data as $Key=>$Post ) {
		    	if ( $LastUpdate < intval($Post['UPDATE-UNIX']) ) { $LastUpdate = intval($Post['UPDATE-UNIX']); }
			    $ReplStr['[ENTRIES]'] .= $this->_buildRSSEntry( $Post );
		    }
	    }
	    $ReplStr['[LAST-UPDATE]'] = date("c", $LastUpdate);

	    // Replace the Values
        if ( count($ReplStr) > 0 ) {
            $Search = array_keys( $ReplStr );
            $Replace = array_values( $ReplStr );

            // Perform the Search/Replace Actions
            $rVal = str_replace( $Search, $Replace, $Template );
        }

	    // Return the RSS Feed
	    return $rVal;
    }

    /**
     *	
     */
    private function _buildRSSTemplate() {
	    $rVal = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n" .
	    		"<feed xmlns=\"http://www.w3.org/2005/Atom\" xmlns:georss=\"http://www.georss.org/georss\">\r\n" .
	    		tabSpace(1) . "<title type=\"text\">[SITENAME]</title>\r\n" .
	    		tabSpace(1) . "<subtitle type=\"html\">[SITEDESCR]</subtitle>\r\n" .
	    		tabSpace(1) . "<updated>[LAST-UPDATE]</updated>\r\n" .
	    		tabSpace(1) . "<id>[RSSID]</id>\r\n" .
	    		tabSpace(1) . "<link rel=\"alternate\" type=\"text/html\" hreflang=\"[DISPLANG]\" href=\"[HOMEURL]\"/>\r\n" .
	    		tabSpace(1) . "<link rel=\"self\" type=\"application/atom+xml\" href=\"[HOMEURL]/atom/\"/>\r\n" .
	    		tabSpace(1) . "<rights>[COPYRIGHT]</rights>\r\n" .
	    		tabSpace(1) . "<generator uri=\"[HOMEURL]\" version=\"[APPVER]\">[APPNAME]</generator>\r\n" .
	    					  "[ENTRIES]" .
	    		"</feed>";

	    // Return the RSS Prefix
	    return $rVal;
    }
    
    private function _buildRSSEntry( $PostData ) {
	    $rssCopyright = NoNull(readSetting('Site_' . $this->settings['SiteID'], 'RSSCopyright'), $this->messages['rss_copyright']);
	    if ( $rssCopyright != "" ) {
		    // Add the Site Name and Link Information
		    $rssCopyright .= " <a href=\"" . $this->settings['HomeURL'] . "\" title=\"" . $this->settings['SiteName'] . "\">" .
		    				 $this->settings['SiteName'] . "</a>";
	    }
	    $rVal = "";

	    if ( is_array($PostData) ) {
	    	$DispLang = NoNull($this->settings['DispLang'], 'EN');
	    	$UpdateDT = date("c", round(intval($PostData['UPDATE-UNIX']), -1));
	    	$PublshDT = date("c", round(intval($PostData['DATE-UNIX']), -1));
	    	$PostURL = str_replace("[HOMEURL]", $this->settings['HomeURL'], $PostData['POST-URL']);
	    	$Content = $this->_cleanContent($PostData['CONTENT']);
	    	if ( NoNull($PostData['POST-FOOTER']) != "" ) {
		    	$Content .= "<hr />" . NoNull($PostData['POST-FOOTER']);
	    	}
	    	if ( $rssCopyright != "" ) {
		    	$Content .= "<hr />" . $rssCopyright;
	    	}
		    $rVal = tabSpace(1) . "<entry>\r\n" .
		    		tabSpace(2) . "<title>" . NoNull($PostData['TITLE']) . "</title>\r\n" .
		    		tabSpace(2) . "<link href=\"$PostURL\"/>\r\n" .
		    		tabSpace(2) . "<id>urn:uuid:" . NoNull($PostData['POST-GUID']) . "</id>\r\n" .
		    		tabSpace(2) . "<updated>$UpdateDT</updated>\r\n" .
		    		tabSpace(2) . "<published>$PublshDT</published>\r\n" .
		    		tabSpace(2) . "<author>\r\n" .
		    		tabSpace(3) . "<name>" . NoNull($PostData['POST-AUTHOR']) . "</name>\r\n" .
		    		tabSpace(3) . "<uri>" . $this->settings['HomeURL'] . "/</uri>\r\n" .
		    		tabSpace(2) . "</author>\r\n" .
		    		tabSpace(2) . "<content type=\"xhtml\" xml:lang=\"$DispLang\" xml:base=\"" . $this->settings['HomeURL'] . "/\">\r\n" .
		    		tabSpace(3) . "<div xmlns=\"http://www.w3.org/1999/xhtml\">\r\n" .
		    		tabSpace(4) . "$Content\r\n" .
		    		tabSpace(3) . "</div>\r\n" .
		    		tabSpace(2) . "</content>\r\n" .
		    		tabSpace(1) . "</entry>\r\n";
	    }

	    // Return the Entry
	    return $rVal;
    }

    /***********************************************************************
     *  Caching Functions
     ***********************************************************************/
    private function _buildCacheFileName( $FileName ) {
	    $rVal = $FileName . '.cache';

	    // Return the Cache FileName
	    return str_replace('_.', '.', $rVal);
    }
     /**
      *	Function Reads the Cached HTML Data and Returns It if LastContentID
      *		matches the previous value.
      *
      */
     private function _readCachedHTML( $FileName, $UseCurrentID = false ) {
	     $rVal = false;
     	
		// Do Not Save Search Results
		if ( $this->settings['mpage'] != "search" ) {
			$LastContentID = ( $UseCurrentID ) ? $this->_getLastContentID( true ) : $this->settings['LastContentID'];
			$CacheDIR = $this->settings['ContentDIR'] . "/cache";
			$CacheFile = $CacheDIR . '/' . $this->_buildCacheFileName( $FileName );

			if ( file_exists($CacheFile) ) {
				$GLOBALS['Perf']['caches']++;
				$Raw = file_get_contents( $CacheFile, "r");
				$data = unserialize( $Raw );

				if ( intval($LastContentID) == intval($data['LastContentID']) ) {
					$rVal = $data['HTML'];
				}
			}
		}

		// Return the HTML (if Applicable)
		return $rVal;
     }

     private function _saveCachedHTML( $FileName, $HTML, $UseCurrentID = false ) {
	     $rVal = false;

	     if ( $this->settings['mpage'] != "search" ) {
	     	 $LastContentID = ( $UseCurrentID ) ? $this->_getLastContentID( true ) : $this->settings['LastContentID'];
	     	 $CacheDIR = $this->settings['ContentDIR'] . "/cache";
		     $CacheFile = $CacheDIR . '/' . $this->_buildCacheFileName( $FileName );
		     $data = array( "LastContentID" => $LastContentID,
		     				"HTML"			=> $HTML
		     			   );

		     // Check to see if the HTML Folder Exists or Not, and Write the Cache
		     if ( checkDIRExists( $CacheDIR ) ) {
			     // Write the File to the Cache Folder
			     $fh = fopen($CacheFile, 'w');
			     fwrite($fh, serialize($data));
			     fclose($fh);
	
			     // Set the Happy Return Boolean
			     $rVal = true;
			 }
	     }

		 // Return the Boolean Response
		 return $rVal;
     }

}

?>