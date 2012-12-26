<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for User Data
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Class (J2fi)
 */
require_once(LIB_DIR . '/functions.php');

class User extends Midori {
    var $Details;
    var $Errors;

    function __construct( $Token = '' ) {
        $this->Details = $this->_populateClass();
        $this->Errors = array();

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
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    public function isLoggedIn() {
        return YNBool( $this->Details['isLoggedIn'] );
    }

    /**
     * Function sets or gets the Person's Display Name and Returns the appropriate data.
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    public function DisplayName( $Value = '' ) {
	    $rVal = '';
	    
	    if ( $Value != '' ) {
		    // Set the UserName
	    } else {
		    $rVal = NoNull($this->Details['DisplayName']);
	    }
	    
	    // Return the Appropriate Response
	    return $rVal;
    }

    /**
     * Function tests an Email / AdminCode Combination and returns a Boolean Response
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    public function authAccount( $Email, $AdminCode, $Token ) {
        $rVal = array('isGood' => false,
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
	        $isLoggedIn = $this->createAccount( $Email, $AdminCode, $Token );
	        if ( !$isLoggedIn ) { $rVal['ErrMsg'] = "Could Not Create Record"; }
	        writeNote( "Created Account [Good: " . BoolYN($isLoggedIn) . "] - email: $Email AdminCode: $AdminCode Token: $Token" );
        }

        // Check to See if the AdminCode is Valid
        $AccountID = alphaToInt($AdminCode);
        if ( $AccountID > 0 ) {
	        $UsersFile = $this->_getUserFile( $Email );
	        if ( file_exists($UsersFile) ) {
		        $data = file_get_contents( $UsersFile );
		        $lots = unserialize( $data );

		        // Add Some Account-Specific Information
		        $this->Details['id'] = $AccountID;
		        $this->Details['AdminCode'] = $AdminCode;		// One User Can Have Multiple Admin Codes (Temporary Logins)
		        $this->Details['isLoggedIn'] = BoolYN($isLoggedIn);

	            foreach ( $lots as $key=>$val ) {
		            if ( $key != "isLoggedIn" ) {
			            $this->Details[ $key ] = NoNull($val);
		            }
	            }

	            // Create a Token Record
	            $TokenFile = TOKEN_DIR . '/' . $Token . '.token';
	            if ( file_put_contents($TokenFile, serialize($this->Details)) ) {
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
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    public function createAccount( $Email, $AdminCode, $Token ) {
        $rVal = false;

        // Do Some Basic Error Checking
        if ( NoNull($Email) == "" || NoNull($AdminCode) == "" || NoNull($Token) == "" ) { return $rVal; }

        // Create the User File if the Admin Code is Valid
        $AccountID = alphaToInt($AdminCode);
        if ( $AccountID > 0 ) {
	        $UsersFile = $this->_getUserFile( $Email );
	        if ( !file_exists($UsersFile) ) {
		        $data = array('id'			=> $AccountID,
		        			  'adminCode'	=> $AdminCode,
		        			  'DisplayName'	=> "",
		        			  'ImageURL'	=> "",
		        			  'Created'		=> time(),
		        			  'email'		=> NoNull($Email),
		        			  'isLoggedIn'	=> 'Y',
		        			  );

	            // Write the Record
	            if ( file_put_contents($UsersFile, serialize($data)) ) {
		            $rVal = true;
	            }
	        }
        }

        // Return the Success Boolean
        return $rVal;
    }

    /***********************************************************************
     *  Private Functions
     ***********************************************************************/
    /**
     * Function builds the base variables that will be used throughout the
     *      application
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    private function _populateClass() {
        $rVal = array( 'id'          => 0,
                       'DisplayName' => '',
                       'ImageURL'    => '',
                       'Created'     => '',
                       'email'		 => '',
                       'adminCode'	 => '',
                       'isLoggedIn'  => 'N',
                       'lastToken'	 => '',
                      );

        // Return the Base Array
        return $rVal;
    }

    /**
     * Function retrieves any missing data for the User Class from a Token
     *      Record and Returns a Boolean Response
     * 
     * Note: This may not be used long-term
     * 
     * Change Log
     * ----------
     * 2012.10.07 - Created Function (J2fi)
     */
    private function _fillUserClass( $Token ) {
        $rVal = false;
        if ( $Token == '' ) { return $rVal; }
        if ( !checkDIRExists(TOKEN_DIR) ) { return $rVal; }

        // Search the Token Directory for the Appropriate Record
        $TokenFile = TOKEN_DIR . '/' . $Token . '.token';
        if ( file_exists($TokenFile) ) {
	        $data = file_get_contents( $TokenFile );
            $lots = unserialize( $data );

            foreach ( $lots as $key=>$val ) {
		    	$this->Details[ $key ] = NoNull($val);
            }

            // Set the Happy Return Boolean
            $rVal = true;
        }

        // Return a Boolean
        return $rVal;
    }
    
    private function _getUserFile( $Email ) {
    	$disAllow = array('@', '.');
	    $rVal = str_replace($disAllow, '_', $Email);
	    
	    // Return the Cleaned Up UserName
	    return USERS_DIR . '/' . $rVal . '.user';
    }

}

?>