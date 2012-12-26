<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Configuration File (J2fi)
 */
define('DB_SERV', '');                         			// Database Server
define('DB_USER', '');                            		// Database Login
define('DB_PASS', '');                          		// Database Password
define('DB_MAIN', '');                               	// Database Name (Primary)
define('DB_CHARSET', 'utf8');                           // The Default Character Set
define('DB_COLLATE', 'UTF8_UNICODE_CI');                // The Default Database Collation
define('DB_TYPE', 2);									// The Storage Method (0 - API / 1 - MySQL / 2 - NSW)

define('APP_ROOT', '/');                                // The Application Root Location
define('APP_NAME', 'Noteworthy');                       // The Application Name
define('APP_VER', '1.0.0 (2012.10)');                   // The Application Version
define('CACHE_EXPY', 3600);								// Number of Seconds Cache Files Can Survive
define('COOKIE_EXPY', 3600);                            // Number of Seconds Mortal Cookies Live For
define('SHA_SALT', 'nwSiteWith5');                      // Salt Value used with SHA1 Encryption

define('DEFAULT_LANG', 'EN');                           // Default Language Code
define('GENERATOR', 'Midori Lite 2.2.0');               // Generator Name
define('DEBUG_ENABLED', 0);                             // Debug Mode (0 - Disabled | 1 - Enabled)

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
