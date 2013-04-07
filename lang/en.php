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

                      'rss_copyright'   => "Copyright &copy; " . date('Y'),
                      'lblDisqusEnJS'	=> "Please enable JavaScript to view the <a href=\"http://disqus.com/?ref_noscript\">comments powered by Disqus.",
                      'lblFeedTitle'	=> "Subscribe via RSS",

                      'lblPoweredBy'	=> "Powered by Noteworthy",
                      'lblAllRights'	=> "All Rights Reserved",
                      'lblTop'			=> "Return to Top",
                      'lblDashboard'	=> "Dashboard",
                      'lblSites'		=> "Sites",
                      'lblDefault'		=> "Default",
                      'lblAbout'		=> "About",
                      'lblLinks'		=> "Links",
                      'lblSettings'		=> "Settings",
                      'lblCronOn'		=> "Updating DB",
                      'lblCronOff'		=> "Cron Off",
                      'lblLogout'		=> "Logout",
                      
                      'lblLoginSub'		=> "Login to Continue",
                      'lblEmailAddr'	=> "Email Address",
                      'lblRememberMe'	=> "Remember Me",
                      'lblLostPass'		=> "Lost your password?",
                      'lblRetrieval'	=> "Retrieve It",
                      
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
                      
                      'lblMailSettings'	=> "Email Settings",
                      'lblMailEnable'	=> "Enable Email?",
                      'lblMailEnableB'	=> "Only Required If You Have a Contact Form",
                      'lblMailServ'		=> "SMTP Server",
                      'lblMailSecSMTP'	=> "Secure SMTP?",
                      'lblMailPort'		=> "Mail Port",
                      'lblMailPortB'	=> "Usually Something Like 25, 465, 587, or 995",
                      'lblMailUser'		=> "User Name",
                      'lblMailPass'		=> "Mail Password",
                      'lblMailSendTo'	=> "Send All Messages To This Address",
                      'lblMailSendToB'	=> "Your Personal Address (Or the place where you want any mail sent to)",
                      'lblMailReply'	=> "Show <i>this</i> Return Email Address",
                      'lblMailReplyB'	=> "The Email Addres People Will Reply To (If They Choose)",
                      'lblMailTest'		=> "Send Test Message",
                      'lblMailTestBody' => "This is a test message from Noteworthy. If you're reading it, your Email is configured properly.",
                      'lblMailRepName'  => "Site Message",
                      'lblMailMsgFrom'  => "Message From",
                      'lblMailReminder'	=> "Send Reminder",
                      
                      'lblDBTitle'		=> "Databases",
                      'lblDBReason'		=> "<p>Noteworthy currently makes use of a MySQL database which is useful for such things as search, Twitter results, and pagination. Noteworthy is not 100% dependant on a database, though, as a lot of important bits of data are stored in serialized files. What this means is that if you're using a less reliable MySQL server, people will still be able to see your site so long as the data is already cached locally.</p>",
                      'lblDebugTitle'	=> "Debug Mode",
                      'lblDebugReason'	=> "<p>Debug Mode will enable recording of activity logs through a number of Noteworthy's functions to the /logs/ directory. These logs are not visible here in the Administration screens at this time, but are fully readable if you access them directly on the server. Hopefully you won't need to use this function unless something is really, really wrong.</p>",
                      'lblEmailTitle'	=> "Email",
                      'lblEmailReason'	=> "<p>Please note that the <i>sendmail</i> function is not used, which means it will be necessary to fill out the SMTP server information for your particular email account. This is because a number of hosted servers disable sendmail or are blacklisted by Email services as being spam. To get around this, you can create a quick GMail, Hotmail, or Yahoo! account for this website. The most common settings for these free services are a click away in the right-hand panel.</p><p>Rather than use your personal email address, it may make more sense to use something like <i>web@[EMAIL_STUB]</i> to protect your privacy.</p>",
                      'lblRemindTitle'	=> "Admin Link Reminders",
                      'lblRemindReason' => "<p>One of the unique features of Noteworthy is the fact that every installation has a different URL. This makes it much more difficult for bots or other bad people to get access to these screens. The problem, however, is that if you forget this URL, you can't get back in, either. One way to prevent this from happening is to send yourself a reminder email. Click the button below to send a message to the address you provided above.</p>",

                      'lblTwitter'		=> "Tweet Importing",
                      'lblTwitterUse'	=> "Twitter Archives",
                      'lblTwitName'		=> "Your Twitter Name",
                      'lblTwitDescr'	=> "Importing Tweets",
                      'lblTwitReason'	=> "By importing your Twitter Timeline, you'll have a local copy of your Tweets which can be used in the search queries. The first time you activate this feature, your past Tweets (up to 3,000 of them) will be read into the database.",

                      'lblTags'			=> "Tagged Under",
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

                      'lblBaseInfo'		=> "Base Information",

                      'lblTxtHomeURL'	=> "Site URL",
                      'lblTxtSiteName'	=> "Site Name",
                      'lblTxtSiteDescr'	=> "Site Description",
                      'lblTxtSEOTags'	=> "Site SEO Key Words",
                      'lblTxtSEOTagsB'	=> "(Comma Separated)",
                      'lblChkDefault'	=> "Default Landing Site",
                      'lblChkDefaultB'	=> "(Redirects people going to invalid domains or sub-domains to this site)",

                      'lblComments'		=> "Comments",
                      'lblSpamFilter'	=> "Spam Filtering",
                      'lblAkismetKey'	=> "Akismet Key",
                      'lblDisqusID'		=> "Disqus ID",

                      'lblStyling'		=> "Styling",
                      'lblStylingB'		=> "Choose Your Theme",
                      'lblUpdateHndl'	=> "Update Handling",
                      'lblUpdateHndlB'	=> "This is how updates will be done on your site. If you choose \"Disabled\", then you will need to set up a cron job on your server. If you're not sure what this means, or you don't have access to crontab, then click \"Enabled\". This will make the site check for updates a visitor comes to the site. This will <i>not</i> affect your server load whatsoever. You can <a href=\"#cron\" title=\"Cron Jobs on Noteworthy\"><b>read more about Cron here</b></a>.",
                      'lblStatsChk'     => "Statistics",
                      'lblStatsChkB'	=> "Collect basic statistics about how many people come to your website. You can see how many visitors come daily, what websites they're coming from, and what browsers they use to see your content.<br /><em>Note:</em> There is a slight performance cost when using this function. Your database may also fill up very quickly if you're not careful. Be sure to regularly check your server if you don't have a lot of storage space.",
                      'lblStatsDtl'     => "Quick Statistics",
                      'lblStatsDtlA'    => "In The Last 24 Hours You've Had ...",
                      'lblStatsDtlB'    => "These stats are updated every five minutes to reduce server load. Don't be alarmed if the numbers don't update with each page load.",
                      'lblPageView'     => "Page Views",
                      'lblVisitors'     => "Visitors",
                      'lblPopPages'     => "Popular Pages",

                      'lblSocialLinks'	=> "Social Links",
                      'lblSocialLinksB'	=> "Let People See You",
                      'lblSocialLinksC'	=> "Here you can set some links people can use to follow you elsewhere on the Internet.",
                      'lblSocialName'	=> "Display Name",
                      'lblSocialLink'	=> "Link",
                      'lblSocialActive'	=> "Active",

                      'lblSetUpdGood'	=> "Successfully Updated Settings",
                      'lblSetUpdErr001'	=> "One or More Invalid MySQL Settings",
                      'lblSetUpdErr002'	=> "Could Not Save Configuration Data",
                      'lblSetUpdErr003'	=> "Database Already Exists",
                      'lblSetUpdErr004'	=> "Could Not Populate Database",

                      'lblCron001'		=> "Checking Twitter for Updates",
                      'lblCron002'		=> "Checking Evernote for Updates",
                      'lblCron003'		=> "Finishing Up",
                      'lblCron004'		=> "Updating Cache Files",

                      'lblInvalidFunc'	=> "Invalid Function",
                      'lblUnknownErr'	=> "An Odd Error Has Occurred",
                      'lblSuccess'		=> "Success!",
                      'lblEmailSent'	=> "The Message Has Been Successfully Sent",

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