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

/**
 * Evernote API Settings
 *
 * This only ever needs to be configured once, regardless of how many sites you're running. Every account will
 *	have different keys, but Noteworthy only ever needs one.
 *
 * Notes: OAUTH_CONSUMER_KEY - Your Key from Evernote
 *		  OAUTH_CONSUMER_SECRET - Your Consumer Secret from Evernote
 *		  EVERNOTE_SERVER - (Dev) https://sandbox.evernote.com
 *							(Live) https://www.evernote.com
 *		  NOTESTORE_HOST -	(Dev) sandbox.evernote.com
 *							(Live) www.evernote.com
 *		  NOTESTORE_PORT 	 - Don't Change This
 *		  NOTESTORE_PROTOCOL - Don't Change This
 *		  REQUEST_TOKEN_URL  - Don't Change This
 *		  ACCESS_TOKEN_URL   - Don't Change This
 *		  AUTHORIZATION_URL  - Don't Change This
 */
define('ENABLE_EVERNOTE', 0);				// Evernote ON (0 - Disabled | 1 - Enabled)
define('OAUTH_CONSUMER_KEY', '');
define('OAUTH_CONSUMER_SECRET', '');
define('EVERNOTE_SERVER', 'https://sandbox.evernote.com');
define('NOTESTORE_HOST', 'sandbox.evernote.com');
define('NOTESTORE_PORT', '443');
define('NOTESTORE_PROTOCOL', 'https');
define('REQUEST_TOKEN_URL', EVERNOTE_SERVER . '/oauth');
define('ACCESS_TOKEN_URL', EVERNOTE_SERVER . '/oauth');
define('AUTHORIZATION_URL', EVERNOTE_SERVER . '/OAuth.action');  

?>
