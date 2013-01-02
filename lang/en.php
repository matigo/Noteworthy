<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the English Label Strings for use in Noteworthy
 */

class lang_en implements lang_base {
    var $labels;

    function __construct( $Custom = array() ) {
        $this->labels = $this->_fillLabels( $Custom );
    }

    public function getLangCd() {
        return $this->labels['lang_cd'];
    }

    public function getLangName() {
        return $this->labels['lang_name'];
    }

    public function getStrings() {
        return $this->labels;
    }

    private function _fillLabels( $Custom ) {
        $rVal = array('404'             => "You are looking for something that isn't here.",
                      'und_const'       => "This Page is Under Construction",
                      'deprecated'      => "This Page has been Deprecated.<br>\n Please Contact Support if you need this site restored.",
                      'serverError'     => "A critical error has occurred. We're terribly sorry for this and are working on a fix.",
                      
                      'SiteName'		=> "A New Noteworthy Site",

                      'footer_msg'      => "Powered by " . APP_NAME,
                      'all_rights'      => "All Rights Reserved",
                      'copyright'       => "Copyright &copy;",

                      'lang_name'       => "English",
                      'lang_cd'         => "EN",

                      'rss_copyright'   => "",
                      'lblDisqusEnJS'	=> "Please enable JavaScript to view the <a href=\"http://disqus.com/?ref_noscript\">comments powered by Disqus.",
                      
                      'lblPoweredBy'	=> "Powered by Noteworthy",
                      'lblAllRights'	=> "All Rights Reserved",
                      'lblTop'			=> "Return to Top",
                      'lblDashboard'	=> "Dashboard",
                      'lblSites'		=> "Sites",
                      'lblDefault'		=> "Default",
                      'lblAbout'		=> "About",
                      'lblLinks'		=> "Links",
                      'lblSettings'		=> "Settings",
                      'lblLogout'		=> "Logout",
                      
                      'lblDebugMode'	=> "Debug Mode",
                      'lblEnabled'		=> "Enabled",
                      'lblDisabled'		=> "Disabled",
                      'lblSaveChanges'	=> "Save Changes",
                      'lblTest'			=> "Test",
                      
                      'lblID'			=> "ID",
                      'lblTitle'		=> "Title",
                      'lblPostDTS'		=> "Post Date",
                      'lblUpdateDTS'	=> "Updated",
                      'lblMetas'		=> "Metas",
                      
                      'lblDataSets'		=> "Data Storage Settings",
                      'lblDataType'		=> "Data Storage Type",
                      'lblDBServ'		=> "Database Server",
                      'lblDBName'		=> "Database Name",
                      'lblDBUser'		=> "Database Login",
                      'lblDBPass'		=> "Database Password",
                      'lblDBMySQL'		=> "MySQL Database",
                      'lblDBNone'		=> "No Database",
                      
                      'lblTwitter'		=> "Tweet Importing",
                      'lblTwitterUse'	=> "Twitter Archives",
                      'lblTwitName'		=> "Your Twitter Name",
                      'lblTwitDescr'	=> "Importing Tweets",
                      'lblTwitReason'	=> "By importing your Twitter Timeline, you'll have a local copy of your Tweets which can be used in the search queries. The first time you activate this feature, your past Tweets (up to 3,000 of them) will be read into the database.",

                      'lblTags'			=> "Tagged",
                      'lblPublished'	=> "Published On",
                      'lblLinks'		=> "Links",
                      'lblComment'		=> "Leave a Comment",
                      'lblArchives'		=> "Archives",
                      'lblShowArchives'	=> "Show Archives",
                      'lblShowAllPosts' => "Show All [NUM] Posts",
                      'lblTitleArchive'	=> "[NUM] Posts",

                      'lblSearchResult'	=> "Search Results",

                      'lblMonth01'      => "January",
                      'lblMonth02'      => "February",
                      'lblMonth03'      => "March",
                      'lblMonth04'      => "April",
                      'lblMonth05'      => "May",
                      'lblMonth06'      => "June",
                      'lblMonth07'      => "July",
                      'lblMonth08'      => "August",
                      'lblMonth09'      => "September",
                      'lblMonth10'      => "October",
                      'lblMonth11'      => "November",
                      'lblMonth12'      => "December",
                      'lblYearSuffix'	=> "",
                      
                      'lblSetUpdGood'	=> "Successfully Updated Settings",
                      'lblSetUpdErr001'	=> "Invalid MySQL Settings",
                      'lblSetUpdErr002'	=> "Could Not Save Configuration Data",
                      'lblSetUpdErr003'	=> "Database Already Exists",

                      'admin_lblLogo'	=> APP_NAME,
                      'admin_strLogo'	=> APP_NAME . " | Sharing Your Ideas With the World",
                      'admin_lblSub'	=> "Sharing Your Ideas with the World",
                      
                      );

        // Add any Custom Labels that are Required
        if ( count($Custom) > 0 ) {
            foreach( $Custom as $key=>$val ) {
                $rVal[ "$key" ] = $val;
            }
        }

        // Return the Completed Array
        return $rVal;
    }
}

?>