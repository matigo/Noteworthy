<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * This is the Central Functions File that will be used throughout the
 *  Noteworthy Application, including Themes and Plugins
 */
require_once(LIB_DIR . '/globals.php');

    /**
     *	Function returns the Site Details based on the SiteID Requested
     */
    function getSiteDetails( $SiteID = 0 ) {
    	$RootURL = str_replace('/index.php', '', $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']);
    	$SiteID = nullInt( $SiteID );
    	$CacheToken = "Site_$SiteID";
    	$APIKey = readSetting( $CacheToken, 'api_key' );
    	$SiteURL = NoNull(readSetting( $CacheToken, 'HomeURL' ), $RootURL);
    	$doSave = false;
    	if ( $APIKey == "" ) {
    		$APIKey = getRandomString( 32, true);
    		$doSave = true;
    	}

    	// Construct the Return Array
        $rVal = array('URL'             => $SiteURL,
		              'HomeURL'         => 'http://' . $SiteURL,
		
		              'api_url'         => 'http://' . $SiteURL . '/api/',
		              'api_port'        => 80,
		              'api_key'         => $APIKey,
		              'require_key'		=> 'Y',

		              'ContentDIR'      => BASE_DIR . '/content/default',
		              'AkismetKey'		=> '',

		              'SiteID'			=> $SiteID,
		              'SiteName'		=> 'Ambling Down the Path',
		              'SiteDescr'		=> 'A Quick &amp; Dirty Noteworthy-Powered Website',
		              'SiteSEOTags'		=> 'Noteworthy',

		              'Location'        => 'manifest',
		              'isDefault'       => 'Y',

		              'doComments'		=> 'N',
		              'DisqusID'     	=> '',

		              'EN_ENABLED'		=> 'N',
		              'EN_SANDBOX'		=> 'N',
		              'EN_TOKEN_EXPY'	=> 0,
		              );
		// Read In the Social Defaults
		$Social = getSocialDefaults();
		foreach( $Social as $Key=>$Val ) {
			$rVal[ $Key ] = $Val;
		}

		// Fill In the Site-Specific Data
		$Details = readSetting( $CacheToken, "*" );
		foreach( $Details as $Key=>$Val ) {
			$rVal[ $Key ] = $Val;
		}
		
		// If this is the First Access for the SiteID, Save The Defaults
		if ( $doSave ) {
			foreach( $rVal as $Key=>$Val ) {
				saveSetting( $CacheToken, $Key, $Val );
			}
		}

        // Return the Array of Site Details
        return $rVal;
    }
    
    function getSocialDefaults() {
		$Sites = array( 'Twitter'	=> 'http://twitter.com/',
	    				'Facebook'	=> 'http://facebook.com/',
	    				'App.Net'	=> 'http://alpha.app.net/',
	    				'YouTube'	=> 'http://youtube.com/',
	    				'Last.fm'	=> 'http://lastfm.com/',
	    				'Vimeo'		=> 'http://vimeo.com/',
		               );
		$rVal = array();
		$idx = 1;

		// Construct the Array
		foreach ( $Sites as $Name=>$Link ) {
    		$Suffix = str_pad((int) $idx, 2, "0", STR_PAD_LEFT);
        	$rVal[ "SocName$Suffix" ] = $Name;
        	$rVal[ "SocLink$Suffix" ] = $Link;
        	$rVal[ "SocShow$Suffix" ] = "Y";
        	$idx++;
		}

		// Return the Array
		return $rVal;
    }

    /**
     *	Function Returns an Array of Themes in the Theme Directory
     */
    function getThemeList() {
    	$Excludes = array( 'admin', 'resource', 'themes.php', '.DS_Store');
	    $rVal = array();

		if ($handle = opendir( THEME_DIR )) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					if ( !in_array($entry, $Excludes) ) {
						$rVal[ $entry ] = readThemeName( $entry );
					}
				}
			}
			closedir($handle);
		}

	    // Return the List of Themes
	    return $rVal;
    }

    /**
     *	Function Returns a Theme Name based on the Values in style.css
     */
    function readThemeName( $ThemeDIR ) {
    	$CSSFile = THEME_DIR . "/$ThemeDIR/css/style.css";
    	$rVal = $ThemeDIR;

    	if ( file_exists($CSSFile) ) {
	    	$lines = file($CSSFile);

	    	foreach ( $lines as $line ) {
		    	$row = split(":", $line);
		    	if ( $row[0] == "Theme Name" ) {
			    	$rVal = NoNull($row[1]);
			    	return $rVal;
		    	}
	    	}
    	} 

    	// Return the Theme Name
    	return $rVal;
    }
    
    /**
     *	Function Sets the Defaults Used Within the Theme
     */
    function prepThemeLocations( $HomeURL, $ThemeDIR ) {
    	$ThemeSplit = explode('/', $ThemeDIR);
    	$ThemeLoc = $ThemeSplit[ count($ThemeSplit) - 1 ];

		define('SITE_DIR', "http://$HomeURL");
		define('HOME_DIR', "$HomeURL/themes/$ThemeLoc");

		define('IMG_DIR',  HOME_DIR . "/img");
		define('CSS_DIR', HOME_DIR . "/css");
		define('JS_DIR', HOME_DIR . "/js");
		define('RES_DIR', "$ThemeDIR/resource" );
    }

    /**
     *	Function Deletes all of the Files (Not Directories) in a Specified Location
     */
    function scrubDIR( $DIR ) {
    	$FileName = "";
    	$Excludes = array( 'rss.cache' );
	    $rVal = false;

		if (is_dir($DIR)) {
			$objects = scandir($DIR);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					$FileName = $DIR . "/" . $object;
					if (filetype($FileName) == "dir") rrmdir($FileName); else unlink($FileName);
				}
			}
			reset($objects);
		}

		// Return a Boolean
		return true;
    }

    /**
     * Function returns a requested Number of spaces
     *  Example: tabSpace(1)
     *  Returns: "  "
     */
    function tabSpace( $tabs ) {
        $rVal = "";
        for ($i = 0; $i <= ($tabs - 1); $i++) {
            $rVal .= "  ";
        }    

        // Return the Spaces
        return $rVal;
    }

    /**
     * Function returns the current MicroTime Value
     */
    function getMicroTime() {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        
        // Return the Time
        return $time;
    }
    
    /**
     * Function Identifies UserNames, HashTags, and URLs before Replacing them with proper URLs
     */
    function parseTweet( $Tweet ) {
        $rVal = $Tweet;
        $TwitsDone = array();

        // Change any URLs into proper-working Links
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        if(preg_match($reg_exUrl, $rVal, $url)) {
            $rVal = preg_replace($reg_exUrl, "<a href=\"{$url[0]}\">{$url[0]}</a>", $rVal);
        }
        
        // Handle HashTags
        $rVal = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $rVal);
        
        // Set the TwitName Links Accordingly
        preg_match_all("/@([A-Za-z0-9_]+)/", strip_tags($rVal), $Twits);
        if ( $Twits ) {
            foreach( $Twits[1] as $Twit ) {
                //$TwitName = str_replace("@", "", $Twit);
                $TwitName = $Twit;
                if ( !in_array($TwitName, $TwitsDone, true) ) {
                    $TwitsDone[] = $TwitName;
                    $URL = "<a href=\"http://twitter.com/$TwitName\" target=\"_blank\">@$TwitName</a>";
                    $rVal = str_replace('@' . $Twit, $URL, $rVal);
                }
            }
        }
        
        // Return the Tweet
        return NoNull($rVal);
    }
    
    /**
     * Function Returns an Excerpt of the Content
     */
    function parseExcerpt( $Content ) {
	    $rVal = $Content;

	    $pattern = "/<p>(.*?)<\/p>/si";
        preg_match($pattern, $Content, $excerpt);
        if ( is_array($excerpt) ) {
	        $rVal = '<p>' . NoNull($excerpt[1]) . '</p>';
        }

        // Return the Exerpt
        return $rVal;
    }

    /**
     *	Function Generates an XML Element
     */
	function generateXML( $tag_in, $value_in = "", $attribute_in = "" ){
		$rVal = "";
		$attributes_out = "";
		if (is_array($attribute_in)){
			if (count($attribute_in) != 0){
				foreach($attribute_in as $k=>$v) {
					$attributes_out .= " ".$k."=\"".$v."\"";
				}
			}
		}
		
		// Return the XML Tag
		return "<".$tag_in."".$attributes_out.((trim($value_in) == "") ? "/>" : ">".$value_in."</".$tag_in.">" );
	}

    /**
     *
     */
	function arrayToXML( $array_in ) {
		$rVal = "";
		$attributes = array();

		foreach($array_in as $k=>$v) {
			if ($k[0] == "@"){
				// attribute...
				$attributes[str_replace("@","",$k)] = $v;
			} else {
				if (is_array($v)){
					$rVal .= generateXML($k,arrayToXML($v),$attributes);
					$attributes = array();
				} else if (is_bool($v)) {
					$rVal .= generateXML($k,(($v==true)? "true" : "false"),$attributes);
					$attributes = array();
				} else {
					$rVal .= generateXML($k,$v,$attributes);
					$attributes = array();
				}
			}
		}

		// Return the XML
		return $rVal;
	}   

    /**
     * Function Returns the Amount of Time that has passed since $UnixTime
     */
    function getTimeSince( $UnixTime ) {
        $rVal = "";

        if ( $UnixTime > 0 ) {
            $time = time() - $UnixTime;

            $tokens = array (
                31536000 => 'year',
                2592000 => 'month',
                604800 => 'week',
                86400 => 'day',
                3600 => 'hour',
                60 => 'minute',
                1 => 'second'
            );

            foreach ($tokens as $unit => $text) {
                if ($time < $unit) continue;
                $numberOfUnits = floor($time / $unit);
                return $numberOfUnits . ' ' . $text . ( ($numberOfUnits > 1) ? 's' : '' );
            }
        }

        // Return the Appropriate Time String
        return $rVal;        
    }

    /**
     *	Function Returns the RSS ID String
     *	Note: If a String Does Not Exist, One is Created
     */
    function getRSSIDString( $SiteID = 0 ) {
	    $rVal = readSetting( "core", "RSS_$SiteID" );

	    // Create an RSS ID String If One Does Not Exist
	    if ( $rVal == "" ) {
	    	$rand = getRandomString( 36, true );
	    	$rVal = "urn:uuid:" . substr($rand,  0,  8) . '-' . substr($rand,  8,  4) . '-' .
	    						  substr($rand, 12,  4) . '-' . substr($rand, 16,  4) . '-' .
	    						  substr($rand, 20, 12);
	    	$rVal = strtolower( $rVal );
	    	saveSetting( "core", "RSS_$SiteID", $rVal );
	    }

	    // Return the String
	    return $rVal;
    }
    
    function setRSSIDString( $RSSID, $SiteID ) {
	    saveSetting( "core", "RSS_$SiteID", $RSSID );
    }

    /**
     * Function returns a random string of X Length
     */
    function getRandomString( $Length = 10, $AsHex = false ) {
        $rVal = "";
        $nextChar = "";

        $chars = ( $AsHex ) ? '0123456789abcdef' : '0123456789abcdefghijklmnopqrstuvwxyz';
        for ($p = 0; $p < $Length; $p++) {
            $randBool = rand(1, 9);
            $nextChar = ( $randBool > 5 ) ? strtoupper( $chars[mt_rand(0, strlen($chars))] ) 
                                          : $chars[mt_rand(0, strlen($chars))];
            
            //Append the next character to the string
            $rVal .= $nextChar;
        }

        // Return the Random String
        return $rVal;
    }

    /**
     * Functions are Used in uksort() Operations
     */
    function arraySortAsc( $a, $b ) {
		if ($a == $b) return 0;
		return ($a > $b) ? -1 : 1;
	}

    function arraySortDesc( $a, $b ) {
		if ($a == $b) return 0;
		return ($a > $b) ? 1 : -1;
	}

    /**
     * Function Determines if String "Starts With" the supplied String
     */
	function startsWith($haystack, $needle) {
    	return !strncmp($haystack, $needle, strlen($needle));
    }

    /**
     * Function Determines if String "Ends With" the supplied String
     */
	function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if ($length == 0) { return true; }

		return (substr($haystack, -$length) === $needle);
	}

    /**
     * Function Confirms a directory exists and makes one if it doesn't
     *      before returning a Boolean
     */
    function checkDIRExists( $DIR ){
        $rVal = true;
        if ( !file_exists($DIR) ) {
            $rVal = mkdir($DIR, 755, true);
        }

        // Return the Boolean
        return $rVal;
    }
    
    /**
     * Function Returns the Number of Files contained within a directory
     */
    function countDIRFiles( $DIR ) {
	    $rVal = 0;

	    // Only check if the directory exists (of course)
	    if ( file_exists($DIR) ) {
			foreach ( glob($DIR . "/*.token") as $filename) {
			    $rVal += 1;
			}
	    }

		// Return the Number of Files
		return $rVal;
    }

    /**
     * Function returns an array from an Object
     */
    function objectToArray($d) {
		if (is_object($d)) {
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			return array_map(__FUNCTION__, $d);
		}
		else {
			return $d;
		}
	}

    /**
     * Function constructs and returns a Google Analytics Code Snippit
     */
    function getGoogleAnalyticsCode( $UserAccount ) {
        if ( $UserAccount != '' ) {
            $rVal = tabSpace(4) . "<!-- BEGIN google analytics -->\n" .
                    tabSpace(4) . "<script type=\"text/javascript\">\n" .
                    tabSpace(6) . "var _gaq = _gaq || [];\n" .
                    tabSpace(6) . "_gaq.push(['_setAccount', '$UserAccount']);\n" .
                    tabSpace(6) . "_gaq.push(['_trackPageview']);\n" .
                    tabSpace(6) . "(function() {\n" .
                    tabSpace(8) . "var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;\n" .
                    tabSpace(8) . "ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';\n" .
                    tabSpace(8) . "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);\n" .
                    tabSpace(6) . "})();\n" .
                    tabSpace(4) . "</script>" .
                    tabSpace(4) . "<!-- END google analytics --> ";
        }

        // Return the Code Snippit
        return $rVal;
    }

    /**
     * Function scrubs a string to ensure it's safe to use in a URL
     */
    function sanitizeURL( $string, $excludeDot = true ) {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                       "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                       "â€”", "â€“", ",", "<", ">", "/", "?");
        $cleanFilter = "/[^a-zA-Z0-9-]/";
        if ( $excludeDot ) {
            array_push($strip, ".");
            $cleanFilter = "/[^a-zA-Z0-9-.]/";
        }
        $clean = NoNull(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace($cleanFilter, "", str_replace(' ', '-', $clean));

        //Return the Lower-Case URL
        return strtolower($clean);
    }

    /**
     * Function Returns a Google Maps URL for the Latitude/Longitude Provided
     */
    function getGoogleMapsCode( $Latitude, $Longitude ) {
        $rVal = '';
        
        if ( $Latitude != 0 && $Longitude != 0 ) {
            $rVal = "http://maps.google.ca/maps?q=$Latitude,$Longitude&hl=en&ll=$Latitude,$Longitude&spn=0.001967,0.003449&sll=$Latitude,$Longitude&sspn=0.007867,0.013797&t=m&z=18";
        }

        // Return the Google Maps Link
        return $rVal;
    }

    /**
     *	Function Returns a Gravatar URL for a Given Email Address
     *	Note: Code based on source from https://en.gravatar.com/site/implement/images/php/
     */
    function getGravatarURL( $emailAddr, $size = 80, $default = 'mm', $rating = 'g', $img = false, $atts = array() ) {
    	$rVal = "";
    	
    	if ( NoNull($emailAddr) != "" ) {
	    	$rVal = "http://www.gravatar.com/avatar/" . md5( strtolower( NoNull($emailAddr) ) ) .
	    			"?s=$size&d=$default&r=$rating";

		    if ( $img ) {
		        $rVal = '<img src="' . $rVal . '"';
		
		        foreach ( $atts as $key => $val )
		            $rVal .= ' ' . $key . '="' . $val . '"';
		        $rVal .= ' />';
		    }	    	
    	}

    	// Return the URL
    	return $rVal;
    }

    /**
     * Function parses the HTTP Header to extract just the Response code
     */
    function checkHTTPResponse( $header ) {
        $rVal = 0;
        
        if(preg_match_all('!HTTP/1.1 ([0-9a-zA-Z]*) !', $header, $matches, PREG_SET_ORDER)) {
        	foreach($matches as $match) {
                $rVal = nullInt( $match[1] );
        	}
        }

        // Return the HTTP Response Code
        return $rVal;
    }

    /**
     * Function parses the HTTP Header into an array and returns the results.
     * 
     * Note: HTTP Responses are not included in this array
     */
    function parseHTTPResponse( $header ) {
        $rVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));

        // Parse the Fields
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));

                if( isset($rVal[$match[1]]) ) {
                    $rVal[$match[1]] = array($rVal[$match[1]], $match[2]);
                } else {
                    $rVal[$match[1]] = trim($match[2]);
                }
            }
        }

        // Return the Array of Headers
        return $rVal;
    }

    /**
     * Function redirects a visitor to the specified URL
     */
    function redirectTo( $URL ) {
    	header( "Location: " . $URL );
    	die;
    }

    /***********************************************************************
     *  Resource Functions
     ***********************************************************************/
    /**
     * Function reads a file from the file system, parses and replaces,
     *      minifies, then returns the data in a string
     */
    function readResource( $ResFile, $ReplaceList = array(), $Minify = false ) {
        $rVal = "";

        // Check to ensure the Resource Exists
        if ( file_exists($ResFile) ) {
            $rVal = file_get_contents( $ResFile, "r");
        }
        
        // If there are Items to Replace, Do So
        if ( count($ReplaceList) > 0 ) {
            $Search = array_keys( $ReplaceList );
            $Replace = array_values( $ReplaceList );

            // Perform the Search/Replace Actions
            $rVal = str_replace( $Search, $Replace, $rVal );
        }
        
        // Strip all the white space if required
        if ( $Minify ) {
            $rVal = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $rVal);
            $rVal = ereg_replace(" {2,}", ' ',$rVal);
        }

        // Return the Data
        return $rVal;
    }

    function getLabelValue( $String, $ReplaceList = array() ) {
        $rVal = $String;

        // If there are Items to Replace, Do So
        if ( count($ReplaceList) > 0 ) {
            $Search = array_keys( $ReplaceList );
            $Replace = array_values( $ReplaceList );

            // Perform the Search/Replace Actions
            $rVal = str_replace( $Search, $Replace, $rVal );
        }

        // Return the Appropriate String
        return $rVal;
    }

    /**
     * Function returns the appropriate logging table for the current week
     * 
     * Note: Last Monday is used for the Year and Week Number to ensure
     *          roll-overs do not occur mid-week (J2fi)
     */
    function getTableName( $Prefix ) {
        date_default_timezone_set( 'Asia/Tokyo' );
        $RecordDTS = strtotime('last monday', strtotime('tomorrow'));

        //Return the Table Name
        return $Prefix . date('y', $RecordDTS) . date('W', $RecordDTS);
    }

    /***********************************************************************
     *                          Language Functions
     ***********************************************************************/
    /**
     * Function returns an array containing the base language strings used
     *      within the application, including themes, RSS feeds, and other
     *      locations.
     * 
     * Note: If the Language Requested does not exist, the Application Default
     *       will be loaded and returned.
     */
    function getLangDefaults( $LangCd ) {
        $rVal = array();

        // Load the User-Specified Language File
        $LangFile = LANG_DIR . "/" . strtolower($LangCd) . ".php";
        if ( file_exists($LangFile) ){
            require_once( $LangFile );
            $LangClass = 'lang_' . strtolower($LangCd);
            $Lang = new $LangClass();
            $rVal = $Lang->getStrings();
            unset( $Lang );

        } else {
            if ( strtolower($LangCd) != strtolower(DEFAULT_LANG) ) {
                $rVal = getLangDefaults( DEFAULT_LANG );
            } else {
                $rVal = array();
            }
        }

        // Return the Array of Strings
        return $rVal;
    }

    /**
     * Function returns a list of languages available for the Theme
     * Notes: * This list is defined by the files located in THEME_DIR/lang
     *        * The Theme's Language File is read rather than the application's
     *          language file because the a Theme cannot have languages not
     *          already supported by the app, but not the inverse.
     */
    function listThemeLangs() {
        $rVal = array();

        if ( $handle = opendir( LANG_DIR ) ) {
            //For each file; open, instantiate, read, close
            while ( false !== ($FileName = readdir($handle)) ) {
                $LangFile = LANG_DIR . "/" . $FileName;
                
                $ClassStr = explode( '.', $FileName );
                if ( $ClassStr[0] != "" ) {
                    if ( $FileName != 'langs.php' && file_exists($LangFile) ) {
                        require_once( $LangFile );
                        $ClassName = "lang_" . $ClassStr[0];
                        $LangClass = new $ClassName();
        
                        $rVal[ strtoupper( $LangClass->getLangCd() ) ] = NoNull( $LangClass->getLangName() );     // Set the Array Key=>Value
                        unset( $LangClass );
                    }
                }

            }

            //Close the Directory Handle
            closedir($handle);
        }

        // Return the Array of Language Files
        return $rVal;
    }

    /**
     * Function reads a custom language file and returns the array of values
     */
    function getThemeStrings( $Location, $DispLang ) {
        $LangFile = $Location . '/theme_' . strtolower( $DispLang ) . '.php';
        $rVal = array();

        if ( file_exists($LangFile) ){
            require_once( $LangFile );
            $LangClass = 'Extra_Labels';
            $Lang = new $LangClass;
            $rVal = $Lang->getStrings();

            //Desctruct & Unset the Class
            $Lang->__destruct();
            unset( $Lang );
        }

        // Return the Array
        return $rVal;
    }

    /**
     * Function checks a Language Code against the installed languages
     *      and returns a LangCd Response.
     * 
     * Note: If the provided Language Code is invalid, the application's
     *       default language is returned.
     */
    function validateLanguage( $LangCd ) {
        $LangList = listThemeLangs();
        
        foreach ( $LangList as $key=>$val ) {
            if ( $key = $LangCd ) {
                return $key;
            }
        }

        // Return the Default Application Language
        return DEFAULT_LANG;
    }

    /***********************************************************************
     *  MySQL Functions
     ***********************************************************************/
    /**
     * Function Queries the Required Database and Returns the values as an array
     * 
     * Notes:
     *  -- This should NOT be used to update data, as the "Read" Server is called
     */
    function doSQLQuery( $sqlStr, $UseDB = '' ) {
        $rVal = array();
        $r = 0;

        // Do Not Proceed If We Don't Have SQL Settings
		if ( !defined('DB_SERV') ) { return false; }
		if ( $UseDB == '' && !defined('DB_MAIN') ) {
			return false;
		}
		if ( $UseDB == '' ) { $UseDB = DB_MAIN; }
        writeNote( "doSQLQuery(): $sqlStr" );

        $GLOBALS['Perf']['queries']++;
        $db = mysql_connect(DB_SERV, DB_USER, DB_PASS);
        $selected = mysql_select_db($UseDB, $db);
        $utf8 = mysql_query("SET NAMES " . DB_CHARSET);
        $result = mysql_query($sqlStr);

        if ( $result ) {
            // Read the Result into an Array
            while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $arr_row = array();
                $c = 0;
                while ($c < mysql_num_fields($result)) {        
                    $col = mysql_fetch_field($result, $c);    
                    $arr_row[$col -> name] = $row[$col -> name];            
                    $c++;
                }
                $rVal[ $r ] = $arr_row;
                $r++;
            }

            // Close the MySQL Connection
            mysql_close( $db );
        }

        // Return the Array of Details
        return $rVal;
    }

    /**
     * Function Executes a SQL String against the Required Database and Returns
     *      a boolean response.
     */
    function doSQLExecute( $sqlStr, $UseDB = '' ) {
        $sqlQueries = array();
        $rVal = -1;

        // Do Not Proceed If We Don't Have SQL Settings
		if ( !defined('DB_SERV') ) { return false; }
		if ( $UseDB == '' && !defined('DB_MAIN') ) {
			return false;
		}
		if ( $UseDB == '' ) { $UseDB = DB_MAIN; }

		// Strip Out The SQL Queries (If There Are Many)
        if ( strpos($sqlStr, ';') > 0 ) {
            $sqlQueries = explode(';', $sqlStr);
        } else {
            $sqlQueries[] = $sqlStr;
        }

		$GLOBALS['Perf']['queries']++;
        $db = mysql_connect(DB_SERV, DB_USER, DB_PASS);
        $selected = mysql_select_db($UseDB, $db);
        mysql_query("SET NAMES " . DB_CHARSET);

        // Execute Each Statement
        foreach ( $sqlQueries as $sqlStatement ) {
            if ( NoNull($sqlStatement) != "" ) {
                writeNote( "doSQLExecute(): $sqlStatement" );
                mysql_query($sqlStatement);
            }
        }

        $rVal = mysql_insert_id();
        if ( $rVal == 0 ) {
            $rVal = mysql_affected_rows();
        }
        mysql_close( $db );

        // Return the Insert ID
        return $rVal;
    }

    /**
     * Function returns a SQL-safe String
     */
    function sqlScrub( $str ) {
        $rVal = $str;

        if(is_array($str)) 
        return array_map(__METHOD__, $str); 

        if(!empty($str) && is_string($str)) { 
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $str); 
        }

        // Return the Scrubbed String
        return $rVal; 
    }

    /***********************************************************************
     *                          Conversion Functions
     *
     *   The following code is used by Alpha<->Int Functions
     *
     ***********************************************************************/
    /**
     * Function returns the Character Table Required for Alpha->Int Conversions
     */
    function getChrTable() {
        return array('jNn7uY2ETd6JUOSVkAMyhCt3qw1WcpIv5P0LK4DfXFzbl8xemrB9RHGgoiQZsa',
            		 '3tDL8pPwScIbnE0gsjvK2QxoVhrf17eG6yM4BJkOTXWzNduiFHZqAC9UmY5Ral',
            		 'JyADsUFtkjzXqLG0SMb1egmhw8Q6cETpVfI5xdl42H9vROKYuNiWonPC73rBaZ',
            		 '2ZTSUXQFPgK7nwOi0N5s8z1rjqC4E6VHkRypo3J9hdBImxAGltWeMvYfLuDbca',
            		 '8NlPjJIHE7naFyewTqmdsK5YQhU9gp6WRXBVGouMDALtr0c324bzCSfOv1iZkx',
            		 'OPwcLs1zy69KpNjm0hFGaEte5UIrfVBXZYQWv27S34MJHkTbdgDARlConqx8iu'
                    );
    }

    /**
     * Function converts an AlphaNumeric Value to an Integer based on the
     *      static characters passed.
     */
	function alphaToInt($alpha) {
        $chrTable = getChrTable();

		if (!$alpha) return null;
        
		$radic = strlen($chrTable[0]);
		$offset = strpos($chrTable[0], $alpha[0]);
		if ($offset === false) return false;
		$value = 0;

		for ($i=1; $i < strlen($alpha); $i++) {
			if ($i >= count($chrTable)) break;

			$pos = (strpos($chrTable[$i], $alpha[$i]) + $radic - $offset) % $radic;
			if ($pos === false) return false;

			$value = $value * $radic + $pos;
		}

		$value = $value * $radic + $offset;

        // Return the Integer Value
		return $value;
	}

    /**
     * Function converts an Integer to an AlphaNumeric Value based on the
     *      static characters passed.
     */
	function intToAlpha($num) {
        if ( nullInt( $num ) <= 0 ) { return ""; }

        $chrTable = getChrTable();
		$digit = 5;
		$radic = strlen( $chrTable[0] );
		$alpha = '';

		$num2 = floor($num / $radic);
		$mod = $num - $num2 * $radic;
		$offset = $mod;

		for ($i=0; $i<$digit; $i++) {
			$mod = $num2 % $radic;
			$num2 = ($num2 - $mod) / $radic;

			$alpha = $chrTable[ $digit-$i ][ ($mod + $offset )% $radic ] . $alpha;
		}
		$alpha = $chrTable[0][ $offset ] . $alpha;

        // Return the AlphaNumeric Value
		return $alpha;
	}

    /***********************************************************************
     *  HTTP Asyncronous Calls
     ***********************************************************************/
    /**
     *	Function Calls a URL Asynchronously, and Returns Nothing
     *	Source: http://stackoverflow.com/questions/962915/how-do-i-make-an-asynchronous-get-request-in-php
     */
	function curlPostAsync( $url, $params ) {
	    foreach ($params as $key => &$val) {
			if (is_array($val)) $val = implode(',', $val);
			$post_params[] = $key.'='.urlencode($val);
	    }
	    $post_string = implode('&', $post_params);
	    $parts=parse_url($url);

	    $fp = fsockopen($parts['host'], isset($parts['port'])?$parts['port']:80, $errno, $errstr, 30);

	    $out = "POST ".$parts['path']." HTTP/1.1\r\n";
	    $out.= "Host: ".$parts['host']."\r\n";
	    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
	    $out.= "Content-Length: ".strlen($post_string)."\r\n";
	    $out.= "Connection: Close\r\n\r\n";
	    if (isset($post_string)) $out.= $post_string;

	    fwrite($fp, $out);
	    fclose($fp);
	}


    /**
     *	Function Calls a URL Asynchronously, and Returns Nothing
     *	Source: http://codeissue.com/issues/i64e175d21ea182/how-to-make-asynchronous-http-calls-using-php
     */
    function httpPostAsync( $url, $paramstring, $method = 'get', $timeout = '30', $returnresponse = false ) {
		$method = strtoupper($method);
		$urlParts = parse_url($url);      
		$fp = fsockopen($urlParts['host'],         
						isset( $urlParts['port'] ) ? $urlParts['port'] : 80,         
						$errno, $errstr, $timeout);
		$rVal = false;
	
		//If method="GET", add querystring parameters
		if ($method='GET')
			$urlParts['path'] .= '?'.$paramstring;
	
		$out = "$method ".$urlParts['path']." HTTP/1.1\r\n";     
		$out.= "Host: ".$urlParts['host']."\r\n";
		$out.= "Connection: Close\r\n";
	
		//If method="POST", add post parameters in http request body
		if ($method='POST') {
			$out.= "Content-Type: application/x-www-form-urlencoded\r\n";     
			$out.= "Content-Length: ".strlen($paramstring)."\r\n\r\n";
			$out.= $paramstring;      
		}

		fwrite($fp, $out);     
	
		//Wait for response and return back response only if $returnresponse=true
		if ( $returnresponse ) {
			$rVal = stream_get_contents($fp);
		} else {
			$rVal = true;
		}

		// Close the Connection
		fclose($fp);
		
		// Return the Result
		return $rVal;
	}

    /***********************************************************************
     *  API Functions
     ***********************************************************************/
    /**
     * Function sends a POST Request to the Midori API and returns an array
     *      of data
     */
    function apiRequest( $Method, $data, $referer = '', $CallType = 'POST' ) {
        $url = API_URL . $Method;
        $data = apiFilteredItems( $data );

        $GLOBALS['Perf']['apiHits']++;
        if ( !array_key_exists('apiKey', $data) ) {
            $data['apiKey'] = API_KEY;
        }
        $data = http_build_query( $data );
        $url = parse_url($url);

        if ($url['scheme'] != 'http') { 
            die('Error: Only HTTP request are supported !');
        }

        $host = $url['host'];
        $path = $url['path'];

        $fp = fsockopen($host, API_PORT, $errno, $errstr, 15);

        if ($fp){
            fputs($fp, "$CallType $path HTTP/1.1\r\n");
            fputs($fp, "Host: $host\r\n");

            if ($referer != '') {
                fputs($fp, "Referer: $referer\r\n");
            }

            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: ". strlen($data) ."\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $data);

            $result = ''; 
            while(!feof($fp)) {
                // receive the results of the request
                $result .= fgets($fp, 128);
            }
        }
        else { 
            return array(
                'status' => 'err', 
                'error' => "$errstr ($errno)"
            );
        }

        // close the socket connection:
        fclose($fp);

        // split the result header from the content
        $result = explode("\r\n\r\n", $result, 2);

        $header = isset($result[0]) ? $result[0] : '';
        $content = isset($result[1]) ? $result[1] : '';

        // return as structured array:
        $json = json_decode($content);
        if ( $json ) {
            return $json;
        } else {
            preg_match("/{(.*)}/s", $content, $match);
            return json_decode($match[0]);
        }
    }
    
    /**
     * Function Filters the Items Being Passed to the API
     */
    function apiFilteredItems( $Items ) {
        $rVal = array();
        $filter = array('PgRoot', 'PgSub1', 'DispPg', 'pftheme', 
                        'isLoggedIn', 'token',
                        'URL', 'HomeURL',
                        'ThemeName', 'ContentDIR',
                        'Location', 'isDefault'
                        );

        // Filter each of the Items
        if ( is_array($Items) ) {
            foreach ( $Items as $Key=>$Val ) {
                if ( !in_array($Key, $filter) ) {
                    $rVal[ $Key ] = $Val;
                }
            }
        }

        // Return the Array of Acceptable Items
        return $rVal;
    }

    /***********************************************************************
     *  Configuration & Information Store
     ***********************************************************************/
    /**
     * Function Saves a Setting with a Specific Token to the Temp Directory
     */
    function saveSetting( $token, $key, $value ) {
    	$settings = array();

	    // Check to see if the Settings File Exists or Not
	    if ( checkDIRExists( CONF_DIR ) ) {
		    $tmpFile = CONF_DIR . "/$token.inc";
		    if ( file_exists( $tmpFile ) ) {
			    $data = file_get_contents( $tmpFile );
			    $settings = unserialize($data);
		    }

		    // Add or Update the Specified Key
		    $settings[ $key ] = NoNull($value);

		    // Write the File Back to the Settings Folder
		    $fh = fopen($tmpFile, 'w');
		    fwrite($fh, serialize($settings));
		    fclose($fh);
	    }

	    // Return a Happy Boolean
	    return true;
    }

    /**
     * Function Reads a Setting with a Specific Token from the Temp Directory
     */
    function readSetting( $token, $key ) {
	    $rVal = "";

	    // Check to see if the Settings File Exists or Not
	    $tmpFile = CONF_DIR . "/$token.inc";
	    if ( file_exists( $tmpFile ) ) {
		    $data = file_get_contents( $tmpFile );
		    $settings = unserialize($data);
	    }

	    // If an Asterisk was Passed, Return Everything
	    if ( $key == '*' ) {
		    $rVal = $settings;
	    } else {
		    // Check to see if the Key Exists
		    if ( array_key_exists($key, $settings) ) {
			    $rVal = NoNull( $settings[ $key ] );
		    }
	    }

	    // Return the Setting Value
	    return $rVal;
    }
    
    /**
     * Function Deletes a Setting with a Specific Token from the Temp Directory
     */
    function deleteSetting( $token, $key ) {
	    $rVal = false;

	    // Check to see if the Settings File Exists or Not
	    $tmpFile = CONF_DIR . "/$token.inc";
	    if ( file_exists( $tmpFile ) ) {
		    $data = file_get_contents( $tmpFile );
		    $settings = unserialize($data);

		    // Remove the Specified Key
		    unset( $settings[$key] );

		    // Write the File Back to the Settings Folder
		    $fh = fopen($tmpFile, 'w');
		    fwrite($fh, serialize($settings));
		    fclose($fh);
		    
		    // Set the Happy Boolean
		    $rVal = true;
	    }

	    // Return the Setting Value
	    return $rVal;
    }

    /**
     * Function Clears Out a Settings File
     */
    function clearSettings( $token ) {
	    $rVal = false;

	    // Clear the File (if it exists)
	    $tmpFile = CONF_DIR . "/$token.inc";
	    if ( file_exists( $tmpFile ) ) {
		    // Create an Empty Array
		    $settings = array();

		    // Write the File to the Settings Folder
		    $fh = fopen($tmpFile, 'w');
		    fwrite($fh, serialize($settings));
		    fclose($fh);

		    $rVal = true;
	    }

	    // Return the Boolean (Stating Whether a File has been Wiped Out or Not)
	    return $rVal;
    }

    /**
     *	Function Determines if a Setting File Exists or Not
     */
    function validateSettingFile( $SettingFile ) {
	    $rVal = false;

	    // Check if the File Exists
	    $setFile = CONF_DIR . '/' . $SettingFile;
	    if ( file_exists( $setFile ) ) {
		    $rVal = true;
	    }

	    // Return the Boolean Response
	    return $rVal;

    }

    /***********************************************************************
     *  Debug & Error Reporting Functions
     ***********************************************************************/
    /**
     * Function records a note to the File System when DEBUG_ENABLED > 0
     *		Note: Timezone is currently set to Asia/Tokyo, but this should
     *			  be updated to follow the user's time zone.
     */
	function writeNote( $Message, $doOverride = false ) {
        if ( DEBUG_ENABLED != 0 || $doOverride === true ) {
            date_default_timezone_set( 'Asia/Tokyo' );
      		$ima = time();
            $yW = date('yW', $ima);
    		$log_file = "logs/debug-$yW.log";

    		$fh = fopen($log_file, 'a');
            $ima_str = date("F j, Y h:i:s A", $ima );
            $swatch = date("B", $ima );;
    		$stringData = "[$ima_str] [$swatch] | Note: $Message \n";
    		fwrite($fh, $stringData);
    		fclose($fh);
        }
	}

    /**
     * Function formats the Error Message for {Procedure} - Error and Returns it
     */
    function formatErrorMessage( $Location, $Message ) {
    	writeNote( "{$Location} - $Message", false );
        return "$Message";
    }

?>