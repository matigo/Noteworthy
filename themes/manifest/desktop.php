<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for the Manifest Theme
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Class (J2fi)
 */
require_once( LIB_DIR . '/content.php' );

class miTheme extends theme_main {
    var $settings;
    var $messages;
    var $pageData;
    var $content;
    var $perf;

    function __construct( $settings ) {
        $GLOBALS['Perf']['app_s'] = getMicroTime();
        $this->settings = $settings;

        // Set the Resource Prefix
        $this->_setResourcePrefix();
        $this->messages = getLangDefaults( $this->settings['DispLang'] );
        
        // Prep the Content
        $this->content = new Content( $settings, dirname(__FILE__) );
        
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

        // Load the Page Data if this is Valid, otherwise redirect
        if ( $this->_isValidPage() ) {
            $this->pageData = $this->_collectPageData();
        } else {
            redirectTo($this->settings['HomeURL']);
        }
    }

    public function getHeader() {
        return $this->BuildHeaderData();
    }

    public function getContent() {
        return $this->BuildBodyData();
    }

    public function getSuffix() {
        return $this->BuildFooterData();
    }

    /***********************************************************************
     *                          Content Functions
     ***********************************************************************/
    /**
     * Function constructs the header data and returns the formatted HTML
     */
    private function BuildHeaderData() {
        return readResource( RES_DIR . '/' . $this->settings['resource_prefix'] . '_head.html', $this->pageData );
    }

    /**
     * Function constructs the body data and returns the formatted HTML
     */
    private function BuildBodyData() {
        $ResFile = '/' . $this->settings['resource_prefix'] . '_body.html';

        // Collect the Resource Data
        $rVal = readResource( RES_DIR . $ResFile, $this->pageData );

        // Return the Body Content
        return $rVal;
    }

    /**
     * Function constructs the footer data and returns the formatted HTML
     */
    private function BuildFooterData() {
        $precision = 6;
        $GLOBALS['Perf']['app_f'] = getMicroTime();
        $App = round(( $GLOBALS['Perf']['app_f'] - $GLOBALS['Perf']['app_s'] ), $precision);
        $SQL = nullInt( $GLOBALS['Perf']['queries'] );
        $Api = nullInt( $GLOBALS['Perf']['apiHits'] );
        $Analytics = ( ANALYTICS_ENABLED == 1 ) ? getGoogleAnalyticsCode( GA_ACCOUNT ) : '';
        $CopyYear = date('Y');
        $LangList = ( ENABLE_MULTILANG == 1 ) ? $this->_listLanguages() . " | " : "";

        $lblSecond = ( $App == 1 ) ? "Second" : "Seconds";
        $lblCalls  = ( $Api == 1 ) ? "Call"   : "Calls";
        $lblQuery  = ( $SQL == 1 ) ? "Query"  : "Queries";

        $ReplStr = array( '[MiSite]'     => ORG_SITE,
                          '[CopyYear]'   => $CopyYear,
                          '[ANALYTICS]'  => $Analytics,
                          '[HOMEURL]'    => $this->settings['HomeURL'],
                          '[MULTILANG]'  => $LangList,
                          '[GenTime]'    => "<!-- Page generated in roughly: $App $lblSecond, $Api API $lblCalls, $SQL SQL $lblQuery -->",
                         );
        // Add the Language-Specific Items
        foreach( $this->messages as $key=>$val ) {
            if ( !array_key_exists( $key, $ReplStr ) ) {
                $ReplStr[ "[$key]" ] = $val;
            }
        }

        // Collect the Resource
        $rVal = readResource( RES_DIR . '/' . $this->settings['resource_prefix'] . '_footer.html', $ReplStr );

        // Return the Closure
        return $rVal;
    }

    /***********************************************************************
     *                          Internal Functions
     *
     *   The following code should only be called by the above functions
     ***********************************************************************/
    /**
     * Function returns the file prefix 
     * 
     * Change Log
     * ----------
     * 2011.08.14 - Created Function (J2fi)
     */
    private function _setResourcePrefix() {
        $prefix = 'desktop';

        switch( NoNull($this->settings['ulvl']) ) {
            case 3:
            case 4:
            case 9:
                $prefix = 'test';
                break;

            default:
                $prefix = 'desktop';
        }

        // Set the Prefix Record
        $this->settings['resource_prefix'] = $prefix;
    }

