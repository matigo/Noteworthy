<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the Japanese Label Strings for use in Nozomi Study
 * 
 * Change Log
 * ----------
 * 2012.01.17 - Created Class (J2fi)
 */
require_once( LIB_DIR . '/functions.php' );

class lang_ja implements lang_base {
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
        $rVal = array('404'				=> "あなたはここではない何かを探しています。",
                      'und_const'		=> "このページは工事中です",
                      'deprecated'		=> "このページは、推奨されていません。<br>\n" . 
                                           "あなたはこのサイトを復元する必要がある場合サポートに問い合わせてください。",

                      'footer_msg'		=> "Powered by " . APP_NAME,
                      'all_rights'		=> "すべての権利予約",
                      'copyright'		=> "コピーライト",

                      'lang_name'		=> "日本語",
                      'lang_cd'			=> "JA",

                      'rss_copyright'   => "",

                      'lblPoweredBy'	=> "Noteworthyアトス",
                      'lblAllRights'	=> "All Rights Reserved",
                      'lblTop'			=> "トップへ戻る",
                      'lblDashboard'	=> "ダッシュボード",
                      'lblSites'		=> "サイト",
                      'lblDefault'		=> "デフォルト",
                      'lblUsers'		=> "ユーザー",
                      'lblSettings'		=> "設定",
                      'lblLogout'		=> "ログアウト",

                      'lblComment'		=> "コメントをお書く",
                      'lblTags'			=> "タグ",

                      'lblMonth01'      => "１月",
                      'lblMonth02'      => "２月",
                      'lblMonth03'      => "３月",
                      'lblMonth04'      => "３月",
                      'lblMonth05'      => "５月",
                      'lblMonth06'      => "６月",
                      'lblMonth07'      => "７月",
                      'lblMonth08'      => "８月",
                      'lblMonth09'      => "９月",
                      'lblMonth10'      => "１０月",
                      'lblMonth11'      => "１１月",
                      'lblMonth12'      => "１２月",
                      'lblShowArchives'	=> "アーカイブ",
                      'lblShowAllPosts' => "すべての[NUM]のブログ記事を表示",
                      'lblTitleArchive'	=> "[NUM]のブログ記事",

                      'admin_lblLogo'	=> APP_NAME,
                      'admin_strLogo'	=> APP_NAME . "を帰る",
                      'admin_lblSub'	=> "ただ自分自身である",
                      );

        // Add any Custom Labels that are Required
        if ( count($Custom) > 0 ) {
            foreach( $Custom as $key=>$val ) {
                $rVal[ "[$key]" ] = $val;
            }
        }

        // Return the Completed Array
        return $rVal;
    }
}

?>