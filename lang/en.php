<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the Label Strings for use in Noteworthy
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Class (J2fi)
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

                      'footer_msg'      => "Powered by " . APP_NAME,
                      'all_rights'      => "All Rights Reserved",
                      'copyright'       => "Copyright &copy;",

                      'lang_name'       => "English",
                      'lang_cd'         => "EN",

                      'rss_copyright'   => "",
                      
                      'lblPoweredBy'	=> "Powered by Noteworthy",
                      'lblAllRights'	=> "All Rights Reserved",
                      'lblTop'			=> "Return to Top",
                      'lblDashboard'	=> "Dashboard",
                      'lblSites'		=> "Sites",
                      'lblDefault'		=> "Default",
                      'lblUsers'		=> "Users",
                      'lblSettings'		=> "Settings",
                      'lblLogout'		=> "Logout",

                      'lblTags'			=> "Tagged",
                      'lblLinks'		=> "Links",
                      'lblComment'		=> "Leave a Comment",
                      'lblArchives'		=> "Archives",
                      'lblShowArchives'	=> "Show Archives",
                      'lblShowAllPosts' => "Show All [NUM] Posts",
                      'lblTitleArchive'	=> "[NUM] Posts",

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