    /**
     * Function returns an HTML Formatted String containing Language Options.
     * 
     * Note: The Current Language will appear as "Selected"
     * 
     * Change Log
     * ----------
     * 2011.04.26 - Created Function (J2fi)
     */
    private function _listLanguages() {
        $Langs = listThemeLangs();
        $rVal = "";

        foreach ($Langs as $key=>$val) {
            if ( strtolower($this->settings['DispLang']) != strtolower($key) ) {
                $rVal .= "<a onClick=\"javascript:switchLang('$key');\">$val</a>";
            }
        }

        // Return the List
        return $rVal;
    }

    /**
     * Function Returns a Boolean Response whether the MPage Requested
     *       is Valid or Not 
     * 
     * Note: This needs to be made a bit more automatic, as it's high
     *       maintenance in the long-term.
     * 
     * Change Log
     * ----------
     * 2012.04.14 - Created Function (J2fi)
     */
    private function _isValidPage() {
        $rVal = false;
        $validPg = array('about', 'archives', 'blog', 'sitemap', 'atom', 'rss',
                         'contact', 'projects', 'services', 'search', 'tags', '');
        
        // Append the Valid Years of Content (Yes, This Needs to be Done Better)
        $validPg[] = "1979";
        for ( $i = 2006; $i <= date("Y"); $i++ ) {
            $validPg[] = "$i";
        }

        // Determine if the Page Requested is in the Array
        if ( in_array(NoNull($this->settings['mpage']), $validPg) ) {
            $rVal = true;
        }

        // Return the Boolean Response
        return $rVal;
    }
    
    /**
     * Function Loads the Entire ReplStr Array for Use Throughout the Page and
     *      Returns the Array
     * 
     * Change Log
     * ----------
     * 2012.05.27 - Created Function (J2fi)
     */
    private function _collectPageData() {
        $ReplStr = array( '[HOME_LOC]'    => APP_ROOT,
                          '[HOMEURL]'     => $this->settings['HomeURL'],
                          '[SITEURL]'     => $this->settings['URL'],
                          '[COPYRIGHT]'   => date('Y') . " - " . NoNull($this->messages['company_name'], NoNull($this->messages['site_name'])),
                          '[SITEDESCR]'   => $this->messages['site_descr'],
                          '[APPINFO]'     => APP_NAME . " | " . APP_VER,
                          '[APP_VER]'     => APP_VER,
                          '[GENERATOR]'   => GENERATOR,
                          '[LANG_CD]'     => strtoupper($this->messages['lang_cd']),
                          '[SIGNWARN]'    => ( DEBUG_ENABLED == 1 ) ? YNBool( $this->settings['isLoggedIn'] ) ? " [DEBUG]" : " [ERROR]" : "",
                          '[ERROR_MSG]'   => '',
                          '[CONF_DIR]'    => $this->settings['HomeURL'] . "/conf",
                          '[CSS_DIR]'     => CSS_DIR,
                          '[IMG_DIR]'     => IMG_DIR,
                          '[JS_DIR]'      => JS_DIR,
                          
                          /* Body Content */
                          '[BLOG_BODY]'   => $this->_getBlogContent( 5 ),
                          '[NAVIGATION]'  => $this->_getNavigationMenu(),
                          '[PAGE_TITLE]'  => $this->_getPageTitle( NoNull($this->settings['mpage']) ),
                          '[EXTEND_HDR]'  => '',
                          
                         );

        // Read In the Language Strings
        foreach( $this->messages as $key=>$val ) {
            if ( !array_key_exists( $key, $ReplStr ) ) {
                $ReplStr[ "[$key]" ] = $val;
            }
        }

        // Add any Extra Data
        $Extras = $this->_getExtraContent();
        foreach( $Extras as $key=>$val ) {
            if ( !array_key_exists( $key, $ReplStr ) ) {
                $ReplStr[ $key ] = $val;
            }
        }

        // Read the Appropriate Template File if the Page Requested is Valid
        if ( $this->_isValidPage() ) {
            $ReqFile = $this->_getReqFileName();
            $ReplStr[ '[CONTENT_BODY]' ] = readResource( RES_DIR . $ReqFile, $ReplStr );
        }

        // Return the Array
        return $ReplStr;
    }

