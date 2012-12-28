<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for the Admin Theme
 */
require_once( LIB_DIR . '/content.php' );

class miTheme extends theme_main {
    var $settings;
    var $messages;
    var $content;
    var $perf;

    function __construct( $settings ) {
        $GLOBALS['Perf']['app_s'] = getMicroTime();
        $this->settings = $settings;

        // Set the Resource Prefix
        $this->settings['resource_prefix'] = 'desktop';
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

        // Prep the Content
        $this->content = new Content( $settings, $this->messages, dirname(__FILE__) );

        // Load the Page Data if this is Valid, otherwise redirect
        if ( !$this->_isValidPage() ) {
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
        $ReplStr = array( '[HOMEURL]'	  => $this->settings['HomeURL'],
                      	  '[SITEURL]'	  => $this->settings['URL'],
                      	  '[HOME_LOC]'    => APP_ROOT,
                      	  '[APPINFO]'	  => APP_NAME . " | " . APP_VER,
                      	  '[APP_VER]'	  => APP_VER,
                      	  '[GENERATOR]'	  => GENERATOR,
                          '[COPYRIGHT]'   => date('Y') . " - " . NoNull($this->messages['company_name']),
                          '[SITEDESCR]'   => $this->messages['site_descr'],
                          '[PAGE_TITLE]'  => $this->_getPageTitle( NoNull($this->settings['mpage']) ),
                          '[LANG_CD]'     => strtoupper($this->messages['lang_cd']),
                          '[ERROR_MSG]'   => '',
                          '[CONF_DIR]'    => $this->settings['HomeURL'] . "/conf",
                          '[CSS_DIR]'     => CSS_DIR,
                          '[IMG_DIR]'     => IMG_DIR,
                          '[JS_DIR]'      => JS_DIR,
                          '[TOKEN]'       => $this->settings['token']
                         );

        return readResource( RES_DIR . '/' . $this->settings['resource_prefix'] . '_head.html', $ReplStr );
    }

    /**
     * Function constructs the body data and returns the formatted HTML
     */
    private function BuildBodyData() {
        $ResFile = '/' . $this->settings['resource_prefix'] . '_body.html';

        // Collect the Resource Data
        $data = $this->_collectPageData();
        $rVal = readResource( RES_DIR . $ResFile, $data );

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

        $lblSecond = ( $App == 1 ) ? "Second" : "Seconds";
        $lblCalls  = ( $Api == 1 ) ? "Call"   : "Calls";
        $lblQuery  = ( $SQL == 1 ) ? "Query"  : "Queries";

        $ReplStr = array( '[GenTime]'    => "<!-- Page generated in roughly: $App $lblSecond, $Api API $lblCalls, $SQL SQL $lblQuery -->",
                         );

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
     * Function returns an HTML Formatted String containing Language Options.
     * 
     * Note: The Current Language will appear as "Selected"
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
     */
    private function _isValidPage() {
        $rVal = true;

        $validPg = array('login', 'landing', 'dashboard', 'search', '');

        // Determine if the Page Requested is in the Array
        if ( in_array(NoNull($this->settings['spage']), $validPg) ) {
            $rVal = true;
        }

        // Return the Boolean Response
        return $rVal;
    }

    /**
     * Function Loads the Entire ReplStr Array for Use Throughout the Page and
     *      Returns the Array
     */
    private function _collectPageData() {
    	$PostCount = (!is_numeric( $this->content->getReadableURI() )) ? 5 : 9;
        $ReplStr = array( '[HOMEURL]'	  => $this->settings['HomeURL'],
                      	  '[SITEURL]'	  => $this->settings['URL'],
                          '[COPYRIGHT]'   => date('Y') . " - " . NoNull($this->messages['company_name'], NoNull($this->settings['site_name'])),
                          '[CopyYear]'	  => date('Y'),
                          '[SITENAME]'	  => $this->settings['site_name'],
                          '[SITEDESCR]'   => $this->settings['site_descr'],
                          '[APPINFO]'     => APP_NAME . " | " . APP_VER,
                          '[APP_VER]'     => APP_VER,
                          '[GENERATOR]'   => GENERATOR,
                          '[TOKEN]'		  => NoNull($this->settings['token']),
                          '[EN_TOKEN]'	  => readSetting('core', 'DevToken'),
                          '[EN_SANDBOX]'  => readSetting('core', 'UseSandbox'),
                          '[ACCESSKEY]'	  => NoNull($this->settings['api_key']),
                          '[LANG_CD]'     => strtoupper($this->messages['lang_cd']),
                          '[USERNAME]'	  => readSetting('core', 'username'),
                          '[ERROR_MSG]'   => $this->_getPageError(),
                          '[CONF_DIR]'    => $this->settings['HomeURL'] . "/conf",
                          '[CSS_DIR]'     => CSS_DIR,
                          '[IMG_DIR]'     => IMG_DIR,
                          '[JS_DIR]'      => JS_DIR,

                          /* Body Content */
                          '[NAVIGATION]'  => $this->_getNavigationMenu(),
                          '[PAGE_TITLE]'  => $this->_getPageTitle(),
                          '[EXTEND_HDR]'  => '',                          
                         );

        // Read In the Language Strings
        foreach( $this->messages as $key=>$val ) {
            if ( !array_key_exists( $key, $ReplStr ) ) {
                $ReplStr[ "[$key]" ] = $val;
            }
        }
        
        // Read In the Settings Data
        foreach( $this->settings as $key=>$val ) {
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
     * Function Returns Either a Formatted Error Message or an Empty String.
     */
    private function _getPageError() {
	    $rVal = '';
	    
	    if ( NoNull($this->settings['ErrorMsg']) != '' ) {
		    $rVal = '<div class="sys-message sys-error"><p>' . NoNull($this->settings['ErrorMsg']) . '</p></div>';
	    }
	    
	    // Return the Error Message
	    return $rVal;
    }

    /**
     * Function Returns the Appropriate Page Title for a Section
     */
    private function _getPageTitle() {
        $rVal = NoNull($this->messages['site_name']);
        $rSuffix = $this->messages['ttl_' . strtolower(NoNull($this->settings['mpage'])) ];

        // Append the Page Title if it's Applicable
        if ( $rSuffix != '' ) { $rVal .= " | $rSuffix"; }

        // Return the Page Title
        return $rVal;
    }

    /**
     * Function Returns the Additional Resource Requirements for the Requested Page
     */
    private function _getExtendedHeaderInfo() {
        $rVal = '';
        
        switch ( NoNull($this->settings['spage']) ) {
            case 'contact':
                $rVal = tabSpace(4) . "<link rel=\"stylesheet\" href=\"" . CSS_DIR . "/contact.css\" type=\"text/css\" />";
                break;

            case 'dashboard':
            case '':
            	if ( YNBool($this->settings['isLoggedIn']) ) {
	            	$rVal = '<link rel="stylesheet" href="' . CSS_DIR . '/prettyPhoto.css" type="text/css" /><!-- lightbox stylesheet -->\r\n' .
	            			'<link rel="stylesheet" href="' . JS_DIR . '/markitup/skins/simple/style.css" type="text/css" /><!-- WYSWYG editor stylesheet -->\r\n' .
	            			'<link rel="stylesheet" href="' . JS_DIR . '/markitup/sets/default/style.css" type="text/css" /><!-- WYSWYG editor stylesheet -->\r\n' .
	            			'<link rel="stylesheet" href="' . CSS_DIR . '/jquery-ui.custom.css" type="text/css" /><!-- jQuery UI stylesheet -->\r\n' .
	            			'<link rel="stylesheet" href="' . CSS_DIR . '/font-awesome.css" />\r\n' .
	            			'<link rel="stylesheet" href="' . CSS_DIR . '/font-awesome.less" />';
            	}
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
     */
    private function _getExtraContent() {
        $rVal = array( '[ARCHIVE-LIST]' => '',
                       '[SOCIAL-LINK]'  => '',
                       '[RESULTS]'      => '',
                      );

        switch ( $this->settings['spage'] ) {
            case 'sites':
            	$UseSandbox = NoNull($this->setting['sandbox'], readSetting( 'core', 'UseSandbox' ));
            	if ( $UseSandbox != 'N' ) { $UseSandbox = 'Y'; }
            	
            	// Set the Various Values for Sandbox Usage
                $rVal['[raSandboxChk]'] = ($UseSandbox == 'Y') ? 'checked="checked"' : '';
                $rVal['[raProductionChk]'] = ($UseSandbox == 'N') ? 'checked="checked"' : '';
                $rVal['[note-sandboxStyle]'] = ($UseSandbox == 'N') ? 'style="display: none;"' : '';
                $rVal['[note-productionStyle]'] = ($UseSandbox == 'Y') ? 'style="display: none;"' : '';
                break;

            default:
                
        }

        // Return the Extra Content Data
        return $rVal;
    }

    /**
     * Function Returns the Appropriate .html Content File Required for a
     *      given sPage Value.
     */
    private function _getReqFileName() {
        $rVal = '';

        if ( YNBool($this->settings['isLoggedIn']) ) {
	        $FileName = '/content-' . strtolower(NoNull($this->settings['spage'])) . '.html';
        } else {
	        $FileName = '/content-login.html';
        }

        // Load the Appropriate File
        if ( file_exists( RES_DIR . $FileName ) ) {
            $rVal = $FileName;
        } else {
            $rVal = '/content-landing.html';
        }

        // Return the Required FileName
        return $rVal;        
    }

    /**
     * Function returns the Administration Panel. Should the file be older than the Cache limit,
     *		or a fresh one is requested, the menu will be rebuilt and saved accordingly.
     */
    private function _getNavigationMenu( $forceUpdate = false) {
        $rVal = "";

        if ( YNBool($this->settings['isLoggedIn']) ) {
            $pages = array('dashboard'	=> array('icon' 	=> "icon-home",
            									 'current'	=> "N",
            									 'label'	=> $this->messages['lblDashboard'] ),
            			   'sites'		=> array('icon' 	=> "icon-pencil",
            									 'current'	=> "N",
            									 'label'	=> $this->messages['lblSites'],
            									 'subs'		=> array('0' => $this->messages['lblDefault']) ),
            			   'users'		=> array('icon' 	=> "icon-user",
            									 'current'	=> "N",
            									 'label'	=> $this->messages['lblUsers'] ),
            			   'settings'	=> array('icon' 	=> "icon-cogs",
            									 'current'	=> "N",
            									 'label'	=> $this->messages['lblSettings'] ),
            			   );

            foreach ( $pages as $url=>$dtl ) {
            	$FullURL = $this->settings['HomeURL'] . '/' . $this->settings['mpage'] . '/' . $url;
            	$SubList = '';
            	$isCurrent = '';
            	if ( array_key_exists('subs', $dtl) ) {
            		$SubList = "<ul>";
	            	foreach ( $dtl['subs'] as $subUrl=>$subDtl ) {
		            	$SubList .= '<li><a href="' . $FullURL . '?siteID=' . $subUrl . '"><span class="nav-icon icon-pencil"></span> ' . $subDtl . '</a></li>';
	            	}
	            	$SubList .= "</ul>";
            	}
            	$isCurrent = '';
            	if ( $this->settings['spage'] == $url ) {
	            	$isCurrent = ' class="current_menu_item"';
            	}
            	$rVal .= '<li' . $isCurrent . '><a href="' . $FullURL . '"><span class="nav-icon ' . $dtl['icon'] . '"></span> ' . $dtl['label'] . '</a>' . $SubList . '</li>';
	        }
        }

        // Return the Administration Navigation Menu
        return $rVal;
    }

}
?>