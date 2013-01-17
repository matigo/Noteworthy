<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods called for Email Functions
 */
require_once( LIB_DIR . '/functions.php' );
require_once( LIB_DIR . '/class.phpmailer.php' );         // The PHP Mailer Library

class Email {
    var $settings;
    var $messages;
    var $Errors;

    function __construct( $Items ) {
        $this->messages = getLangDefaults( NoNull($this->settings['DispLang'], "EN") );
        $this->settings = $this->_populateClass();
        $this->Errors = array();

        // Add the Message Items to the Settings Array
        foreach( $Items as $key=>$val ) {
            $this->settings[$key] = $val;
        }
    }

    /***********************************************************************
     *  Public Functions
     ***********************************************************************/
    /**
     * Function performs the Messaging Activity Requested
     */
    public function perform() {
        $Method = NoNull($this->settings['PgSub1']);
        $rVal = $this->messages['lblInvalidFunc'] . " [$Method]";

        // Perform the Requested Work
        switch ( $Method ) {
            case 'send':
                // Send a Message to the Appropriate Accounts
                $rVal = $this->_sendEmail();
                break;

            case 'test':
            	$rVal = $this->_testEmail();
            	break;
            	
            default:
        }

        //Return the Information
        return $rVal;
    }

    /***********************************************************************
     *  Private Functions
     ***********************************************************************/
    /**
     * Function sets the base variables that will be used throughout the class
     */
    private function _populateClass() {
        $rVal = array( 'replyName' => $this->messages['lblMailRepName'],
                       'subject'   => $this->messages['lblMailMsgFrom'] . ": ",
                       'DispLang'  => "EN",
                      );

        // Return the Array of Items
        return $rVal;
    }

    /**
     * Function sends the Email So Long as the Body has Content
     */
    private function _sendEmail() {
        $Body = NoNull($this->settings['testerMSG'], $this->settings['inptMessage']);
	    $rVal = array( 'isGood'	 => 'N',
	    			   'Message' => $this->messages['lblUnknownErr'],
	    			  );

        if ( $Body ) {
        	$SSLVal = NoNull($this->settings['testerESSL'], readSetting('core', 'EmailSSL'));
        	$SecureSSL = YNBool( $SSLVal );
        	$SendFrom = NoNull($this->settings['testerEFrom'], $this->settings['inptEmail']);
        	$SendName = NoNull($this->settings['testerEFNam'], $this->settings['inptName']);
        	$ReplyTo = readSetting('core', 'EmailReplyTo');
        	$SendTo = NoNull($this->settings['testerETo'], readSetting( 'core', 'EmailSendTo' ));

            $mail = new PHPMailer();
            $mail->IsSMTP();

            $mail->SMTPAuth   = true;
            $mail->SMTPSecure = ( $SecureSSL ) ? 'ssl' : '';
            $mail->Host       = NoNull($this->settings['testerEServ'], readSetting('core', 'EmailServ'));
            $mail->Port       = NoNull($this->settings['testerEPort'], readSetting('core', 'EmailPort'));
            $mail->Username   = NoNull($this->settings['testerEUser'], readSetting('core', 'EmailUser'));
            $mail->Password   = NoNull($this->settings['testerEPass'], readSetting('core', 'EmailPass'));

            $mail->SetFrom(NoNull($SendFrom, $ReplyTo), NoNull($SendName, 'Site Message'));
            $mail->Subject = $this->settings['subject'] . ' ' . NoNull($SendName, $this->settings['replyName']);
            //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!";
            $mail->MsgHTML( $Body );
            $mail->AddAddress( $SendTo );

            if(!$mail->Send()) {
            	$ErrorMsg = "Mailer Error: " . $mail->ErrorInfo;
                $rVal['Message'] = $ErrorMsg;
                $this->Errors[] = $ErrorMsg;
            } else {
            	$rVal['Message'] = $this->messages['lblSuccess'];
            	$rVal['isGood'] = 'Y';
            }                
        }

        // Return the Response Array
        return $rVal;
    }

    /**
     *	Function Sends a Test Message, either with the Passed Variables, or with Existing Values
     */
    private function _testEmail() {
    	$EmailDomain = $this->_readBaseDomainURL( $this->settings['HomeURL'] );
    	$data = array( 'testerEServ' => NoNull($this->settings['txtMailHost'], readSetting('core', 'EmailServ')),
    				   'testerEPort' => NoNull($this->settings['txtMailPort'], readSetting('core', 'EmailPort')),
    				   'testerEUser' => NoNull($this->settings['txtMailUser'], readSetting('core', 'EmailUser')),
    				   'testerEPass' => NoNull($this->settings['txtMailPass'], readSetting('core', 'EmailPass')),
    				   'testerESSL'  => NoNull($this->settings['cmbMailSSL'], readSetting('core', 'EmailSSL')),
    				   'testerMSG'	 => $this->messages['lblMailTestBody'],
    				   'testerEFrom' => NoNull($this->settings['txtMailReply'], "noteworthy@$EmailDomain"),
    				   'testerETo'	 => NoNull($this->settings['txtMailSendTo'], readSetting('core', 'EmailSendTo')),
    				   'testerEFNam' => "Noteworthy",
    				   );
	    $rVal = array( 'isGood'	 => 'N',
	    			   'Message' => $this->messages['lblUnknownErr'],
	    			  );

    	// Update the Class Settings with the Tester Settings
    	foreach ( $data as $Key=>$Val ) {
	    	$this->settings[$Key] = $Val;
    	}

    	// Send the Test Email
    	$rVal = $this->_sendEmail();

    	// Return the Response Array
    	return $rVal;
    }

    /**
     *	Function Reads the Base Domain, excluding any subdomain information that might exist.
     */
	function _readBaseDomainURL( $url ) {
		$rVal = false;
	
		$pieces = parse_url( $url );
		$domain = isset( $pieces['host'] ) ? $pieces['host'] : '';
		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
			$rVal = $regs['domain'];
		}
	
		// Return the Domain Information
		return $rVal;
	}
}
?>