    /**
     * Function Returns the Appropriate Page Title for a Section
     * 
     * Change Log
     * ----------
     * 2012.04.14 - Created Function (J2fi)
     */
    private function _getPageTitle( $Section ) {
        $rVal = NoNull($this->messages['site_name']);
        $rSuffix = "";

        switch ( strtolower($Section) ) {
            case 'blog':
                $pURL = $this->settings['year']  . $this->settings['month'] . 
                        $this->settings['day']   . '_' . $this->settings['title'];
                $rVal = $this->content->getPostTitle( $pURL );
                break;
            
            default:
                $rSuffix = $this->messages['ttl_' . strtolower(NoNull($this->settings[mpage])) ];
        }

        // Append the Page Title if it's Applicable
        if ( $rSuffix != '' ) { $rVal .= " | $rSuffix"; }

        // Return the Page Title
        return $rVal;
    }

    /**
     * Function Returns the Additional Resource Requirements for the Requested Page
     * 
     * Change Log
     * ---------- 
     * 2012.04.14 - Created Function (J2fi)
     */
    private function _getExtendedHeaderInfo() {
        $rVal = '';
        
        switch ( NoNull($this->settings['mpage']) ) {
            case 'contact':
                $rVal = tabSpace(4) . "<link rel=\"stylesheet\" href=\"" . CSS_DIR . "/contact.css\" type=\"text/css\" />";
                break;

            case '':
                $rVal = tabSpace(4) . "";

            default:
                $rVal = '';
        }

        // Return the Extended Header Information
        return $rVal;
    }

    /**
     * Function Returns any Extra Content Fields that Need to Appear
     *      in the $ReplStr Array
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    private function _getExtraContent() {
        $rVal = array( '[ARCHIVE-LIST]' => '',
                       '[SOCIAL-LINK]'  => '',
                       '[RESULTS]'      => '',
                      );

        switch ( $this->settings['mpage'] ) {
            case 'archives':
            case 'archive':
                $rVal['[ARCHIVE-LIST]'] = $this->_getArchivesHTML();
                break;

            case 'blog':
                $tArr = array( '[PAGE-URL]' => $this->settings['HomeURL'] . "/" .
                                               $this->settings['year']  . "/" . 
                                               $this->settings['month'] . "/" .
                                               $this->settings['day']   . "/" . 
                                               $this->settings['title'] . "/",
                              );
                $rVal['[SOCIAL-LINK]'] = readResource( RES_DIR . '/content-blog-social.html', $tArr );
                break;

            case 'contact':
                $rVal['[RESULTS]'] = "";
                if ( NoNull($this->settings['firstName']) ) {
                    $rVal['[RESULTS]'] = "<p style=\"background-color: #FFFF7E; border: 1px solid #000000; display: block; padding: 3px 5px; width: 95%;\">Thank you for the message, " . NoNull($this->settings['firstName']) . "</p>";
                }
                break;
            
            case 'search':
                $rVal['[RESULTS]'] = $this->_getSearchHTML();
                break;
            
            default:
                
        }

        // Return the Extra Content Data
        return $rVal;
    }

    /**
     * Function Returns the Appropriate .html Content File Required for a
     *      given mPage / sPage Combination.
     * 
     * Change Log
     * ----------
     * 2012.04.14 - Created Function (J2fi)
     */
    private function _getReqFileName() {
        $rVal = '';
        $FileName = '/content-' . strtolower(NoNull($this->settings['mpage'])) . '.html';
        if ( NoNull($this->settings['mpage']) == 'page' ) {
            $FileName = '/content-blog.html';
        }
        
        if ( file_exists( RES_DIR . $FileName ) ) {
            $rVal = $FileName;
        } else {
            $rVal = '/content-landing.html';
        }

        // Return the Required FileName
        return $rVal;        
    }

