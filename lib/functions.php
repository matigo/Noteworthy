<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * This is the Central Functions File that will be used throughout the
 *  Noteworthy Application, including Themes and Plugins
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Adapted File from PhotoStore 2.0 (J2fi)
 */
require_once(LIB_DIR . '/globals.php');

    /**
     * Function returns the Site Details based on the Site Requested
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    function _getSiteDetails( $Site = '' ) {
        require_once(CONF_DIR . '/sites/sites.php');

        $rVal = array();
        $SiteURL = cleanURL( ($Site == '') ? $_SERVER['SERVER_NAME'] : $Site );
        $ConfigFile = '';
        
        // Check to See if the Settings File Exists
        if ( file_exists(CONF_DIR . '/sites/' . $SiteURL . '.php') ) {
            $ConfigFile = CONF_DIR . '/sites/' . $SiteURL . '.php';
        } else {
            $ConfigFile = CONF_DIR . '/sites/default.php';
        }

        require_once( $ConfigFile );
        $SiteInfo = new site();
        $SiteData = $SiteInfo->getSettings();
        if ( $SiteData ) {
            $rVal = array();
            foreach ( $SiteData as $Key=>$Val ) {
                $rVal[ $Key ] = NoNull( $Val );
            }
        }
        unset( $SiteData );

        // Return the Array of Site Details
        return $rVal;
    }

    /**
     * Function determines which group a user agent belongs to and
     *      returns a classification (PC/Smart/Mobile)
     * 
     * Change Log
     * ----------
     * 2011.04.22 - Created Function (J2fi)
     */
    function getUserAgentGroup() {
        $rVal = array('Type' => 'PC', 
                      'Device' => 'Default' );
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $accept = $_SERVER['HTTP_ACCEPT'];

        switch(true){
            case (preg_match('/ipad/i', $user_agent));
                $rVal = array('Type' => 'PC', 
                              'Device' => 'iPad' );
                break;

            case (preg_match('/ipod/i', $user_agent) || preg_match('/iphone/i', $user_agent));
                $rVal = array('Type' => 'Smart', 'Device' => 'iPhone' );
                break;

            case (preg_match('/android/i', $user_agent));
                $rVal = array('Type' => 'Smart', 
                              'Device' => 'Android' );
                break;

            case (preg_match('/opera mini/i', $user_agent));
                $rVal = array('Type' => 'Mobile', 
                              'Device' => 'Mobile' );
                break;

            case (preg_match('/blackberry/i', $user_agent));
                $rVal = array('Type' => 'Smart', 
                              'Device' => 'Blackberry' );
                break;

            case (preg_match('/(pre\/|palm os|palm|hiptop|avantgo|plucker|xiino|blazer|elaine)/i', $user_agent));
                $rVal = array('Type' => 'Mobile', 
                              'Device' => 'PalmOS' );
                break;

            case (preg_match('/(iris|3g_t|windows ce|opera mobi|windows ce; smartphone;|windows ce; iemobile)/i', $user_agent));
                $rVal = array('Type' => 'Smart', 
                              'Device' => 'Windows Mobile' );
                break;

            case (preg_match('/(mini 9.5|vx1000|lge |m800|e860|u940|ux840|compal|wireless| mobi|ahong|lg380|lgku|lgu900|lg210|lg47|lg920|lg840|lg370|sam-r|mg50|s55|g83|t66|vx400|mk99|d615|d763|el370|sl900|mp500|samu3|samu4|vx10|xda_|samu5|samu6|samu7|samu9|a615|b832|m881|s920|n210|s700|c-810|_h797|mob-x|sk16d|848b|mowser|s580|r800|471x|v120|rim8|c500foma:|160x|x160|480x|x640|t503|w839|i250|sprint|w398samr810|m5252|c7100|mt126|x225|s5330|s820|htil-g1|fly v71|s302|-x113|novarra|k610i|-three|8325rc|8352rc|sanyo|vx54|c888|nx250|n120|mtk |c5588|s710|t880|c5005|i;458x|p404i|s210|c5100|teleca|s940|c500|s590|foma|samsu|vx8|vx9|a1000|_mms|myx|a700|gu1100|bc831|e300|ems100|me701|me702m-three|sd588|s800|8325rc|ac831|mw200|brew |d88|htc\/|htc_touch|355x|m50|km100|d736|p-9521|telco|sl74|ktouch|m4u\/|me702|8325rc|kddi|phone|lg |sonyericsson|samsung|240x|x320|vx10|nokia|sony cmd|motorola|up.browser|up.link|mmp|symbian|smartphone|midp|wap|vodafone|o2|pocket|kindle|mobile|psp|treo)/i', $user_agent));
                $rVal = array('Type' => 'Mobile', 
                              'Device' => 'Mobile' );
                break;

            case ( (strpos($accept, 'text/vnd.wap.wml') > 0) || (strpos($accept, 'application/vnd.wap.xhtml+xml') > 0) );
                $rVal = array('Type' => 'Mobile', 
                              'Device' => 'WAP Device' );
                break;

            case ( isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']) );
                $rVal = array('Type' => 'Mobile', 
                              'Device' => 'WAP Device' );
                break;

            // Check against a list of trimmed User Agents to see if we find a match
            case ( in_array(strtolower(substr($user_agent,0,4)), array('1207'=>'1207',      '3gso'=>'3gso',     '4thp'=>'4thp',
                                                                       '501i'=>'501i',      '502i'=>'502i',     '503i'=>'503i',
                                                                       '504i'=>'504i',      '505i'=>'505i',     '506i'=>'506i',
                                                                       '6310'=>'6310',      '6590'=>'6590',     '770s'=>'770s',
                                                                       '802s'=>'802s',      'a wa'=>'a wa',     'acer'=>'acer',
                                                                       'acs-'=>'acs-',      'airn'=>'airn',     'alav'=>'alav',
                                                                       'asus'=>'asus',      'attw'=>'attw',     'au-m'=>'au-m',
                                                                       'aur '=>'aur ',      'aus '=>'aus ',     'abac'=>'abac',
                                                                       'acoo'=>'acoo',      'aiko'=>'aiko',     'alco'=>'alco',
                                                                       'alca'=>'alca',      'amoi'=>'amoi',     'anex'=>'anex',
                                                                       'anny'=>'anny',      'anyw'=>'anyw',     'aptu'=>'aptu',
                                                                       'arch'=>'arch',      'argo'=>'argo',     'bell'=>'bell',
                                                                       'bird'=>'bird',      'bw-n'=>'bw-n',     'bw-u'=>'bw-u',
                                                                       'beck'=>'beck',      'benq'=>'benq',     'bilb'=>'bilb',
                                                                       'blac'=>'blac',      'c55/'=>'c55/',     'cdm-'=>'cdm-',
                                                                       'chtm'=>'chtm',      'capi'=>'capi',     'cond'=>'cond',
                                                                       'craw'=>'craw',      'dall'=>'dall',     'dbte'=>'dbte',
                                                                       'dc-s'=>'dc-s',      'dica'=>'dica',     'ds-d'=>'ds-d',
                                                                       'ds12'=>'ds12',      'dait'=>'dait',     'devi'=>'devi',
                                                                       'dmob'=>'dmob',      'doco'=>'doco',     'dopo'=>'dopo',
                                                                       'el49'=>'el49',      'erk0'=>'erk0',     'esl8'=>'esl8',
                                                                       'ez40'=>'ez40',      'ez60'=>'ez60',     'ez70'=>'ez70',
                                                                       'ezos'=>'ezos',      'ezze'=>'ezze',     'elai'=>'elai',
                                                                       'emul'=>'emul',      'eric'=>'eric',     'ezwa'=>'ezwa',
                                                                       'fake'=>'fake',      'fly-'=>'fly-',     'fly_'=>'fly_',
                                                                       'g-mo'=>'g-mo',      'g1 u'=>'g1 u',     'g560'=>'g560',
                                                                       'gf-5'=>'gf-5',      'grun'=>'grun',     'gene'=>'gene',
                                                                       'go.w'=>'go.w',      'good'=>'good',     'grad'=>'grad',
                                                                       'hcit'=>'hcit',      'hd-m'=>'hd-m',     'hd-p'=>'hd-p',
                                                                       'hd-t'=>'hd-t',      'hei-'=>'hei-',     'hp i'=>'hp i',
                                                                       'hpip'=>'hpip',      'hs-c'=>'hs-c',     'htc '=>'htc ',
                                                                       'htc-'=>'htc-',      'htca'=>'htca',     'htcg'=>'htcg',
                                                                       'htcp'=>'htcp',      'htcs'=>'htcs',     'htct'=>'htct',
                                                                       'htc_'=>'htc_',      'haie'=>'haie',     'hita'=>'hita',
                                                                       'huaw'=>'huaw',      'hutc'=>'hutc',     'i-20'=>'i-20',
                                                                       'i-go'=>'i-go',      'i-ma'=>'i-ma',     'i230'=>'i230',
                                                                       'iac'=>'iac',        'iac-'=>'iac-',     'iac/'=>'iac/',
                                                                       'ig01'=>'ig01',      'im1k'=>'im1k',     'inno'=>'inno',
                                                                       'iris'=>'iris',      'jata'=>'jata',     'java'=>'java',
                                                                       'kddi'=>'kddi',      'kgt'=>'kgt',       'kgt/'=>'kgt/',
                                                                       'kpt '=>'kpt ',      'kwc-'=>'kwc-',     'klon'=>'klon',
                                                                       'lexi'=>'lexi',      'lg g'=>'lg g',     'lg-a'=>'lg-a',
                                                                       'lg-b'=>'lg-b',      'lg-c'=>'lg-c',     'lg-d'=>'lg-d',
                                                                       'lg-f'=>'lg-f',      'lg-g'=>'lg-g',     'lg-k'=>'lg-k',
                                                                       'lg-l'=>'lg-l',      'lg-m'=>'lg-m',     'lg-o'=>'lg-o',
                                                                       'lg-p'=>'lg-p',      'lg-s'=>'lg-s',     'lg-t'=>'lg-t',
                                                                       'lg-u'=>'lg-u',      'lg-w'=>'lg-w',     'lg/k'=>'lg/k',
                                                                       'lg/l'=>'lg/l',      'lg/u'=>'lg/u',     'lg50'=>'lg50',
                                                                       'lg54'=>'lg54',      'lge-'=>'lge-',     'lge/'=>'lge/',
                                                                       'lynx'=>'lynx',      'leno'=>'leno',     'm1-w'=>'m1-w',
                                                                       'm3ga'=>'m3ga',      'm50/'=>'m50/',     'maui'=>'maui',
                                                                       'mc01'=>'mc01',      'mc21'=>'mc21',     'mcca'=>'mcca',
                                                                       'medi'=>'medi',      'meri'=>'meri',     'mio8'=>'mio8',
                                                                       'mioa'=>'mioa',      'mo01'=>'mo01',     'mo02'=>'mo02',
                                                                       'mode'=>'mode',      'modo'=>'modo',     'mot '=>'mot ',
                                                                       'mot-'=>'mot-',      'mt50'=>'mt50',     'mtp1'=>'mtp1',
                                                                       'mtv '=>'mtv ',      'mate'=>'mate',     'maxo'=>'maxo',
                                                                       'merc'=>'merc',      'mits'=>'mits',     'mobi'=>'mobi',
                                                                       'motv'=>'motv',      'mozz'=>'mozz',     'n100'=>'n100',
                                                                       'n101'=>'n101',      'n102'=>'n102',     'n202'=>'n202',
                                                                       'n203'=>'n203',      'n300'=>'n300',     'n302'=>'n302',
                                                                       'n500'=>'n500',      'n502'=>'n502',     'n505'=>'n505',
                                                                       'n700'=>'n700',      'n701'=>'n701',     'n710'=>'n710',
                                                                       'nec-'=>'nec-',      'nem-'=>'nem-',     'newg'=>'newg',
                                                                       'neon'=>'neon',      'netf'=>'netf',     'noki'=>'noki',
                                                                       'nzph'=>'nzph',      'o2 x'=>'o2 x',     'o2-x'=>'o2-x',
                                                                       'opwv'=>'opwv',      'owg1'=>'owg1',     'opti'=>'opti',
                                                                       'oran'=>'oran',      'p800'=>'p800',     'pand'=>'pand',
                                                                       'pg-1'=>'pg-1',      'pg-2'=>'pg-2',     'pg-3'=>'pg-3',
                                                                       'pg-6'=>'pg-6',      'pg-8'=>'pg-8',     'pg-c'=>'pg-c',
                                                                       'pg13'=>'pg13',      'phil'=>'phil',     'pn-2'=>'pn-2',
                                                                       'pt-g'=>'pt-g',      'palm'=>'palm',     'pana'=>'pana',
                                                                       'pire'=>'pire',      'pock'=>'pock',     'pose'=>'pose',
                                                                       'psio'=>'psio',      'qa-a'=>'qa-a',     'qc-2'=>'qc-2',
                                                                       'qc-3'=>'qc-3',      'qc-5'=>'qc-5',     'qc-7'=>'qc-7',
                                                                       'qc07'=>'qc07',      'qc12'=>'qc12',     'qc21'=>'qc21',
                                                                       'qc32'=>'qc32',      'qc60'=>'qc60',     'qci-'=>'qci-',
                                                                       'qwap'=>'qwap',      'qtek'=>'qtek',     'r380'=>'r380',
                                                                       'r600'=>'r600',      'raks'=>'raks',     'rim9'=>'rim9',
                                                                       'rove'=>'rove',      's55/'=>'s55/',     'sage'=>'sage',
                                                                       'sams'=>'sams',      'sc01'=>'sc01',     'sch-'=>'sch-',
                                                                       'scp-'=>'scp-',      'sdk/'=>'sdk/',     'se47'=>'se47',
                                                                       'sec-'=>'sec-',      'sec0'=>'sec0',     'sec1'=>'sec1',
                                                                       'semc'=>'semc',      'sgh-'=>'sgh-',     'shar'=>'shar',
                                                                       'sie-'=>'sie-',      'sk-0'=>'sk-0',     'sl45'=>'sl45',
                                                                       'slid'=>'slid',      'smb3'=>'smb3',     'smt5'=>'smt5',
                                                                       'sp01'=>'sp01',      'sph-'=>'sph-',     'spv '=>'spv ',
                                                                       'spv-'=>'spv-',      'sy01'=>'sy01',     'samm'=>'samm',
                                                                       'sany'=>'sany',      'sava'=>'sava',     'scoo'=>'scoo',
                                                                       'send'=>'send',      'siem'=>'siem',     'smar'=>'smar',
                                                                       'smit'=>'smit',      'soft'=>'soft',     'sony'=>'sony',
                                                                       't-mo'=>'t-mo',      't218'=>'t218',     't250'=>'t250',
                                                                       't600'=>'t600',      't610'=>'t610',     't618'=>'t618',
                                                                       'tcl-'=>'tcl-',      'tdg-'=>'tdg-',     'telm'=>'telm',
                                                                       'tim-'=>'tim-',      'ts70'=>'ts70',     'tsm-'=>'tsm-',
                                                                       'tsm3'=>'tsm3',      'tsm5'=>'tsm5',     'tx-9'=>'tx-9',
                                                                       'tagt'=>'tagt',      'talk'=>'talk',     'teli'=>'teli',
                                                                       'topl'=>'topl',      'hiba'=>'hiba',     'up.b'=>'up.b',
                                                                       'upg1'=>'upg1',      'utst'=>'utst',     'v400'=>'v400',
                                                                       'v750'=>'v750',      'veri'=>'veri',     'vk-v'=>'vk-v',
                                                                       'vk40'=>'vk40',      'vk50'=>'vk50',     'vk52'=>'vk52',
                                                                       'vk53'=>'vk53',      'vm40'=>'vm40',     'vx98'=>'vx98',
                                                                       'virg'=>'virg',      'vite'=>'vite',     'voda'=>'voda',
                                                                       'vulc'=>'vulc',      'w3c '=>'w3c ',     'w3c-'=>'w3c-',
                                                                       'wapj'=>'wapj',      'wapp'=>'wapp',     'wapu'=>'wapu',
                                                                       'wapm'=>'wapm',      'wig '=>'wig ',     'wapi'=>'wapi',
                                                                       'wapr'=>'wapr',      'wapv'=>'wapv',     'wapy'=>'wapy',
                                                                       'wapa'=>'wapa',      'waps'=>'waps',     'wapt'=>'wapt',
                                                                       'winc'=>'winc',      'winw'=>'winw',     'wonu'=>'wonu',
                                                                       'x700'=>'x700',      'xda2'=>'xda2',     'xdag'=>'xdag',
                                                                       'yas-'=>'yas-',      'your'=>'your',     'zte-'=>'zte-',
                                                                       'zeto'=>'zeto',      'acs-'=>'acs-',     'alav'=>'alav',
                                                                       'alca'=>'alca',      'amoi'=>'amoi',     'aste'=>'aste',
                                                                       'audi'=>'audi',      'avan'=>'avan',     'benq'=>'benq',
                                                                       'bird'=>'bird',      'blac'=>'blac',     'blaz'=>'blaz',
                                                                       'brew'=>'brew',      'brvw'=>'brvw',     'bumb'=>'bumb',
                                                                       'ccwa'=>'ccwa',      'cell'=>'cell',     'cldc'=>'cldc',
                                                                       'cmd-'=>'cmd-',      'dang'=>'dang',     'doco'=>'doco',
                                                                       'eml2'=>'eml2',      'eric'=>'eric',     'fetc'=>'fetc',
                                                                       'hipt'=>'hipt',      'http'=>'http',     'ibro'=>'ibro',
                                                                       'idea'=>'idea',      'ikom'=>'ikom',     'inno'=>'inno',
                                                                       'ipaq'=>'ipaq',      'jbro'=>'jbro',     'jemu'=>'jemu',
                                                                       'java'=>'java',      'jigs'=>'jigs',     'kddi'=>'kddi',
                                                                       'keji'=>'keji',      'kyoc'=>'kyoc',     'kyok'=>'kyok',
                                                                       'leno'=>'leno',      'lg-c'=>'lg-c',     'lg-d'=>'lg-d',
                                                                       'lg-g'=>'lg-g',      'lge-'=>'lge-',     'libw'=>'libw',
                                                                       'm-cr'=>'m-cr',      'maui'=>'maui',     'maxo'=>'maxo',
                                                                       'midp'=>'midp',      'mits'=>'mits',     'mmef'=>'mmef',
                                                                       'mobi'=>'mobi',      'mot-'=>'mot-',     'moto'=>'moto',
                                                                       'mwbp'=>'mwbp',      'mywa'=>'mywa',     'nec-'=>'nec-',
                                                                       'newt'=>'newt',      'nok6'=>'nok6',     'noki'=>'noki',
                                                                       'o2im'=>'o2im',      'opwv'=>'opwv',     'palm'=>'palm',
                                                                       'pana'=>'pana',      'pant'=>'pant',     'pdxg'=>'pdxg',
                                                                       'phil'=>'phil',      'play'=>'play',     'pluc'=>'pluc',
                                                                       'port'=>'port',      'prox'=>'prox',     'qtek'=>'qtek',
                                                                       'qwap'=>'qwap',      'rozo'=>'rozo',     'sage'=>'sage',
                                                                       'sama'=>'sama',      'sams'=>'sams',     'sany'=>'sany',
                                                                       'sch-'=>'sch-',      'sec-'=>'sec-',     'send'=>'send',
                                                                       'seri'=>'seri',      'sgh-'=>'sgh-',     'shar'=>'shar',
                                                                       'sie-'=>'sie-',      'siem'=>'siem',     'smal'=>'smal',
                                                                       'smar'=>'smar',      'sony'=>'sony',     'sph-'=>'sph-',
                                                                       'symb'=>'symb',      't-mo'=>'t-mo',     'teli'=>'teli',
                                                                       'tim-'=>'tim-',      'tosh'=>'tosh',     'treo'=>'treo',
                                                                       'tsm-'=>'tsm-',      'upg1'=>'upg1',     'upsi'=>'upsi',
                                                                       'vk-v'=>'vk-v',      'voda'=>'voda',     'vx52'=>'vx52',
                                                                       'vx53'=>'vx53',      'vx60'=>'vx60',     'vx61'=>'vx61',
                                                                       'vx70'=>'vx70',      'vx80'=>'vx80',     'vx81'=>'vx81',
                                                                       'vx83'=>'vx83',      'vx85'=>'vx85',     'wap-'=>'wap-',
                                                                       'wapa'=>'wapa',      'wapi'=>'wapi',     'wapp'=>'wapp',
                                                                       'wapr'=>'wapr',      'webc'=>'webc',     'whit'=>'whit',
                                                                       'winw'=>'winw',      'wmlb'=>'wmlb',     'xda-'=>'xda-',
                                                                    )));
                $rVal = array('Type' => 'Mobile', 
                              'Device' => 'Mobile' );
                break;

			case (preg_match("/^DoCoMo\/(.*)/i", $user_agent));
				$rVal = array('Type' => 'Mobile', 
								'Device' => 'WAP Device' );
				break;
		
			case (preg_match("/^KDDI-(\w*) UP\.Browser/i", $user_agent));
				$rVal = array('Type' => 'Mobile', 
								'Device' => 'WAP Device' );
				break;
					
			case (preg_match("/^(?:J-PHONE|Vodafone|SoftBank)\/[0-9.]*\/([-\w]*)/i", $user_agent));
				$rVal = array('Type' => 'Mobile', 
								'Device' => 'WAP Device' );
				break;

            default;
                $rVal = array('Type' => 'PC', 
                              'Device' => 'Full Capacity' );
                break;
        } 

        // Return the Type and Device
        return $rVal;
    }

    /**
     * Function returns a requested Number of spaces
     *  Example: tabSpace(1)
     *  Returns: "  "
     * 
     * Change Log
     * ----------
     * 2011.01.29 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2011.03.22 - Created Function (J2fi)
     */
    function getMicroTime() {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        
        // Return the Time
        return $time;
    }
    
    /**
     * Function Identifies UserNames and Replaces the Name with a proper URL
     * 
     * Change Log
     * ----------
     * 2012.04.22 - Created Function (J2fi)
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
     *
     * Change Log
     * ----------
     * 2012.11.27 - Created Function (J2fi)
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
     * Function Returns the Amount of Time that has passed since $UnixTime
     * 
     * Change Log
     * ----------
     * 2012.04.22 - Created Function (J2fi)
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
     * Function returns a random string of X Length
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    function getRandomString( $Length = 10 ) {
        $rVal = "";
        $nextChar = "";

        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
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
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
	function startsWith($haystack, $needle) {
    	return !strncmp($haystack, $needle, strlen($needle));
    }

    /**
     * Function Determines if String "Ends With" the supplied String
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
	function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if ($length == 0) { return true; }

		return (substr($haystack, -$length) === $needle);
	}

    /**
     * Function Confirms a directory exists and makes one if it doesn't
     *      before returning a Boolean
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
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
     *
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2012.04.17 - Created Function (J2fi)
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
     * Function returns a person's IPv4 address
     * 
     * Change Log
     * ----------
     * 2011.04.19 - Created Function (J2fi)
     */
    function getVisitorIPv4() {
        $rVal = "";

        if (isset( $_SERVER['HTTP_X_FORWARDED_FOR'] )) {
            $rVal = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $rVal = $_SERVER['REMOTE_ADDR'];
        }

        // Return the Visitor's IPv4 Address
        return trim($rVal);
    }

    /**
     * Function constructs and returns a Google Analytics Code Snippit
     * 
     * Change Log
     * ----------
     * 2011.04.25 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2012.02.02 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2012.02.02 - Created Function (J2fi)
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
     * Function parses the HTTP Header to extract just the Response code
     * 
     * Change Log
     * ----------
     * 2011.04.28 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2011.04.28 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2011.04.26 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2011.03.04 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2011.03.01 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2012.03.24 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2011.04.05 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2011.03.17 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2011.04.19 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2011.01.31 - Created Function (J2fi)
     */
    function doSQLQuery( $sqlStr, $UseDB = DB_MAIN ) {
        $rVal = array();
        $r = 0;

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
     * 
     * Change Log
     * ----------
     * 2011.01.31 - Created Function (J2fi)
     */
    function doSQLExecute( $sqlStr, $UseDB = DB_MAIN ) {
        $rVal = -1;

        $GLOBALS['Perf']['queries']++;
        $db = mysql_connect(DB_SERV, DB_USER, DB_PASS);
        $selected = mysql_select_db($UseDB, $db);
        mysql_query("SET NAMES " . DB_CHARSET);
        mysql_query($sqlStr);
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
     * 
     * Change Log
     * ----------
     * 2011.12.11 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2010.11.04 - Created Function (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2010.11.04 - Altered Function for use in PhoSho (J2fi)
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
     * 
     * Change Log
     * ----------
     * 2010.11.04 - Altered Function for use in PhoSho (J2fi)
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
     *  API Functions
     ***********************************************************************/
    /**
     * Function sends a POST Request to the Midori API and returns an array
     *      of data
     * 
     * Change Log
     * ----------
     * 2011.04.28 - Created Function (J2fi)
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
        /*
        return array(
            'status' => 'ok',
            'header' => $header,
            'content' => $content
        );
        */
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
     * 
     * Change Log
     * ----------
     * 2012.03.03 - Created Function (J2fi)
     */
    function apiFilteredItems( $Items ) {
        $rVal = array();
        $filter = array('mpage', 'spage', 'DispPg', 'pftheme', 
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
     *  Temporary Information Store
     ***********************************************************************/
    /**
     * Function Saves a Setting with a Specific Token to the Temp Directory
     */
    function saveSetting( $token, $key, $value ) {
    	$settings = array();

	    // Check to see if the Settings File Exists or Not
	    if ( checkDIRExists( TMP_DIR ) ) {
		    $tmpFile = TMP_DIR . '/' . $token;
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
	    $tmpFile = TMP_DIR . '/' . $token;
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
	    $tmpFile = TMP_DIR . '/' . $token;
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
	    $tmpFile = TMP_DIR . '/' . $token;
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

    /***********************************************************************
     *  Debug & Error Reporting Functions
     ***********************************************************************/
    /**
     * Function records a note to the File System when DEBUG_ENABLED > 0
     *		Note: Timezone is currently set to Asia/Tokyo, but this should
     *			  be updated to follow the user's time zone.
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
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
     * Function returns the current MicroTime Value
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    function formatErrorMessage( $Location, $Message ) {
    	writeNote( "{$Location} - $Message", false );
        return "$Message";
    }

?>