<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for User Data
 */
require_once(LIB_DIR . '/functions.php');

class User extends Midori {
    var $settings;
    var $errors;

    function __construct( $Token = '' ) {
        $this->settings = $this->_populateClass();
        $this->errors = array();

        // Collect any information that might be missing
        if ( $this->_fillUserClass($Token) ) {
	        
        }
    }

    /***********************************************************************
     *  Public Functions
     ***********************************************************************/
    /**
     * Function returns a Boolean value representing whether a person is logged
     *      in or not.
     */
    public function isLoggedIn() {
        return YNBool( $this->settings['isLoggedIn'] );
    }

    // Return the Administration Code (Used for the Administration Panel)
    public function AdminCode() {
        return $this->settings['adminCode'];
    }

    // Return the User.ID Value (If Applicable)
    public function UserID() {
	    return nullInt($this->settings['id']);
    }

    public function doLogout() {
	    return $this->_logoutUser();
    }

    public function EmailAddr( $Value = '' ) {
	    $rVal = '';

	    if ( $Value != '' ) {
		    // Set the Email Address
	    } else {
		    $rVal = NoNull($this->settings['email']);
	    }
	    
	    // Return the Appropriate Response
	    return $rVal;
	}

    /**
     * Function sets or gets the Person's Display Name and Returns the appropriate data.
     */
    public function DisplayName( $Value = '' ) {
	    $rVal = NoNull($this->settings['DisplayName']);
	    
	    if ( $rVal == "" ) {
	    	// Use the Information from Evernote
	    	$data = readSetting('core', '*');
		    $rVal = NoNull($data['name'], $data['username']);
	    }

	    // Return the Appropriate Response
	    return $rVal;
    }

    /**
     * Function tests an Email / AdminCode Combination and returns a Boolean Response
     */
    public function authAccount( $Email, $AdminCode, $Token ) {
        $rVal = array('isGood' => false,
        			  'redir'  => '',
        			  'ErrMsg' => '',
        			  );
        $isLoggedIn = false;

        // Basic Validation
        if ( NoNull($Email) == "" || NoNull($AdminCode) == "" ) {
        	$rVal['ErrMsg'] = "Failed Basic Validation";
        	return $rVal;
        }
        if ( !checkDIRExists(USERS_DIR) ) { return $rVal; }

        // If No Token Accounts Exist, This is the First Visit. Create a User.
        if ( countDIRFiles(TOKEN_DIR) == 0 ) {
	        $newToken = $this->createAccount( $Email, $AdminCode, $Token );
	        if ( $newToken == "" ) { $rVal['ErrMsg'] = "Could Not Create Record"; }
	        $AdminCode = $newToken;
	        $rVal['redir'] = $newToken;
	        writeNote( "Created Account [Good: " . BoolYN($isLoggedIn) . "] - email: $Email AdminCode: $AdminCode Token: $Token" );

		    // Ensure the InstallDone Setting is Complete
		    $instDone = readSetting('core', 'installDone');
		    if ( $instDone != 'Y' ) {
			    saveSetting('core', 'installDone', 'Y');
		    }
        }

        // Check to See if the AdminCode is Valid
        $AccountID = alphaToInt($AdminCode);
        if ( $AccountID > 0 ) {
	        $UsersFile = $this->_getUserFile( $Email );
	        if ( file_exists($UsersFile) ) {
		        $data = file_get_contents( $UsersFile );
		        $lots = unserialize( $data );

		        // Add Some Account-Specific Information
		        $this->settings['id'] = $AccountID;
		        $this->settings['isLoggedIn'] = BoolYN($isLoggedIn);

	            foreach ( $lots as $key=>$val ) {
			    	$this->settings[ $key ] = NoNull($val);
	            }

	            // Create a Token Record
	            $TokenFile = TOKEN_DIR . '/' . $Token . '.token';
	            $TokenData = array( 'email' => $Email );
	            if ( file_put_contents($TokenFile, serialize($TokenData)) ) {
		            $isLoggedIn = true;
	            } else {
		            $rVal['ErrMsg'] = "Could Not Write Token Record";
	            }
	        } else {
		        $rVal['ErrMsg'] = "Could Not Find User File: $UsersFile";
	        }
        } else {
	        $rVal['ErrMsg'] = "Invalid AccountID: $AccountID | AdminCode: $AdminCode";
        }

        // Set the Return Value
        $rVal['isGood'] = BoolYN($isLoggedIn);

        // Return the Token Response or an Unhappy Boolean
        return $rVal;
    }