    private function _getNavigationMenu() {
        $rVal = "";

        $inCache = $this->content->getContent("navMenu", CACHE_EXPY);
        if ( $inCache ) {
            $rVal = $inCache;
        } else {
            $pages = array("about"		=> $this->messages['lblAbout'],
                           "archives"	=> $this->messages['lblArchives'],
                           "links"		=> $this->messages['lblLink'],
                           );

            $i = 1;
            foreach ( $pages as $url=>$title ) {
                $suffix = ( NoNull($url) == "" ) ? "" : "/$url";
                $rVal .= "<li class=\"page_item\"><a href=\"" . $this->settings['HomeURL'] . $suffix . "\" title=\"$title\">$title</a></li>";
                $i++;
            }

            // Save the Data to Cache
            $this->content->saveContent($navMenuID, $rVal);
        }

        // Return the Top Navigation Menu
        return $rVal;
    }
    /* ********************************************************************* *
     *  Blog Content Component
     * ********************************************************************* */
    private function _getBlogContent( $PostNum ) {
        $rVal = "";
        $ReplStr = array( '[HOMEURL]'		=> $this->settings['HomeURL'],
                          '[POST-AUTHOR]'	=> "",
                          '[POST-FOOTER]'	=> "",
                          '[POST-TAG]'		=> "",
                          '[COMMENTS]'		=> "",
                          '[GEO-TAG]'		=> "",
                         );

        // Determine the Content Type
        if ( nullInt($this->settings['mpage']) == 0 ) {
            $mPage = NoNull($this->settings['mpage']);
        } else {
            $mPage = 'monthly';
            $this->settings['year'] = nullInt($this->settings['mpage']);
            $this->settings['month'] = NoNull($this->settings['spage']);
        }

        // Return Some Lorem Ipsum for Now
        $rVal = '<div class="post hentry">
					<h5 class="postDate"><abbr class="published">In The Year of our Lord, 1176</abbr></h5>
					<div class="postContent">
						<h3 class="entry-title"><a href="' . $this->settings['HomeURL'] . '/2009/02/lorem-ipsum/" rel="bookmark">Lorem Ipsum Post</a></h3>
						<h4 class="vcard author">by <span class="fn">jo Mama</span></h4>

						<div class="entry-content">
							<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
						</div>
					</div>

					<div class="postMeta">
						<div class="comments">
							<a href="' . $this->settings['HomeURL'] . '/2009/02/lorem-ipsum/#comments" title="Comment on Lorem Ipsum Post">0 Comments</a>          
						</div>
					</div>
				</div>';

        // Return the HTML Formatted Content
        return $rVal;
    }
    
