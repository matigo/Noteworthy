<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 */
define('APP_ROOT', '/');                                // The Application Root Location
define('APP_NAME', 'Noteworthy');                       // The Application Name
define('APP_VER', '0.9.0 (2013.01)');                   // The Application Version
define('CACHE_EXPY', 3600);								// Number of Seconds Cache Files Can Survive
define('COOKIE_EXPY', 3600);                            // Number of Seconds Mortal Cookies Live For
define('SHA_SALT', 'nwSiteWith5');                      // Salt Value used with SHA1 Encryption

define('DEFAULT_LANG', 'EN');                           // Default Language Code
define('GENERATOR', 'Midori Lite 2.2.0');               // Generator Name

if( !defined('DB_TYPE') ) {
	define('DB_TYPE', 2);								// State the Database Type (If Not Already Defined)
}
if( !defined('DEBUG_ENABLED') ) {
	define('DEBUG_ENABLED', 0);							// Set the Debug Level (If Not Already Defined)
}

?>
