<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called to Read Tweets
 *	NOTE: This just grabs the Open XML feed, so Private Accounts will be blocked.
 */
require_once(LIB_DIR . '/functions.php');

class Twitter {
    var $Details;
    var $Errors;

    function __construct() {
        $this->Details = $this->_populateClass();
        $this->Errors = array();
    }

    /***********************************************************************
     *  Public Functions
     ***********************************************************************/
    /**
     * Function performs the User Activity Requested
     */
    public function doUpdate( $FillGaps = false ) {
	    $rVal = 0;

	    if ( $this->Details['TwitUserName'] != "" ) {
		    if ( !FillGaps ) {
			    $rVal = $this->_updateTweets();

		    } else {
	            $LastTweet = "z";
	            $LastID = "";

	            do {
	                $LastTweet = $LastID;
	                $ut = $this->_updateTweets( $LastTweet );
	                $LastID = $ut['LastTweet'];
	            } while ( $LastTweet != $LastID );
	            $rVal = $LastID;
	        }
	    }

        //Return the Information
        return $rVal;
    }

    /***********************************************************************
     *  Private Functions
     ***********************************************************************/
    /**
     * Function builds the base variables that will be used throughout the
     *      Class
     */
    private function _populateClass() {
        $rVal = array( 'TwitUserName'      => readSetting('core', 'TwitUserName'),
                       'methodTimeline'    => "http://twitter.com/statuses/user_timeline.json",
                      );

        // Return the Base Array
        return $rVal;
    }

    /**
     * Function Updates the Local Database for the Tweets since Last Check
     */
    private function _updateTweets( $MaxID = "" ) {
        $rVal = array( 'LastTweet' => $MaxID,
                       'Records'   => 0
                       );
        $count = 100;
        
        $url = 'http://api.twitter.com/1/statuses/user_timeline.json?screen_name=' . $this->Details['TwitUserName'] . '&count=' . $count . '&trim_user=true';
        if ( $MaxID != "" ) { $url .= "&max_id=" . $MaxID; }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $Tweets = json_decode(curl_exec($ch), true);
        $TweetList = "'0'";
        
        // Collect the TweetIDs in the ResultSet
        foreach ( $Tweets as $Tweet ) {
            $TweetList .= ", '" . NoNull($Tweet['id_str']) . "'";
        }
        $TweetsInDB = $this->_getTweetIDs( $TweetList );
        
        foreach ( $Tweets as $Tweet ) {
            $Tweet = preg_replace('/"id":(\d+)/', '"id":"$1"', $Tweet);
            
            // Construct the Main Content Element
            $guid = md5($Tweet['id_str']);
            $msg = NoNull($Tweet['text']);
            $TweetID = NoNull($Tweet['id_str']);
            $TweetDTS = strtotime($Tweet['created_at']);
            $inReplyToTweet = NoNull($Tweet['in_reply_to_status_id_str']);
            $inReplyToTwit = NoNull($Tweet['in_reply_to_screen_name']);
            $geoLoc = "";
            if ( is_array($Tweet['coordinates']) ) {
                $geoLoc = implode(',', $Tweet['coordinates']);
            }

            if ( !in_array($TweetID, $TweetsInDB) ) {
                $sqlStr = "INSERT INTO `Content` (`guid`, `TypeCd`, `Title`, `Value`, `Hash`, `CreateDTS`, `UpdateDTS`) " .
                          "VALUES ('$guid', 'TWEET', '', '" . sqlScrub( $msg ) . "', '" . md5($msg) . "', FROM_UNIXTIME($TweetDTS), FROM_UNIXTIME($TweetDTS));";
                $ContentID = doSQLExecute( $sqlStr );

                if ( $ContentID > 0 ) {
                    $sqlMeta = "INSERT INTO `Meta` (`ContentID`, `guid`, `TypeCd`, `Value`, `Hash`) " .
                               "VALUES ($ContentID, '$guid', 'TWEET-ID', '$TweetID', '" . md5($TweetID) . "')";

                    if ( $inReplyToTweet != "" ) {
                        $sqlMeta .= ", ($ContentID, '$guid', 'TWT-RPYID', '$inReplyToTweet', '" . md5($inReplyToTweet) . "')" .
                                    ", ($ContentID, '$guid', 'TWT-RPYTWIT', '$inReplyToTwit', '" . md5($inReplyToTwit) . "')";
                    }
                    if ( $geoLoc != "" ) {
                        $sqlMeta .= ", ($ContentID, '$guid', 'TWT-GEO', '$geoLoc', '" . md5($geoLoc) . "')";
                    }

                    $isOK = doSQLExecute( $sqlMeta );
                }
                $rVal['Records']++;
            }
            $rVal['LastTweet'] = $TweetID;
        }

        // Return the Number of Tweets Added
        return $rVal;
    }

    private function _getTweetIDs( $TweetList ) {
        $rVal = array();

        $sqlStr = "SELECT `Value` FROM `Meta`" . 
                  " WHERE `TypeCd` = 'TWEET-ID'" .
                  "   and `Value` IN ( $TweetList )";
        $rslt = doSQLQuery( $sqlStr );
        if ( is_array($rslt) ) {
            foreach( $rslt as $row ) {
                array_push( $rVal, NoNull($row['Value']) );
            }
        }

        // Return the Array
        return $rVal;
    }

}