    private function _parseBlogPost( $postData ) {
        $rVal = "";
        $ReplStr = array( '[HOMEURL]'     => $this->settings['HomeURL'],
                          '[DISQUS_ID]'   => $this->settings['disqus_name'],
                          '[POST-AUTHOR]' => "",  
                          '[POST-FOOTER]' => "",
                          '[COMMENTS]'    => "",
                          '[GEO-TAG]'     => "",
                         );

        if ( $postData ) {
            foreach( $postData->data as $key=>$val ) {
                if ( is_array($val) ) {
                    foreach( $val as $k=>$v ) {
                        if ( key_exists($v->TypeCd, $ReplStr) ) {
                            $ReplStr[ strtoupper("[$v->TypeCd]") ] = $ReplStr[ strtoupper($v->TypeCd) ] . ", " . $v->Value;
                        } else {
                            $ReplStr[ strtoupper("[$v->TypeCd]") ] = $v->Value;
                        }
                    }
                } else {
                    $ReplStr[ strtoupper("[$key]") ] = $val;
                }
            }

            // Save the Post Title to the Content Cache
            $pURL = $this->settings['year']  . $this->settings['month'] . 
                    $this->settings['day']   . '_' . $this->settings['title'];
            $this->content->setPostTitle( $pURL, $ReplStr['[TITLE]'] );

            $UTCTime = strtotime($ReplStr['[CREATEDTS]']);
            $ReplStr['[DATE-UTC]'] = date(DATE_ATOM, $UTCTime );
            $ReplStr['[DATE-STR]'] = date("F jS Y h:i A", $UTCTime );
            $ReplStr['[DIV-CLASS]'] = "genesis-feature genesis-feature-1 genesis-feature-odd";
            if ( NoNull($ReplStr['[POST-AUTHOR]']) == "" ) { $ReplStr['[POST-AUTHOR]'] = "Jason F. Irwin"; }
            if ( NoNull($ReplStr['[POST-GPS]']) > "" ) {
                $GeoLoc = explode(",", $ReplStr['[POST-GPS]']);
                if ( $this->_isValidGeo($ReplStr['[POST-GPS]']) ) {
                    $GeoLocStr = number_format(nullInt($GeoLoc[0]), 8) . ", " . number_format(nullInt($GeoLoc[1]), 8);
                    $URL = "<a href=\"" . urlencode("http://maps.google.ca/maps?q=" . $ReplStr['[POST-GPS]'] . "&hl=en&t=m&z=16") . "\" target=\"_blank\">$GeoLocStr</a>";
                    $ReplStr['[GEO-TAG]'] = "<span class=\"geo\" style=\"display: block;\">" . $this->messages['lblGeoTag'] . ": $URL</span>";
                }
            }

            // Ensure the Footnotes Are in the Correct Format
            if ( $ReplStr['[POST-FOOTER]'] != "" ) { 
                $ReplStr['[POST-FOOTER]'] = readResource( RES_DIR . '/content-blog-footer.html', $ReplStr); 
            }

            // Ensure the Blog Content is Up to Date
            $Search = array_keys( $ReplStr );
            $Replace = array_values( $ReplStr );
            $Search[]  = "\n";
            $Replace[] = "<br />";
            $ReplStr['[CONTENT]'] = str_replace( $Search, $Replace, $ReplStr['[CONTENT]'] );

            // Construct the Comments
            $ReplStr['[COMMENTS]'] = readResource( RES_DIR . '/content-blog-comments.html', $ReplStr);

            // Replace the Template Content Accordingly
            $rVal = readResource( RES_DIR . '/content-blog.html', $ReplStr);
        }

        // Return the Blog Post Data
        return $rVal;
    }

