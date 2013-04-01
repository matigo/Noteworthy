<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 */
define('APP_ROOT', '/');					// The Application Root Location
define('APP_NAME', 'Noteworthy');			// The Application Name
define('APP_VER', '13D002 (2013.04)');		// The Application Version
define('CACHE_EXPY', 3600);					// Number of Seconds Cache Files Can Survive
define('COOKIE_EXPY', 3600);				// Number of Seconds Mortal Cookies Live For
define('SHA_SALT', 'nwSiteWith5');			// Salt Value used with SHA1 Encryption
define('GENERATOR', 'Midori Lite 2.2.0');	// Generator Name

define('ENABLE_MULTILANG', 0);				// Enables Multi-Language Support
define('ANALYTICS_ENABLED', 0);				// Enables the Google Analytics Suffix

if( !defined('DEFAULT_LANG') ) {
	define('DEFAULT_LANG', 'EN');			// The Default Application Language (If Not Already Defined)
}
if( !defined('DB_TYPE') ) {
	define('DB_TYPE', 2);					// State the Database Type (If Not Already Defined)
}
if ( !defined('DB_VER') ) {
	define('DB_VER', 4);					// Database Version
}
if( !defined('DEBUG_ENABLED') ) {
	define('DEBUG_ENABLED', 0);				// Set the Debug Level (If Not Already Defined)
}

?>
