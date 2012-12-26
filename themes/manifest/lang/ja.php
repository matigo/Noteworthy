<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the Japanese language strings for use in the Fresh Theme
 * 
 * Change Log
 * ----------
 * 2012.03.17 - Created Class (J2fi)
 */
require_once( LIB_DIR . '/functions.php' );

class theme_ja implements lang_base {
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
        $rVal = array('lang_name'       => "日本語",
                      'lang_cd'         => "JA",

                      'logo_name'       => APP_NAME,
                      'logo_title'      => APP_NAME . "を帰る",
                      'logo_subtitle'   => "ただ自分自身である",
                      
                      'lblUserPrefix'   => "こんにちは、",
                      'lblUserSuffix'   => "さん！",

                      'lblEnableJS'     => "Please Enable JavaScript to Use English Road",

                      'lblLogin'        => "ログイン",
                      'lblSignIn'       => "サインイン",
                      'lblSignUp'       => "サインアップ",
                      'lblSignOut'      => "サインアウト",
                      'lblRegister'     => "会員登録",
                      'lblHowTo'        => "このサイトの趣旨",
                      'lblSitemap'      => "Sitemap",
                      'lblMessage'      => "Message",
                      'lblFAQ'          => "Q&amp;A",
                      'lblAbout'        => "About Us",
                      'lblBlog'         => "Blog",

                      'lblHome'         => "Home",
                      'lblSample'       => "Sample",
                      'lblLevelChk'     => "Level Check",
                      'lblMyPage'       => "My Page",
                      'lblLink'         => "リンク",

                      'strTicker'       => "工事中　　 近日中　　　公開",

                      'lblChartWord'    => "単語",
                      'lblChartPhrase'  => "熟語",
                      'lblChartIdioms'  => "イディオム",
                      'lblChartSyntax'  => "構文",
                      'lblChartGrammar' => "文法",

                      'ttl_about'       => "About Us",
                      'ttl_blog'        => "Blog",
                      'ttl_faq'         => "Q&amp;A",
                      'ttl_levelcheck'  => "Level Check",
                      'ttl_login'       => "ログイン",
                      'ttl_message'     => "このサイトの趣旨",
                      'ttl_mypage'      => "My Page",
                      'ttl_sample'      => "Sample",
                      'ttl_signup'      => "サインアップ",
                      'ttl_sitemap'     => "Site Map",
                      'ttl_verify'      => "Verify",

                      /* *************************************************** *
                       *    Login Page
                       * *************************************************** */
                      'lblLoginName'    => "ユーザ名かメールアドレス",
                      'lblLoginBegin'   => "Please Login To Use English Road",
                      'lblNeedAcct'     => "Don't Have An Account?",
                      'lblNeedAcctLink' => "Create One Here!",

                      /* *************************************************** *
                       *    Sign Up Page
                       * *************************************************** */
                      'lblStepOne'      => "Step 1",
                      'lblStepOneDescr' => "アカウント情報",
                      'lblName'         => "お名前",
                      'lblPrefecture'   => "都道府県",
                      'lblAge'          => "年齢",
                      'lblGender'       => "性別",
                      'lblGenderChoose' => "選択",
                      'lblGenderM'      => "男",
                      'lblGenderF'      => "女",
                      'lblEmail'        => "メールアドレス",
                      
                      'lblStepTwo'      => "Step 2",
                      'lblStepTwoDescr' => "アカウントのユーザー名とパスワードを選択してください",
                      'lblUsername'     => "ユーザ名",
                      'dtlUsername'     => "あなたのユーザー名は文字、数字、アンダースコア、およびスペースを含めることができます。",
                      'lblPassword'     => "パスワード",
                      'lblPassConf'     => "パスワードを確認",
                      'dtlPassword'     => "パスワードは少なくとも" . number_format(MIN_PASS_LENGTH) . "文字の長さでなければなりません。スペースは許可されていません。",     
                      'dtlPassConf'     => "ちょうどあなたがそれを正しく入力したことを確認します。",
                      'lblSignMeUp'     => "サインアップ！",
                      'dtlSignMeUp'     => "ボタンをクリックすると完了です",

                      /* *************************************************** *
                       *    Landing Page Content (Left)
                       * *************************************************** */
                      'lblVocabCheck'   => "語彙チェック",
                      'lblProbSolve'    => "問題を解く",
                      'lblReadFeedback' => "解説を読む",
                      'lblSmallTest'    => "小テストを受ける",
                      'lblChkResults'   => "成績を確認",
                      'lblReview'       => "復習する",

                      'lblFeatures'     => "English Roadの特徴",
                      'lblConcerns'     => "英語の悩みWORST10",
                      'lblConcern01'    => "単語が覚えられない",
                      'lblConcern02'    => "自分の伸びがわからない",
                      'lblConcern03'    => "難しい!(何がわからないのかわからない)",
                      'lblConcern04'    => "長文が苦手",
                      'lblConcern05'    => "和訳・英訳が苦手",
                      'lblConcern06'    => "時間がない",
                      'lblConcern07'    => "実力テストは実力で受けるべき。特別な勉強はしない!が点数は気になる",
                      'lblConcern08'    => "苦手・得意項目にバラツキがある",
                      'lblConcern09'    => "自分のレベルに合う教材がわからない、問題集を使いこなせない",
                      'lblConcern10'    => "どう復習すればいいかわからない",
                      'numConcern01'    => "1位",
                      'numConcern02'    => "2位",
                      'numConcern03'    => "3位",
                      'numConcern04'    => "4位",
                      'numConcern05'    => "5位",
                      'numConcern06'    => "6位",
                      'numConcern07'    => "7位",
                      'numConcern08'    => "8位",
                      'numConcern09'    => "9位",
                      'numConcern10'    => "10位",

                      /* *************************************************** *
                       *    Landing Page Content (Central)
                       * *************************************************** */
                      'lblTangoKyoka'   => "単語強化",
                      'lblReberuBetsu'  => "レベル別",
                      'lblHinshiBetsu'  => "品詞別",
                      'lblBunpuKyoka'   => "文法強化",
                      'lblKomokuBetsu'  => "項目別",
                      'lblKobunKyoka'   => "構文強化",
                      'lblOther'        => "その他",
                      
                      'lblIdioms'       => "イディオム",
                      'lblPhrase'       => "熟語",
                      'lblKanyoKyogen'  => "慣用表現",
                      'lblKaiwaHyogen'  => "会話表現",
                      
                      'lblListening'    => "リスニング",
                      'lblReadLoud'     => "音読",
                      
                      'lblPromo1Span'   => "オンライン学習",
                      'lblPromo1P'      => "携帯やパソコンで受講OK",
                      'lblPromo2Span'   => "自分のレベルを学習",
                      'lblPromo2P'      => "様々なレベルを繰り返しわかるまで学習でき自分の必要なところのみ強化学習できる",
                      'lblPromo3Span'   => "生きた英語",
                      'lblPromo3P'      => "ネイティブと開発した独自の学習法でレベルアップ",

                      /* *************************************************** *
                       *    Sample Page Content
                       * *************************************************** */
                      'lblSample1Title' => "単語は覚えたら使うこと!!",
                      'lblSample1Body'  => "単語の使い方を例文で学び、どんなシチュエーションで用いるのかを理解しよう。まずはネイティブスピーカーのマネから始め、使いこなすまでのレベルにもっていこう。",
                      'lblSample1Link'  => "単語のサンプルはこちら",
                      'lblSample1Btn'   => "単語強化",

                      'lblSample2Title' => "自分で解説できるまで解く",
                      'lblSample2Body'  => "解答・解説を読んで理解しただけでなく、自分で同じように理由の裏付けを言葉に出せるまで徹底してやること。どうしてその答えになるか、本文に戻り、「答えの裏付け」をすることで、確実に答えに導く。",
                      'lblSample2Link'  => "文法のサンプルはこちら",
                      'lblSample2Btn'   => "文法強化",

                      'lblSample3Title' => "何度も復習する",
                      'lblSample3Body'  => "English Roadでは、My Pageにて、小テストの結果、グラフ、自分だけの単語・文法・構文リストを作成できる。携帯やパソコンなどで復習を繰り返そう。<br />復習した記録は残され、復習が少ないレッスンは、メールでお知らせ。",
                      'lblSample3Link'  => "My Pageのサンプルはこちら",
                      'lblSample3Btn'   => "復習",

                      /* *************************************************** *
                       *    Q&A Page Content
                       * *************************************************** */
                      'lblQAQuestion01' => "English Roadはどんな人向けですか?",
                      'lblQAQuestion02' => "どのレベルから始めたらいいかわかりません。",
                      'lblQAQuestion03' => "教材は必要ですか?",
                      'lblQAQuestion04' => "個人情報の管理は?",
                      'lblQAQuestion05' => "パスワードを忘れてしまいました",
                      'lblQAQuestion06' => "ログインできません",
                      'lblQAQuestion07' => "どうしこんなに安いのですか?",
                      'lblQAQuestion08' => "クレジットカードは使用できますか?",
                      'lblQAQuestion09' => "支払はどのようにするのですか?",
                      'lblQAQuestion10' => "メンバーの有効期限は?",
                      'lblQAQuestion11' => "退会したいのですが。",
                      'lblQAQuestion12' => "払い戻しは可能ですか。",
                      'lblQAQuestion13' => "団体で利用できますか?",
                      'lblQAQuestion14' => "その他の質問",
                      'lblQAQuestion15' => "",
                      
                      'lblQAAnswer01'   => "英語の単元ごとに勉強したい人、単語をなかなか覚えられない人、携帯やパソコンで勉強したい人にお勧めです。",
                      'lblQAAnswer02'   => "まずはレベルチェックを受けてみましょう。もしくはLesson1の問題(無料)を解いてみて、だいたいのレベルを確認しましょう。繰り返し勉強することが大切なので、Lessonごとのどのレベルもトライしてみるといいと思います。",
                      'lblQAAnswer03'   => "必要ありません。気軽にパソコンや携帯などで勉強できるのが特徴です。",
                      'lblQAAnswer04'   => "本サイトではE-mailアドレス、性別、年齢、パスワードにてログインできます。",
                      'lblQAAnswer05'   => "",
                      'lblQAAnswer06'   => "",
                      'lblQAAnswer07'   => "本サイトは広告、教材の配送などをしていないため、価格を抑えてあります。",
                      'lblQAAnswer08'   => "使用できません。コンビニでの支払いになります。",
                      'lblQAAnswer09'   => "いただいたE-mailアドレスにコンビニ支払いの情報をお送りしますので、お近くのコンビニにてお支払をお願いいたします。",
                      'lblQAAnswer10'   => "全コース(全Lessonコース)を購入された方は3年間、Lessonごとに購入される方は一つのLessonにつき1年間の有効期限があります。",
                      'lblQAAnswer11'   => "",
                      'lblQAAnswer12'   => "品質上、いったんご購入いただいた場合は払い戻しは致しかねます。心配な方は、Lesson1を受けていただく、またはLessonごとのご購入をお勧めします。",
                      'lblQAAnswer13'   => "現在、団体でのご利用は致しかねます。塾関係者はこちらへ",
                      'lblQAAnswer14'   => "",
                      'lblQAAnswer15'   => "",
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