    /**
     * Function Parses a Page and returns the HTML-Formatted Information
     * 
     * Change Log
     * ----------
     * 2012.04.22 - Created Function (J2fi)
     */
    private function _parsePage( $postData ) {
        $rVal = "";
        $ReplStr = array( '[HOMEURL]'     => $this->settings['HomeURL'],
                          '[POST-AUTHOR]' => "Jason F. Irwin",
                          '[POST-FOOTER]' => "",
                          '[COMMENTS]'    => "",
                          '[GEO-TAG]'     => "",
                          '[PAGINATION]'  => "",
                         );

        if ( $postData ) {
            $isFirst = true;
            $isOdd = true;
            $i = 1;
            foreach ($postData->data as $blog=>$entry ) {
                $ReplStr = array( '[HOMEURL]'     => $this->settings['HomeURL'],
                                  '[POST-FOOTER]' => "",
                                  '[COMMENTS]'    => "",
                                  '[DIV-CLASS]'   => "",
                                  '[GEO-TAG]'     => "",
                                 );
                if ( $isFirst ) {
                    $ReplStr['[DIV-CLASS]'] = "genesis-feature genesis-feature-$i genesis-feature-odd";
                    $isFirst = false;

                } else {
                    $EvenOdd = ( $isOdd ) ? "odd" : "even";
                    $ReplStr['[DIV-CLASS]'] = "genesis-grid genesis-grid-$i genesis-grid-$EvenOdd";
                }
                $isOdd = !$isOdd;
                $i++;

                // Convert the Item from an Object to an Array (If Required)
                if ( !is_array($entry) ) { $entry = objectToArray( $entry ); }

                foreach ( $entry as $key=>$val ) {
                    if ( is_array($val) ) {
                        foreach ( $val as $ky=>$vl ) {
                            $TypeCd = "[" . strtoupper( NoNull($ky) ) . "]";
                            if ( key_exists($TypeCd, $ReplStr) ) {
                                $ReplStr[ $TypeCd ] .= ", " . NoNull( $vl );
                            } else {
                                $ReplStr[ $TypeCd ] = NoNull( $vl );
                            }
                        }
                    } else {
                        $ReplStr[ strtoupper("[$key]") ] = $val;
                    }
                }

                // Set the Time and Complete the Replace Array
                $UTCTime = strtotime($ReplStr['[CREATEDTS]']);
                $ReplStr['[DATE-UTC]'] = date(DATE_ATOM, $UTCTime );
                $ReplStr['[DATE-STR]'] = date("F jS Y g:i A", $UTCTime );

                // Ensure the Blog Content is Up to Date
                $Search = array_keys( $ReplStr );
                $Replace = array_values( $ReplStr );

                // Grab Just the First Paragraph
                preg_match("/<p>(.*?)<\/p>/", $ReplStr['[VALUE]'], $paragraphs);
                $Preview = $paragraphs[1] . 
                           "<p style=\"font-size: 80%;\"><a href=\"" . $this->settings['HomeURL'] . $ReplStr['[POST-URL]'] . 
                               "\" title=\"" . $ReplStr['[POST-TITLE]'] . "\">" . $this->messages['lblReadMore'] . "</a></p>";
                $ReplStr['[CONTENT]'] = str_replace( $Search, $Replace, $Preview );
                if ( NoNull($ReplStr['[POST-GPS]']) > "" ) {
                    $GeoLoc = explode(",", $ReplStr['[POST-GPS]']);
                    $GeoLocStr = number_format(nullInt($GeoLoc[0]), 8) . ", " . number_format(nullInt($GeoLoc[1]), 8);
                    $URL = "<a href=\"" . urlencode("http://maps.google.ca/maps?q=" . $ReplStr['[POST-GPS]'] . "&hl=en&t=m&z=16") . "\" target=\"_blank\">$GeoLocStr</a>";
                    $ReplStr['[GEO-TAG]'] = "<span class=\"geo\" style=\"display: block;\">" . $this->messages['lblGeoTag'] . ": $URL</span>";
                }
                $ReplStr['[POST-FOOTER]'] = "";
                
                // Replace the Template Content Accordingly
                $rVal .= readResource( RES_DIR . '/content-blog.html', $ReplStr);
            }
        }

        // Return the Page Data
        return $rVal;
    }

    private function _getMonthlyArchives() {
        $rVal = "";
        $totalCount = 0;
        $i = 0;

        $inCache = $this->content->getContent("monthlyArchives", 300);
        if ( $inCache ) {
            $rVal = $inCache;
        } else {
            $postData = apiRequest( "content/summary", array('Show' => 'monthly') );
            if ( $postData ) {
                $rVal = "<ul>";
                foreach( $postData->data as $item ) {
                    if ( $i <= 15 ) {
                        $URL = $this->settings['HomeURL'] . "/" . $item->PostYear . "/" . $item->PostMonth . "/";
                        $MonthName = $this->messages['lblMonth' . $item->PostMonth];
                        $Title = "$MonthName (" . nullInt($item->PostCount) . " Posts)";
                        $Display = "$MonthName " . $item->PostYear;
                        $rVal .= "<li><a href=\"$URL\" title=\"$Title\">$Display</a></li>";
                    }

                    $i++;
                    $totalCount += nullInt($item->PostCount);
                }
                if ( $totalCount > 8 ) { $totalCount = $totalCount - 8; }
                $rVal .= "<li><a href=\"" . $this->settings['HomeURL'] . "/archives/\" title=\"Show All " . number_format($totalCount, 0) . " Posts\">Show All Archives</a></li>";
                $rVal .= "</ul>";

                // Save the Data to Cache
                $this->content->saveContent("monthlyArchives", $rVal);
            }            
        }

        // Return the Monthly Archives Item
        return $rVal;
    }
    
