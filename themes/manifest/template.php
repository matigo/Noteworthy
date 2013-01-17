<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for the Manifest theme
 */
require_once(dirname(__FILE__) . '/conf/settings.php');
require_once(LIB_DIR . '/functions.php');

class theme_main implements themes {
    var $settings;

    function __construct( $data ) {
        $this->settings = $data;
    }

    public function _getData() {
        $html_out = "A critical error has occurred. We're terribly sorry for this and are working on a fix.";
        $PgRoot = '';
        if ( array_key_exists('PgRoot', $this->settings) ) {
	        $PgRoot = strtolower(NoNull($this->settings['PgRoot']));
        }

        switch ( $PgRoot ) {
            case 'atom':
            case 'rss':
                $html_out = $this->_getRSS();
                break;
            
            default:
                $html_out = $this->_getHTML();
        }

        // Return the HTML Data
        return $html_out;
    }

    public function _getOverride() {
        $rVal = 'Override ...';

        // Return the Override Link
        return $rVal;
    }

    /***********************************************************************
     *                          Internal Functions
     *
     *   The following code should only be called by the above functions
     ***********************************************************************/
    private function _getHTML() {
        $rVal = "";

        $MainTheme = THEME_DIR . '/manifest/desktop.php';
        if ( file_exists($MainTheme) ) {
            require_once( $MainTheme );
            $html = new miTheme( $this->settings );

            $rVal = $html->getHeader() .
                    $html->getContent() .
                    $html->getSuffix();   
        }

        // Return the HTML Formatted String
        return $rVal;
    }

    private function _getRSS() {
        $PostCount = 25;

        $postData = apiRequest( "content/read", array('Show' => 'rss', 'Posts' => $PostCount) );
        $rVal = $this->_parseAtom( $postData->data );

        // Return the XML Formatted String
        return $rVal;
    }

