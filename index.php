<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * This is the main index file for the Noteworthy Software
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created File (J2fi)
 */
define('BASE_DIR', dirname(__FILE__));
DEFINE('CONTENT_DIR', BASE_DIR . '/content');
define('TOKEN_DIR', BASE_DIR . '/tokens');
define('THEME_DIR', BASE_DIR . '/themes');
define('USERS_DIR', BASE_DIR . '/users');
define('CONF_DIR', BASE_DIR . '/conf');
define('LANG_DIR', BASE_DIR . '/lang');
define('LOG_DIR', BASE_DIR . '/logs');
define('LIB_DIR', BASE_DIR . '/lib');
define('TMP_DIR', BASE_DIR . '/tmp');
require_once(LIB_DIR . '/main.php');

error_reporting(E_ERROR | E_PARSE);
mb_internal_encoding("UTF-8");

// Set the default time zone
date_default_timezone_set('Asia/Tokyo'); 

$Perf = array('app_s'   => 0,
              'app_f'   => 0,
              'apiHits' => 0,
              'caches'	=> 0,
              'queries' => 0
              );
$miSite = new Midori;
echo $miSite->load_page();

?>