    private function _getTagListings() {
        $rVal = "";
        $inCache = $this->content->getContent("tagListings", 3600);
        
        if ( $inCache ) {
            $rVal = $inCache;
        } else {
            $postData = apiRequest( "content/summary", array('Show' => 'tags', 'Count' => 40) );
            if ( $postData ) {
                $rVal = "<div class=\"tagcloud\">\n";
                foreach( $postData->data as $item ) {
                    $TagName = $item->TagName;
                    $URL = $this->settings['HomeURL'] . "/tags/" . urlencode($TagName) . "/";
                    $Title = nullInt($item->Posts) . " Posts";
                    $rVal .= "<a href=\"$URL\" class='tag-link' title=\"$Title\" style=\"font-size: 8pt;\">$TagName</a>\n";
                }
                $rVal .= "</div>";

                // Save the Data to Cache
                $this->content->saveContent("tagListings", $rVal);            
            }
        }
        
        // Return the Tags Listing
        return $rVal;
    }
    
    private function _getPostsListings( $Count = 10 ) {
        $rVal = "";
        $inCache = $this->content->getContent("postListings_$Count", 300);

        if ( $inCache ) {
            $rVal = $inCache;
        } else {
            $postData = apiRequest( "content/summary", array('Show' => 'posts', 'Count' => $Count) );
            if ( $postData ) {
                $rVal = "<ul>\n";
                foreach( $postData->data as $item ) {
                    $URL = $this->settings['HomeURL'] . $item->Value;
                    $Title = $item->Title;
                    
                    $rVal .= "<li><a href=\"$URL\" title=\"$Title\">$Title</a></li>\n";
                }
                $rVal .= "</ul>";

                // Save the Content to Cache
                $this->content->saveContent( "postListings_$Count", $rVal);
            }
        }
        
        // Return the Tags Listing
        return $rVal;
    }

    /**
     * Function Returns the Last X Tweets in a List
     * 
     * Change Log
     * ----------
     * 2012.04.22 - Created Function (J2fi)
     */
    private function _getTweets( $Count = 3 ) {
        $rVal = "";
        
        $inCache = $this->content->getContent("tweets_$Count", 60);
        if ( $inCache ) {
            $rVal = $inCache;
        } else {
            $postData = apiRequest( "content/read", array('Show' => 'tweets', 'Count' => $Count, 'Page' => 1) );
            if ( $postData ) {
                foreach( $postData->data as $item ) {
                    $URL = NoNull($item->URL);
                    $Tweet = parseTweet($item->Value);
                    $Time = getTimeSince( $item->CreateUTS );
    
                    $rVal .= "<li>$Tweet<span style=\"display: block; font-size: 85%;\"><a href=\"$URL\" rel=\"nofollow\">about $Time ago</a></span></li>";
                }

                // Save the Data
                $this->content->saveContent("tweets_$Count", $rVal);
            }
        }

        // Return the Tags Listing
        return $rVal;
    }