    private function _parseAtom( $postData ) {
        require_once( LIB_DIR . '/content.php' );
        $ConObj = new Content( $this->settings, dirname(__FILE__) );
        $atomBody = "";
        $rVal = "";

        $inCache = $ConObj->getContent("rssXML", 120);
        if ( $inCache ) {
            $rVal = $inCache;
        } else {
        $rVal = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" .
                    "<feed xmlns=\"http://www.w3.org/2005/Atom\" xmlns:georss=\"http://www.georss.org/georss\">\n" .
                    tabSpace(1) . "<title type=\"text\">[site_name]</title>\n" .
                    tabSpace(1) . "<subtitle type=\"html\">[site_descr]</subtitle>\n" .
                    tabSpace(1) . "<updated>[lastUpdate]</updated>\n" .
                    tabSpace(1) . "<id>urn:uuid:00aa6c80-0099-1199-99CC-000333666999</id>\n" .
                    tabSpace(1) . "<link rel=\"alternate\" type=\"text/html\" hreflang=\"" . $this->settings['LangCd'] . "\" href=\"" . $this->settings['HomeURL'] . "/\"/>\n" .
                    tabSpace(1) . "<link rel=\"self\" type=\"application/atom+xml\" href=\"" . $this->settings['HomeURL'] . "/atom/\"/>\n" .
                    tabSpace(1) . "<rights>Copyright (c) [lblMyName]</rights>\n" .
                    tabSpace(1) . "<generator uri=\"" . $this->settings['HomeURL'] . "\" version=\"1.0\">Noteworthy</generator>\n" .
                    "[atomBody]" .
                    "</feed>";
            if ( !is_array($postData) ) { $postData = objectToArray( $postData ); }
            $Messages = $this->_getMessageStrings();
            $lastUpdate = "";
    
            foreach( $postData as $Key=>$Post ) {
                $content = "";
                $CreatedUTC = gmDate("Y-m-d\TH:i:s\Z", strtotime($Post['CreateDTS']));
                $UpdatedUTC = gmDate("Y-m-d\TH:i:s\Z", strtotime($Post['UpdateDTS']));
                if ( $lastUpdate == "" ) {
                    $lastUpdate = $CreatedUTC;
                }
    
                // Clean up the body for Atom
                foreach(preg_split("/(\r?\n)/", $Post['Value']) as $line){
                    if ( NoNull($line) ) {
                        $content .= NoNull($line);
                    }
                }
                // Append the Footnotes if they exist
                if ( NoNull($Post['POST-FOOTER']) ) {    
                    $content .= "<hr/>" . $Post['POST-FOOTER'];
                }
                // Append the Copyright / TOS if it exists
                $content .= $this->_getRSSCopyright( $Messages );
    
                $atomBody .= tabSpace(2) . "<entry>\n" .
                             tabSpace(3) . "<title>" . NoNull($Post['Title']) . "</title>\n" .
                             tabSpace(3) . "<link href=\"[HOMEURL]" . $Post['POST-URL'] . "\"/>\n" .
                              $enclosure .
                             tabSpace(3) . "<id>urn:uuid:" . NoNull($Post['guid']) . "</id>\n" .
                             tabSpace(3) . "<updated>$UpdatedUTC</updated>\n" .
                             tabSpace(3) . "<published>$CreatedUTC</published>\n" .
                                 $geotag .
                             tabSpace(3) . "<author>\n" .
                             tabSpace(4) . "<name>" . NoNull($Post['POST-AUTHOR'], "Jason F. Irwin") . "</name>\n" .
                             tabSpace(4) . "<uri>[HOMEURL]/</uri>\n" .
                             tabSpace(3) . "</author>\n" .
                             tabSpace(3) . "<content type=\"xhtml\" xml:lang=\"[lang_cd]\" xml:base=\"[HOMEURL]/\">\n" .
                             tabSpace(4) . "<div xmlns=\"http://www.w3.org/1999/xhtml\">\n" .
                             tabSpace(5) . $content . "\n" .
                             tabSpace(4) . "</div>\n" .
                             tabSpace(3) . "</content>\n" .
                             tabSpace(2) . "</entry>\n";
            }

            // Insert the Appropriate Data into the Atom Element
            $rVal = str_replace('[lastUpdate]', $lastUpdate, $rVal);
            $rVal = str_replace('[atomBody]', $atomBody, $rVal);
            $rVal = str_replace('[HOMEURL]', $this->settings['HomeURL'], $rVal);
            $rVal = str_replace('[lang_cd]', $Messages['lang_cd'], $rVal);
            $rVal = str_replace('[site_name]', $Messages['site_name'], $rVal);
            $rVal = str_replace('[site_descr]', $Messages['site_descr'], $rVal);
            $rVal = str_replace('[lblMyName]', $Messages['lblMyName'], $rVal);
            $rVal = str_replace('<p><p', '<p', $rVal);
            $rVal = str_replace('</p></p>', '</p>', $rVal);

            // Save the Data to Cache
            $ConObj->saveContent("rssXML", $rVal);
        }

        // Return the XML Object
        return $rVal;
    }

    /**
     * Function Returns the RSS Copyright Message if a message has been entered
     *      in the language definition file.
     * 
     * Change Log
     * ----------
     * 2012.05.03 - Created Function (J2fi)
     */    
    private function _getRSSCopyright( $Messages ) {
        $rVal = "";

        if ( NoNull($Messages['rss_copyright']) != "" ) {
            $CopyYear = date("Y");
            $rVal = "<hr/>" . $Messages['copyright'] . " 2003 - $CopyYear " . 
                    "<strong><a href=\"" . $this->settings['HomeURL'] . "\">" . $Messages['rss_copyright'] . "</a></strong>";
        }

        // Return the Copyright String
        return $rVal;
    }

    private function _getMessageStrings() {
        $rVal = getLangDefaults( $this->settings['DispLang'] );
        
        // Load the User-Specified Language Files for this theme
        $LangFile = dirname(__FILE__) . "/lang/" . strtolower($this->settings['DispLang']) . ".php";

        if ( file_exists($LangFile) ){
            require_once( $LangFile );
            $LangClass = 'theme_' . strtolower( $this->settings['DispLang'] );
            $Lang = new $LangClass();

            // Append the List of Strings to the End of the Messages Array
            //      and replace any existing ones that may need the update
            foreach( $Lang->getStrings() as $Key=>$Val ) {
                $rVal[ $Key ] = $Val;
            }

            // Kill the Class
            unset( $Lang );
        }

        // Return the Array of Language Items
        return $rVal;
    }
}

?>