    /**
     * Function creates a new User Account and Returns a Boolean Response
     */
    public function createAccount( $Email, $AdminCode, $Token ) {
        $rVal = "";

        // Do Some Basic Error Checking
        if ( NoNull($Email) == "" || NoNull($AdminCode) == "" || NoNull($Token) == "" ) { return $rVal; }

        // Ensure the AdminCode is just words, and not "install"
        $AdminCode = preg_match('/^[\w\d]$/', $AdminCode);
        if ( $AdminCode == 'install' ) {
	        $AdminCode = getRandomString(8);
	        $rVal = $AdminCode;
        }

        // Create the User File if the Admin Code is Valid
        $AccountID = alphaToInt($AdminCode);
        if ( $AccountID > 0 ) {
	        $UsersFile = $this->_getUserFile( $Email );
	        if ( !file_exists($UsersFile) ) {
		        $data = array('id'			=> $AccountID,
		        			  'adminCode'	=> $AdminCode,
		        			  'Created'		=> time(),
		        			  'email'		=> NoNull($Email),
		        			  'isLoggedIn'	=> 'Y',
		        			  );

	            // Write the Record
	            file_put_contents($UsersFile, serialize($data));
	        }
        }

        // Return the Admin Code
        return $rVal;
    }

    /***********************************************************************
     *  Private Functions
     ***********************************************************************/
    /**
     * Function builds the base variables that will be used throughout the
     *      application
     */
    private function _populateClass() {
        $rVal = array( 'id'          => 0,
                       'DisplayName' => '',
                       'Created'     => '',
                       'email'		 => '',
                       'adminCode'	 => '',
                       'isLoggedIn'  => 'N',
                      );

        // Return the Base Array
        return $rVal;
    }
    
    private function _getUserFromToken( $Token ) {
	    $rVal = false;
        if ( $Token == '' ) { return $rVal; }
        if ( !checkDIRExists(TOKEN_DIR) ) { return $rVal; }

        // Search the Token Directory for the Appropriate Record
        $TokenFile = TOKEN_DIR . '/' . $Token . '.token';
        if ( file_exists($TokenFile) ) {
	        $data = file_get_contents( $TokenFile );
            $user = unserialize( $data );

            // Read the Email Address
            $rVal = $user['email'];
        }

	    // Return the UserName (Email Address)
	    return $rVal;
    }
    
    private function _setUserToken( $Token, $Email ) {
	    $rVal = false;
        if ( $Token == '' ) { return $rVal; }
        if ( !checkDIRExists(TOKEN_DIR) ) { return $rVal; }

        $TokenFile = TOKEN_DIR . '/' . $Token . '.token';
        $data = array('email' => $Email );

        // Write the Record
        if ( file_put_contents($TokenFile, serialize($data)) ) {
            $rVal = true;
        }

        // Return the Boolean Response
        return $rVal;
    }

    /**
     * Function retrieves any missing data for the User Class from a Token
     *      Record and Returns a Boolean Response
     * 
     * Note: This may not be used long-term
     */
    private function _fillUserClass( $Token ) {
        $rVal = false;
        if ( $Token == '' ) { return $rVal; }
        if ( !checkDIRExists(TOKEN_DIR) ) { return $rVal; }

        // Search the Token Directory for the Appropriate Record
        $TokenFile = TOKEN_DIR . '/' . $Token . '.token';
        if ( file_exists($TokenFile) ) {
	        $data = file_get_contents( $TokenFile );
            $user = unserialize( $data );

            // Read the User Data from the Token Value
            if ( $user['email'] != "" ) {
	            $rVal = $this->_readUserData( $user['email'] );
            }
        }

        // Return a Boolean
        return $rVal;
    }
    
    private function _readUserData( $Email ) {
	    $UserFile = $this->_getUserFile( $Email );
	    $rVal = false;

        if ( file_exists($UserFile) ) {
	        $data = file_get_contents( $UserFile );
            $sets = unserialize( $data );

            foreach ( $sets as $Key=>$Val ) {
	            $this->settings[$Key] = $Val;
            }

            // Set a Happy Return Boolean
            $rVal = true;
        }

        // Return the Boolean Response
        return $rVal;
    }
    
    private function _getUserFile( $Email ) {
    	$disAllow = array('@', '.');
	    $rVal = str_replace($disAllow, '_', $Email);
	    
	    // Return the Cleaned Up UserName
	    return USERS_DIR . '/' . $rVal . '.user';
    }
    
    /**
     *	Function Removes the Token from the Tokens directory and Returns a boolean
     *
     *	NOTE: There really should be a check in place to make sure there will still be some
     *		  functional tokens remaining afterwards. We don't want a user locked out.
     */
    private function _logoutUser() {
    	$TokenFile = TOKEN_DIR . '/' . $this->settings['token'] . '.token';
	    $rVal = false;
	    
	    if ( file_exists($TokenFile) ) {
		    unset( $TokenFile );
		    $rVal = true;
	    }
	    
	    // Return the Boolean Response
	    return $rVal;
    }

}

?>