    /**
     * Function Returns the HTML-formatted Archives Page Content
     * 
     * Change Log
     * ----------
     * 2012.05.05 - Created Function (J2fi)
     */
    private function _getArchivesHTML() {
        $rVal = "";
        $Count = 50000;

        $inCache = $this->content->getContent("archives", 3600);
        if ( $inCache ) {
            $rVal = $inCache;
        } else {
            $postData = apiRequest( "content/summary", array('Show' => 'posts', 'Count' => $Count) );
            if ( $postData ) {
                $rVal = tabSpace( 8) . "<div class=\"car-container car-collapse\">\n" .
                        tabSpace(10) . "[POST_COUNT]\n" .
                        tabSpace(10) . "<ul class=\"car-list\">\n";
                $YearMonth = "";
                $i = 0;

                foreach( $postData->data as $item ) {
                    $URL = NoNull($item->Value);
                    $Title = htmlspecialchars(NoNull($item->Title), ENT_QUOTES);
                    $Date = strtotime( $item->CreateDTS );
    
                    // Add the Year/Month Grouping
                    if ( $YearMonth != date("Ym", $Date) ) {
                        if ( $YearMonth != "" ) {
                            $rVal .= tabSpace(14) . "</ul>\n" .
                                     tabSpace(12) . "</li>\n";
                        }
                        $YearMonth = date("Ym", $Date);
                        $rVal .= tabSpace(12) . "<li>\n" .
                                 tabSpace(14) . "<span class=\"car-yearmonth\">" . date("F Y", $Date) . "</span>\n" .
                                 tabSpace(14) . "<ul class=\"car-monthlisting\">\n";
                    }
    
                    // Add the Post to the List
                    $rVal .= tabSpace(16) . "<li>" . date("d", $Date) . ": <a href=\"" . $this->settings['HomeURL'] . "$URL\">$Title</a></li>\n";
                    $i++;
                }
    
                // Close off the div
                $rVal .= tabSpace(14) . "</ul>\n" .
                         tabSpace(12) . "</li>\n" .
                         tabSpace(10) . "</ul>\n" .
                         tabSpace( 8) . "</div>\n";
                $CountLine = "<p>Here you can view all " . number_format($i) . " posts I&apos;ve published on this site, sorted from newest to oldest.</p>";
                $rVal = str_replace( '[POST_COUNT]', $CountLine, $rVal );
                
                // Save the Data
                $this->content->saveContent("archives", $rVal);
            }            
        }
        
        // Return the HTML
        return $rVal;
    }

    private function _getSearchHTML() {
        $rVal = "I Can't Find Anything Like You Asked For...";
        $SearchStr = NoNull( $this->settings['s'] );
        $SearchQty = nullInt( $this->settings['count'], 25 );
        if ( $SearchQty <= 0 ) { $SearchQty = 25; }
        
        if ( $SearchStr != "" ) {
            $postData = apiRequest( "content/search", array('s' => $SearchStr, 'Count' => $SearchQty) );
            if ( $postData ) {
                $rVal = "";
                $i = 0;
                foreach( $postData->data as $item ) {
                    $UTCTime = strtotime($item->CreateDTS);
                    $ReplStr = array( '[TITLE]'     => NoNull($item->Title),
                                      '[CREATEDTS]' => date("F jS Y g:i A", $UTCTime ),
                                      '[URL]'       => NoNull($item->URL),
                                      '[ID]'        => $i,
                                      '[IMG_DIR]'   => IMG_DIR,
                                     );
                    switch ( strtoupper(NoNull($item->TypeCd)) ) {
                        case 'TWEET':
                            $ReplStr['[CONTENT]'] = parseTweet($item->Value);
                            $ReplStr['TITLE'] = strip_tags( $ReplStr['CONTENT'] );
                            $rVal .= readResource( RES_DIR . '/content-search-tweet.html', $ReplStr );
                            break;

                        default:
                            // Get Just the Paragraph containing the Selected Word
                            $ReplStr['[CONTENT]'] = $this->_getSearchSection( $item->Value, $SearchStr );
                            $rVal .= readResource( RES_DIR . '/content-search-post.html', $ReplStr );
                    }
                    $i++;
                }
            }        
        }

        // Return the Search Results
        return $rVal;
    }

    /**
     * Function Returns Just the Paragraph Containing the String Requested
     * 
     * Change Log
     * ----------
     * 2012.05.05 - Created Function (J2fi)
     */
    private function _getSearchSection( $Content, $Needle ) {
        $rVal = "";
        
        // Split the Content into an Array
        $Words = explode(" ", strip_tags($Content));
        
        // Determine the Location of the Needle
        $Key = array_search( $Needle, $Words );
        $Start = (($Key - 10) < 0) ? 0 : ($Key - 10);
        if ( $Key > 0 ) { $rVal .= "..."; }

        // Construct the String
        for ( $i = $Start; $i <= ($Start + 20); $i++ ) {
            if ( $Words[$i] == $Needle ) {
                $rVal .= "<span class=\"searchresult\">" . $Words[$i] . "</span> ";
            } else {
                $rVal .= $Words[$i] . " ";
            }
        }
        if ( count($Words) > $i ) { $rVal .= "..."; }

        // Return the Value
        return "<p>" . NoNull($rVal) . "</p>";
    }

}
?>
