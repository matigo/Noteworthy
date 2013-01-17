<?php

/**
 * @author Jason F. Irwin
 * @copyright 2012
 * 
 * Class contains the rules and methods required for Evernote Handling
 * 
 * Change Log
 * ----------
 * 2012.10.07 - Created Class (J2fi)
 */
define('EVER_DIR', LIB_DIR . '/evernote');
require_once( LIB_DIR . '/functions.php');
require_once( EVER_DIR . '/autoload.php');
require_once( EVER_DIR . '/Thrift.php');
require_once( EVER_DIR . '/classes.php');

require_once( EVER_DIR . '/transport/TTransport.php');
require_once( EVER_DIR . '/transport/THttpClient.php');
require_once( EVER_DIR . '/protocol/TProtocol.php');
require_once( EVER_DIR . '/protocol/TBinaryProtocol.php');

require_once( EVER_DIR . '/packages/Types/Types_types.php');
require_once( EVER_DIR . '/packages/UserStore/UserStore.php');
require_once( EVER_DIR . '/packages/NoteStore/NoteStore.php');

class evernote {
    var $settings;
    var $errors;

    var $noteStore;
    var $userStore;
    var $tmp;

    function __construct( $settings ) {
        $this->settings = $settings;
        $this->errors = array();
    }

    /** ********************************************************************** *
     *  Public Functions
     ** ********************************************************************** */

    /**
     * Function performs the requested Method Activity and Returns the Results
     *		in an array.
     */
    public function performAction() {
	    $data = "Evernote Feature Not Activated";

	    // Ensure the Basic Requirements are Met, and Perform the Requested Action(s)
		if ( $this->_canProceed() ) {
			writeNote( "Evernote - performAction: " . NoNull($this->settings['PgSub1']) );
		    switch ( NoNull($this->settings['PgSub1']) ) {
		    	case 'listNotebooks':
		    		$data = $this->_getNotebooks();
		    		break;
		    	
		    	case 'listNotes':
		    		$data = $this->_getNotes();
		    		break;
		    	
		    	case 'listUserInfo':
		    		$data = $this->_getUserInfo();
		    		break;

		    	case 'getSelectedNotebooks':
		    		$data = $this->_getSelectedNotebooks();
		    		break;

		    	case 'setSelectedNotebooks':
		    		$data = $this->_setSelectedNotebooks();
		    		break;

		    	case 'performUpdate':
		    		$data = $this->_performUpdate();
		    		break;
		    		
		    	case 'refreshNote':
		    		$data = $this->_refreshNote();
		    		break;

		    	case 'testToken':
		    		$data = $this->_testToken();
		    		break;

			    default:
			    	$data = "Invalid API Request";
			    	break;
		    }
		}

		// Assemble the Data
		$rVal = array('data' => $data,
					  'errors' => $this->errors,
					  'isGood' => ( count($this->errors) == 0 ) ? 'Y' : 'N',
					  );

	    // Return the Array
	    return $rVal;
    }

    /** ********************************************************************** *
     *  Private Functions
     ** ********************************************************************** */
    /**
     * Function Checks to Ensure the Request Can Proceed
     *	Definition: Is API Key required? Yes? Do we have it? Yes? Matches?
     */
    private function _canProceed() {
	    $UseSandbox = NoNull($this->setting['sandbox'], readSetting( 'core', 'UseSandbox' ));
	    if ( $UseSandbox != 'N' ) { $UseSandbox = 'Y'; }
	    $HostName = ( $UseSandbox == 'Y' ) ? 'sandbox.evernote.com' : 'www.evernote.com';
	    $isProd = ( $UseSandbox == 'Y' ) ? '_sb' : '_prod';
	    $ErrCount = 0;

	    if ( BoolYN($this->settings['EN_ENABLED']) ) {
			$data = array( 'EVERNOTE_HOST'			=> $HostName,
	                       'EVERNOTE_SCHEME'		=> 'https',
	                       'EVERNOTE_PORT'			=> 443,
	                       'DEVELOPER_TOKEN'		=> readSetting( 'core', 'DevToken' ),
	                       'EVERNOTE_POINTER'		=> $isProd,
	                       'noteStoreOK'			=> false,
	                       'userStoreOK'			=> false,
	                       );

	    	// Add the Configuration to the Settings Array
	    	foreach( $data as $key=>$val ) {
		    	$this->settings[ $key ] = $val;
	    	}

	    } else {
	    	$this->errors[] = "Evernote is Not Enabled";
			$ErrCount++;
		}

	    // Return a Boolean Response
	    return ($ErrCount > 0) ? false : true;
    }
    
    private function _saveUserData( $Key, $Value ) {
	    switch ( $Key ) {
		    case 'id':
		    case 'username':
		    case 'email':
		    case 'name':
		    case 'shardId':
		    	saveSetting( 'core', $Key, $Value );
		    	break;
		    
		    default:
		    	// Do Nothing
	    }
	    
	    // Return a Happy Boolean
	    return true;
    }
    
    /**
     * Function Prepares the Notestore for Use (if it's not already active) and returns a Boolean Response
     */
    private function _prepNoteStore() {
    	if ( !$this->settings['noteStoreOK'] ) {
			try {
				$noteStoreShard = $this->_getNoteStoreShard();
		        if ( $noteStoreShard != '' ) {
			        // Prepare the NoteStore
			        $noteStoreHttpClient = new THttpClient( $this->settings['EVERNOTE_HOST'],
		        											$this->settings['EVERNOTE_PORT'], 
		        											$noteStoreShard, 
		        											$this->settings['EVERNOTE_SCHEME']
		        										   );
		        	$noteStoreProtocol = new TBinaryProtocol($noteStoreHttpClient);
			        $this->noteStore = new \EDAM\NoteStore\NoteStoreClient( $noteStoreProtocol, $noteStoreProtocol );
			        
			        // If we're this far, then the NoteStore is Good
			        $this->settings['noteStoreOK'] = true;
			    }

			} catch (TTransportException $e) {
				writeNote( "Error Preparing NoteStore: " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_prepNoteStore() | Error Preparing NoteStore: " . $e->getMessage() );
			} catch (EDAMUserException $e) {
				writeNote( "Error Preparing NoteStore: [EDAM] " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_prepNoteStore() | Error Preparing NoteStore: [EDAM] " . $e->getMessage() );
			} catch (EDAMSystemException $e) {
				writeNote( "Error Preparing NoteStore: [EDAM] " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_prepNoteStore() | Error Preparing NoteStore: [EDAM] " . $e->getMessage() );
			} catch (Exception $e) {
				writeNote( "Error Preparing NoteStore: [General] " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_prepNoteStore() | Error Preparing NoteStore: [General] " . $e->getMessage() );
			}
    	}

    	// Return the Boolean Response
	    return $this->settings['noteStoreOK'];
    }
    
    /**
     * Function Prepares the UserStore for Use (if it's not already active) and returns a Boolean Response
     */
    private function _prepUserStore() {
    	if ( !$this->settings['userStoreOK'] ) {
			try {
		        // Prepare the UserStore
		        $userStoreHttpClient = new THttpClient( $this->settings['EVERNOTE_HOST'],
		        										$this->settings['EVERNOTE_PORT'], 
		        										"/edam/user", 
		        										$this->settings['EVERNOTE_SCHEME']
		        									   );
		        $userStoreProtocol = new TBinaryProtocol($userStoreHttpClient);
		        $this->userStore = new \EDAM\UserStore\UserStoreClient($userStoreProtocol, $userStoreProtocol);

		        // If we're this far, then the NoteStore is Good
		        $this->settings['userStoreOK'] = true;

			} catch (TTransportException $e) {
				writeNote( "Error Preparing UserStore: " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_prepUserStore() | Error Preparing UserStore: " . $e->getMessage() );
			} catch (EDAMUserException $e) {
				writeNote( "Error Preparing UserStore: [EDAM] " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_prepUserStore() | Error Preparing UserStore: [EDAM] " . $e->getMessage() );
			} catch (EDAMSystemException $e) {
				writeNote( "Error Preparing UserStore: [EDAM] " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_prepUserStore() | Error Preparing UserStore: [EDAM] " . $e->getMessage() );
			} catch (Exception $e) {
				writeNote( "Error Preparing UserStore: [General] " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_prepUserStore() | Error Preparing UserStore: [General] " . $e->getMessage() );
			}
    	}

    	// Return the Boolean Response
	    return $this->settings['userStoreOK'];
    }

    /**
     * Function Checks the Validity of a Token and Returns User Data
     */
    private function _testToken() {
	    $rVal = array();

	    if ( $this->settings['ttoken'] != '' ) {
	    	writeNote( "Testing Token: " . $this->settings['ttoken'] );
		    $splitter = array();
		    $isOK = true;

	    	// Validate the Token Value Passed
	    	if ( strlen($this->settings['ttoken']) < 12 ) {
	    		$isOK = false;
	    	}
	    	if ( strpos($this->settings['ttoken'], ':') > 0 ) {
		    	$splitter = explode(':', $this->settings['ttoken']);
	    	} else {
	    		$isOK = false;
	    	}
	    	
	    	// Set whether we should use the Sandbox or Production Servers
	    	saveSetting( 'core', 'UseSandbox', NoNull($this->settings['sandbox'], 'Y') );

	    	// Try to Retrieve the User Data if the H Token Exists
	    	if ( in_array('A=en-devtoken', $splitter) && $isOK ) {
		    	$rVal = $this->_getUserInfo( $this->settings['ttoken'] );

		    	// No Errors? Save the (applicable) Details
		    	foreach ( $rVal as $key=>$val ) {
			    	$this->_saveUserData( $key, $val );
		    	}
		    	saveSetting( 'core', 'DevToken', $this->settings['ttoken'] );

	    	} else {
	    		writeNote( "Invalid Token Provided: [" . $this->settings['ttoken'] . "]" );
	    		$this->errors[] = formatErrorMessage( 'main.php', "Invalid Token Provided: [" . $this->settings['ttoken'] . "]" );
	    	}

	    } else {
		    writeNote( "Invalid Token Provided: [" . $this->settings['ttoken'] . "]" );
			$this->errors[] = formatErrorMessage( 'main.php', "Invalid Token Provided: [" . $this->settings['ttoken'] . "]" );
	    }

	    // Return the Array
	    return $rVal;
    }

	/**
	 * Function Returns the NoteStore Shard
	 */
	private function _getNoteStoreShard() {
		$rVal = readSetting( 'core', 'noteStoreShard' . $this->settings['EVERNOTE_POINTER'] );

		// If We Don't Have a Shard, Get One
		if ( $rVal == '' ) {
			try {
		        // Collect the NoteStore URL and Extract the Shard
		        if ( $this->_prepUserStore() ) {
			        $noteStoreURL = $this->userStore->getNoteStoreUrl( $this->settings['DEVELOPER_TOKEN'] );
			        $noteStoreShard = str_replace($this->settings['EVERNOTE_SCHEME'] . '://' . $this->settings['EVERNOTE_HOST'], '', $noteStoreURL);
	
			        if ( $noteStoreShard != '' ) {
			        	saveSetting( 'core', 'noteStoreShard' . $this->settings['EVERNOTE_POINTER'], NoNull($noteStoreShard) );
				        $rVal = $noteStoreShard;
			        }
		        }

			} catch (TTransportException $e) {
				writeNote( "Error Obtaining NoteStore Shard: " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_getNoteStoreShard() | Error Obtaining NoteStore Shard: " . $e->getMessage() );
			} catch (EDAMUserException $e) {
				writeNote( "Error Obtaining NoteStore Shard: [EDAM] " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_getNoteStoreShard() | Error Obtaining NoteStore Shard: [EDAM] " . $e->getMessage() );
			} catch (EDAMSystemException $e) {
				writeNote( "Error Obtaining NoteStore Shard: [EDAM] " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_getNoteStoreShard() | Error Obtaining NoteStore Shard: [EDAM] " . $e->getMessage() );
			} catch (Exception $e) {
				writeNote( "Error Obtaining NoteStore Shard: [General] " . $e->getMessage() );
				$this->errors[] = formatErrorMessage( 'main.php', "_getNoteStoreShard() | Error Obtaining NoteStore Shard: [General] " . $e->getMessage() );
			}
		}

		// Return the NoteStore Shard
		return $rVal;
	}
	
	/*
	 * Function Returns an Array of User Data
	 *
	 * id / username / email / name / timezone / privilege / created / updated / deleted /
	 * active / shardId / attributes / accounting {} - Upload Limits & Premium
	 */
	private function _getUserInfo( $DevToken = '' ) {
		if ( $DevToken == '' ) { $DevToken = $this->settings['DEVELOPER_TOKEN']; }
		$rVal = array();

		try {
			// Prepare the UserStore
	        if ( $this->_prepUserStore() ) {
		        $rVal = $this->userStore->getUser( $DevToken );
	        }

		} catch (TTransportException $e) {
			writeNote( "Error Obtaining User Info: [TTransport] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getUserInfo() | Error Obtaining User Info: [TTransport] " . $e->getMessage() );
		} catch (EDAMUserException $e) {
			writeNote( "Error Obtaining User Info: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getUserInfo() | Error Obtaining User Info: [EDAM] " . $e->getMessage() );
		} catch (EDAMSystemException $e) {
			writeNote( "Error Obtaining User Info: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getUserInfo() | Error Obtaining User Info: [EDAM] " . $e->getMessage() );
		} catch (Exception $e) {
			writeNote( "Cannot Get User Info: " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getUserInfo() | Error Obtaining User Info: [General] " . $e->getMessage() );
		}

		// Return the Array of Data
		return $rVal;
	}
	
	/**
	 * Function Records a Set of Notebooks to Read Data From
	 */
	private function _setSelectedNotebooks() {
		$NotebookGUIDs = NoNull($this->settings['guidlist']);
		$rVal = array();
		
		if ( $NotebookGUIDs != "" ) {
			$settingKey = 'core_notebooks' . $this->settings['EVERNOTE_POINTER'];
			$guidList = explode('|', $NotebookGUIDs);
			$Notebooks = array();

			clearSettings( $settingKey );
			foreach ( $guidList as $NotebookGUID ) {
				if ( $this->_isValidNotebookGUID($NotebookGUID) ) {
					saveSetting( $settingKey, $NotebookGUID, 0);
				}
			}

			// Read the Notebooks Back into the Return Array
			$nBookGUIDs = $this->_getSelectedNotebooks();
			$rVal = array( 'NotebookGUIDs' => $nBookGUIDs,
						   'length' => count($nBookGUIDs),
						  );
		}

		// Return an Array Containing the Selected Notebooks
		return $rVal;
	}

	/*
	 * Get an Array of Selected Notebook GUIDs
	 */
	private function _getSelectedNotebooks() {
		writeNote( "Entered Function: _getSelectedNotebooks()" );
		$settingKey = 'core_notebooks' . $this->settings['EVERNOTE_POINTER'];
		$rVal = readSetting( $settingKey, '*' );

		// Return the Array
		return $rVal;
	}

	/*
	 * Get a Boolean Response Outlining the Validity of the Supplied NotebookGUID
	 */	
	private function _isValidNotebookGUID( $NotebookGUID ) {
		writeNote( "Entered Function: _isValidNotebookGUID( $NotebookGUID )" );
		$rVal = false;

		// Don't Allow an Empty String to Waste Time
		if ( $NotebookGUID == "" ) {
			return $rVal;
		}

		try {
			$noteStoreShard = $this->_getNoteStoreShard();

	        if ( $noteStoreShard != '' ) {
		        // Collect the Notebook Information
		        if ( $this->_prepNoteStore() ) {
			        $data = $this->noteStore->listNotebooks( $this->settings['DEVELOPER_TOKEN'] );
			        if ( $data ) {
				        foreach ( $data as $notebook ) {
				        	if ( $notebook->guid == $NotebookGUID ) {
				        		writeNote( "NotebookID [$NotebookGUID] Is Valid )" );
					        	$rVal = true;
					        	break;
				        	}
				        }
			        }

					// Record Whether Notebook ID is Valid or Not
					if ( !$rVal ) {
						writeNote( "NotebookID [$NotebookGUID] Is Invalid )" );
					}
		        }
	        }

		} catch (TTransportException $e) {
			writeNote( "Error Obtaining Notebooks: [TTransport] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_isValidNotebookGUID() | Error Obtaining Notebooks: [TTransport] " . $e->getMessage() );
		} catch (EDAMUserException $e) {
			writeNote( "Error Obtaining Notebooks: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_isValidNotebookGUID() | Error Obtaining Notebooks: [EDAM] " . $e->getMessage() );
		} catch (EDAMSystemException $e) {
			writeNote( "Error Obtaining Notebooks: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_isValidNotebookGUID() | Error Obtaining Notebooks: [EDAM] " . $e->getMessage() );
		} catch (Exception $e) {
			writeNote( "Error Obtaining Notebooks: [General] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_isValidNotebookGUID() | Error Obtaining Notebooks: [General] " . $e->getMessage() );
		}

        // Return a Boolean Response
        return $rVal;
	}

	/*
	 * Get an Array of Notebooks, their Names, and GUIDs
	 */
	private function _getNotebooks() {
		writeNote( "Entered Function: _getNotebooks()" );
		$rVal = array();

		try {
			$noteStoreShard = $this->_getNoteStoreShard();

	        if ( $noteStoreShard != '' ) {
		        // Collect the Notebooks
		        if ( $this->_prepNoteStore() ) {
			        $data = $this->noteStore->listNotebooks( $this->settings['DEVELOPER_TOKEN'] );
			        $sorter = array();
			        if ( $data ) {
			        	$Selected = $this->_getSelectedNotebooks();
			        	
				        foreach ( $data as $notebook ) {
					        $sorter[] = $notebook->name;
				        }

				        // Sort the Array
				        natcasesort( $sorter );
				        foreach ( $sorter as $item ) {
				        	$isChecked = '';
				        	
					        foreach ( $data as $notebook ) {
						        if ( $notebook->name == $item ) {
						        	if ( array_key_exists($notebook->guid, $Selected) ) { $isChecked = 'checked'; }
						        	$notebook->state = $isChecked;
							        $rVal[] = $notebook;
						        }
					        }
				        }
			        }
		        }
	        }

		} catch (TTransportException $e) {
			writeNote( "Error Obtaining Notebooks: [TTransport] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getNotebooks() | Error Obtaining Notebooks: [TTransport] " . $e->getMessage() );
		} catch (EDAMUserException $e) {
			writeNote( "Error Obtaining Notebooks: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getNotebooks() | Error Obtaining Notebooks: [EDAM] " . $e->getMessage() );
		} catch (EDAMSystemException $e) {
			writeNote( "Error Obtaining Notebooks: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getNotebooks() | Error Obtaining Notebooks: [EDAM] " . $e->getMessage() );
		} catch (Exception $e) {
			writeNote( "Error Obtaining Notebooks: [General] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getNotebooks() | Error Obtaining Notebooks: [General] " . $e->getMessage() );
		}

        // Return The Array of Notebook Data
        return $rVal;
	}

	/*
	 * Get the Notes for a Specific Notebook
	 */
	private function _getNotes() {
		$NotebookGUID = NoNull($this->settings['notebookguid']);
		$rVal = array();

		// If we don't have a GUID, Exit
		if ( $NotebookGUID == '' ) {
			return $rVal;
			exit;
		}

		try {
			$noteStoreShard = $this->_getNoteStoreShard();
			$isOK = false;

	        if ( $noteStoreShard != '' ) {
		        // Prepare the NoteStore
		        if ( $this->_prepNoteStore() ) {
			        $noteBooks = $this->noteStore->listNotebooks( $this->settings['DEVELOPER_TOKEN'] );			        
		        } else {
			        return $rVal;
		        }

		        // Confirm the NotebookGUID Exists
		        foreach ( $noteBooks as $notebook ) {
			        if ( $notebook->guid == $NotebookGUID ) {
				        $isOK = true;
			        }
		        }

		        // Collect the Notes, or Return an Error
		        if ( $isOK ) {
		        	$rVal = $this->_collectNotes( array($NotebookGUID), true );

		        } else {
			        $this->errors[] = formatErrorMessage( 'main.php', "_getNotes() | Could Not Locate Notebook with GUID: " . $NotebookGUID );
		        }
	        }

		} catch (TTransportException $e) {
			writeNote( "Error Obtaining Notes: " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getNotes() | Error Obtaining Notes: " . $e->getMessage() );
		} catch (EDAMUserException $e) {
			writeNote( "Error Obtaining Notes: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getNotes() | Error Obtaining Notes: [EDAM] " . $e->getMessage() );
		} catch (EDAMSystemException $e) {
			writeNote( "Error Obtaining Notes: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getNotes() | Error Obtaining Notes: [EDAM] " . $e->getMessage() );
		} catch (Exception $e) {
			writeNote( "Error Obtaining Notes: [General] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_getNotes() | Error Obtaining Notes: [General] " . $e->getMessage() );
		}

        // Return the Notes for the Provided NotebookGUID
        return $rVal;
	}
	
	/**
	 * Function Updates all of the Recorded notes.
	 */
	private function _performUpdate() {
		$rVal = "Update Incomplete";
		
		try {
			writeNote( "_performUpdate: About to Update Notes from Evernote Server" );
			$noteStoreShard = $this->_getNoteStoreShard();
			$isOK = false;

	        if ( $noteStoreShard != '' ) {
		        // Prepare the NoteStore
		        if ( !$this->_prepNoteStore() ) { return $rVal; }

		        // Collect the Notebooks
		        $Selected = $this->_getSelectedNotebooks();
		        $NotebookGUIDs = array_keys( $Selected );

		        // Collect the Notes, or Return an Error
		        $Count = $this->_collectNotes( $NotebookGUIDs );
		        
		        if ( $Count == 0 ) {
			        $rVal = "No Notes Required Updating";
		        } else {
			        $rVal = ($Count == 1) ? "1 Note Was Updated" : "$Count Notes Were Updated";
		        }
	        }

		} catch (TTransportException $e) {
			writeNote( "Error Obtaining Notes: " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_performUpdate() | Error Obtaining Notes: " . $e->getMessage() );
		} catch (EDAMUserException $e) {
			writeNote( "Error Obtaining Notes: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_performUpdate() | Error Obtaining Notes: [EDAM] " . $e->getMessage() );
		} catch (EDAMSystemException $e) {
			writeNote( "Error Obtaining Notes: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_performUpdate() | Error Obtaining Notes: [EDAM] " . $e->getMessage() );
		} catch (Exception $e) {
			writeNote( "Error Obtaining Notes: [General] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_performUpdate() | Error Obtaining Notes: [General] " . $e->getMessage() );
		}

        // Return the Notes for the Provided NotebookGUID
		writeNote( "_performUpdate: Completed Update" );
        return $rVal;
	}
	
	/**
	 *	Function Refreshes a Note in the DataStore
	 */
	private function _refreshNote() {
		$NoteGUID = sqlScrub($this->settings['guid']);
		writeNote( "_refreshNote: Refresh Note [$NoteGUID]" );
		$rVal = false;
		
		// Don't Do Anything if the GUID is Invalid
		if ( $NoteGUID == "" || strlen($NoteGUID) != 36 ) {
			writeNote( "_refreshNote: Invalid GUID: $NoteGUID" );
			return $rVal;
			exit;
		}
		
		// Swap the Return Value to an Array if Necessary
		if ( $ReadOnly ) { $rVal = array(); }

		try {
			$noteStoreShard = $this->_getNoteStoreShard();
			$isOK = false;

	        if ( $noteStoreShard != '' ) {
                $settingKey = 'core_notes' . $this->settings['EVERNOTE_POINTER'];

		        // Prepare the NoteStore
		        if ( !$this->_prepNoteStore() ) {
		        	writeNote( "_refreshNote: NoteStore Not Prepared" );
			        return $rVal;
		        }

		        // Collect the Resource (With Data and With Attributes)
		        $note = $this->noteStore->getNote($this->settings['DEVELOPER_TOKEN'], $NoteGUID, true, true, false, false);
		        $CurrUpdSeqNo = nullInt($note->updateSequenceNum);

            	// Update the Note
            	if ( $this->_recordNote($note, $CurrUpdSeqNo) ) {
            		writeNote( "_refreshNote: Note [" . $note->guid . "] Has Been Recorded" );
            		$sqlStr = "SELECT c.`id`, c.`guid`, c.`Title`, UNIX_TIMESTAMP(c.`CreateDTS`) as `CreateDTS`, c.`PostURL`," .
            					    " UNIX_TIMESTAMP(c.`UpdateDTS`) as `UpdateDTS`, LENGTH(c.`Value`) as `PostLength`," .
            					    " (SELECT count(m.`id`) FROM `Meta` m WHERE c.`id` = m.`ContentID`) as `MetaRecords`" .
            				  "  FROM `Content` c" .
            				  " WHERE c.`TypeCd` = 'POST' and c.`isReplaced` = 'N'" .
            				  "   and c.`guid` = '$NoteGUID';";
            		$rVal = doSQLQuery( $sqlStr );
            	}
	        }

		} catch (TTransportException $e) {
			writeNote( "Error Collecting Notes: " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_refreshNote() | Error Obtaining Notes: " . $e->getMessage() );
		} catch (EDAMUserException $e) {
			writeNote( "Error Obtaining Notes: [EDAM - User] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_refreshNote() | Error Obtaining Notes: [EDAM - User] " . $e->getMessage() );
		} catch (EDAMSystemException $e) {
			writeNote( "Error Obtaining Notes: [EDAM - System] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_refreshNote() | Error Obtaining Notes: [EDAM - System] " . $e->getMessage() );
		} catch (EDAMNotFoundException $e) {
			writeNote( "Error Obtaining Notes: [EDAM - NotFound] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_refreshNote() | Error Obtaining Notes: [EDAM - NotFound] " . $e->getMessage() );
		} catch (Exception $e) {
			writeNote( "Error Obtaining Notes: [General] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_refreshNote() | Error Obtaining Notes: [General] " . $e->getMessage() );
		}

		// Return the Boolean or Array
		writeNote( "_refreshNote: Completed Note [$NoteGUID]" );
		return $rVal;
	}
	
	/**
	 * Function Collects the Notes from Evernote if they match the Specific NotebookGUID
	 *
	 * Note: ReadOnly = {TRUE} will return an array of notes (Name, GUID, CreateDTS, UpdateDTS)
	 */
	private function _collectNotes( $NotebookGUIDs, $ReadOnly = false ) {
		$LastUpdSeqNo = nullInt(readSetting( 'cron', 'UpdateSequenceNo' ));
		$CurrUpdSeqNo = $MaxUpdSeqNo = 0;
		$rVal = 0;

		// Don't Collect Notes unless we have an array (even for one)
		if ( !is_array( $NotebookGUIDs ) ) {
			return $rVal;
			exit;
		}

		try {
			$noteStoreShard = $this->_getNoteStoreShard();
			$isOK = false;

	        if ( $noteStoreShard != '' ) {
                $settingKey = 'core_notes' . $this->settings['EVERNOTE_POINTER'];

		        // Prepare the NoteStore
		        if ( !$this->_prepNoteStore() ) {
			        return $rVal;
		        }

		        // Prepare the Search Filter
		        $searchFilter = new \EDAM\NoteStore\NoteFilter();
                $searchFilter->ascending = false;
                $searchFilter->order = evernoteNoteSortOrder::UPDATE_SEQUENCE_NUMBER;

                $Offset = 0;
                $Pages = 50;
                $totalNotes = -1;
                $i = 1;

                while ( $Offset < $totalNotes || $totalNotes < 0 ) {
                	$notes = $this->noteStore->findNotes($this->settings['DEVELOPER_TOKEN'], $searchFilter, $Offset, $Pages);
                	$totalNotes = $notes->totalNotes;

                    foreach ( $notes->notes as $note ) {
                    	$CurrUpdSeqNo = nullInt($note->updateSequenceNum);
                    	if ( $CurrUpdSeqNo > $MaxUpdSeqNo ) { $MaxUpdSeqNo = $CurrUpdSeqNo; }

                    	if ( $CurrUpdSeqNo > $LastUpdSeqNo ) {
	                    	writeNote( "_collectNotes: Note [" . $note->guid . " | " . $note->title . "] Requires Updates" );
	                    	$i++;

	                    	// Check to see if there is a difference
	                    	if ( $this->_isNewNote($note->guid, $CurrUpdSeqNo) ) {
	                    		writeNote( "_collectNotes: Note [" . $note->guid . "] Is Ready To Be Recorded" );
	                    		if ( in_array($note->notebookGuid, $NotebookGUIDs) ) {
			                    	// Record the Note
				                    if ( $this->_recordNote( $note, $CurrUpdSeqNo ) ) {
				                    	$rVal++;
				                    } else {
					                    writeNote( "_collectNotes: ERROR -- Note [" . $note->guid . " | " . $note->title . "] COULD NOT Be Recorded" );
				                    }
			                    } else {
				                    // The Note has been Deleted or Moved (or is not part of the site)
				                    $this->_removeNote( $note->guid );
			                    }
	                    	} else {
		                    	writeNote( "_collectNotes: Note [" . $note->guid . " | " . $note->title . "] Is Already In the Database" );
	                    	}
                    	}
                    }

                    // Set the Offset Value (Exit If We're Done)
                    if ( $CurrUpdSeqNo < $LastUpdSeqNo ) {
                    	$Offset = $totalNotes + 1;
                    } else {
	                	$Offset += $Pages;
	                }
                }

				// Save the New Update Sequence Number                
                if ( $MaxUpdSeqNo > $LastUpdSeqNo ) {
	                saveSetting( 'cron', 'UpdateSequenceNo', $MaxUpdSeqNo );
                }

                // Debug Data
				writeNote( "_collectNotes: Last Update Sequence No: $LastUpdSeqNo" );
				writeNote( "_collectNotes: Maximum Update Sequence No: $MaxUpdSeqNo" );
				writeNote( "_collectNotes: -- Test Segment Complete --" );
	        }

		} catch (TTransportException $e) {
			writeNote( "Error Collecting Notes: " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_collectNotes() | Error Obtaining Notes: " . $e->getMessage() );
		} catch (EDAMUserException $e) {
			writeNote( "Error Obtaining Notes: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_collectNotes() | Error Obtaining Notes: [EDAM] " . $e->getMessage() );
		} catch (EDAMSystemException $e) {
			writeNote( "Error Obtaining Notes: [EDAM] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_collectNotes() | Error Obtaining Notes: [EDAM] " . $e->getMessage() );
		} catch (Exception $e) {
			writeNote( "Error Obtaining Notes: [General] " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_collectNotes() | Error Obtaining Notes: [General] " . $e->getMessage() );
		}
		
		// Return the Boolean or Array
		return $rVal;
	}

	/*
	 * Function Checks to See if the Note Has Been Updated or Changed in Some Way through the Update Sequence ID
	 */
	private function _isNewNote( $NoteGUID, $UpdSeqID ) {
		$rVal = true;
		$CurrSeqID = 0;

		switch ( DB_TYPE ) {
			case 1:
				// MySQL
				if ( !is_array($this->tmp) ) {
					$this->tmp = array();
					$sqlStr = "SELECT c.`id`, c.`guid`, c.`UpdateSeqID` FROM `Content` c" .
							  " WHERE c.`isReplaced` = 'N' and c.`TypeCd` = 'POST'" .
							  " ORDER BY c.`CreateDTS` DESC";
					$rslt = doSQLQuery( $sqlStr );
					if ( is_array($rslt) ) {
	                    foreach ( $rslt as $Key=>$Row ) {
	                        $this->tmp[ $Row['guid'] ] = nullInt($Row['UpdateSeqID']);
	                    }
					}
				}

				if ( array_key_exists($NoteGUID, $this->tmp) ) {
					$CurrSeqID = $this->tmp[ $NoteGUID ];
				}
				break;

			case 2:
				// NoteWorthy Store
				$settingKey = 'core_notes' . $this->settings['EVERNOTE_POINTER'];
				$CurrSeqID = readSetting( $settingKey, $NoteGUID );
				break;

			default:
				// Do Nothing Here
		}

		// Compare the Sequence IDs
		if ( $CurrSeqID == $UpdSeqID ) {
			$rVal = false;
		}

		// Return Whether The Note is New or Not
		return $rVal;
	}

	/*
	 * Function Records a Note to either the NoteWorthy Store or to the MySQL Database
	 *
	 * Note: The API Function Will Not Accept New Posts This Way, it is Not Included Here
	 */
	private function _recordNote( $NoteObj, $UpdSeqID ) {
		writeNote( "_recordNote: Building Records for [" . $NoteObj->guid . "] | " . NoNull($NoteObj->title));
		$rVal = false;

		try {
		    // Adapt the Note Object Into a Consistent Object
			$attribs = array();
			foreach ( $NoteObj->attributes as $key=>$val ) {
				$attribs[ $key ] = NoNull($val);
			}
			$tags = array();
			if ( $NoteObj->tagGuids ) {
				foreach ( $NoteObj->tagGuids as $tagGuid ) {
					$tg = NoNull($tagGuid);
					$tags[ $tg ] = $this->_getTagName( $tg );
				}
			}
			$data = array( 'guid'			=> $NoteObj->guid,
						   'title'			=> NoNull($NoteObj->title),
						   'content'		=> '',
						   'footnotes'      => '',
						   'contentLength'	=> nullInt($NoteObj->contentLength),
						   'contentHash'	=> NoNull($NoteObj->contentHash),
						   'author'         => NoNull($attribs['author'], $this->settings['DEFAULT_AUTHOR']),
						   'created'		=> (nullInt($NoteObj->created) / 1000) + 1,
						   'updated'		=> nullInt($NoteObj->updated) / 1000,
						   'deleted'		=> nullInt($NoteObj->deleted) / 1000,
						   'updSeqID'		=> nullInt($NoteObj->updateSequenceNum),
						   'excerpt'		=> '',
						   'notebookGUID'	=> $NoteObj->notebookGuid,
						   'url'			=> '',
						   'attributes'		=> $attribs,
						   'tagGuids'		=> $tags,
						  );
			$BodyFoot = $this->_collectNoteContent( $NoteObj->guid, $data );
			$data['url'] = $this->_getPostURL( $data );
			$data['content'] = $BodyFoot['content'];
			$data['footnotes'] = $BodyFoot['footnotes'];
			$data['excerpt'] = parseExcerpt( $data['content'] );

			switch ( DB_TYPE ) {
				case 1:
					/** MySQL **/
                    $ParentID = $this->_getPostParentIDFromGUID( $data['guid'] );
                    $DeleteDTS = ( $data['deleted'] > 0 ) ? "FROM_UNIXTIME(" . $data['deleted'] . ")" : "NULL";

                    $sqlStr = "INSERT INTO `Content` (`guid`, `TypeCd`, `Title`, `Value`, `Hash`, `ParentID`, `PostURL`, `PostAuthor`," . 
                    								 "`UpdateSeqID`, `CreateDTS`, `UpdateDTS`, `DeleteDTS`) " .
                              "VALUES ( '" . $data['guid'] . "', " .
                                       "'POST', " .
                                       "'" . sqlScrub( $data['title'] ) . "', ".
                                       "'" . sqlScrub( $data['content'] ) . "', " .
                                       "'" . $data['contentHash'] . "', " .
                                       " $ParentID, '" . $data['url'] . "', '" . sqlScrub($data['author']) . "', " .
                                       		 $data['updSeqID'] . ", " .
                                       " FROM_UNIXTIME(" . $data['created'] . "), " .
                                       " FROM_UNIXTIME(" . $data['updated'] . "), " .
                                       " $DeleteDTS );";
                    $dbID = doSQLExecute( $sqlStr );

                    if ( $dbID > 0 ) {
	                    // Save the Footnotes in the Content Directory (If They Exist)
                        if ( $data['footnotes'] != '' ) {
                            $sqlStr = "INSERT INTO `Content` (`guid`, `TypeCd`, `Title`, `Value`, `Hash`, `ParentID`, `PostURL`, `PostAuthor`," . 
                    								 "`CreateDTS`, `UpdateDTS`, `DeleteDTS`) " .
		                              "VALUES ( '" . $data['guid'] . "', 'POST-FOOTER', '', '" . sqlScrub($data['footnotes']) . "', " .
		                                       "'" . md5($data['footnotes']) . "', $dbID, '" . $data['url'] . "', '" . sqlScrub($data['author']) . "', " .
		                                       " FROM_UNIXTIME(" . $data['created'] . "), FROM_UNIXTIME(" . $data['updated'] . "), " .
		                                       " $DeleteDTS );";
		                    $footID = doSQLExecute( $sqlStr );
		                    writeNote( "PostID $dbID Footnote Saved to $footID" );
                        }

                        // Write the Meta Data Where Appropriate
                        $sqlStr = "INSERT INTO `Meta` (`ContentID`, `ParentID`, `guid`, `TypeCd`, `Value`, `Hash`) " .
                                  "VALUES";
                        $sqlVal = " ($dbID, NULL, '" . $data['guid'] . "', 'POST-NBGUID', '" . $data['notebookGUID'] . "', '" . md5($data['notebookGUID']) . "'),";
                        if ( $data['contentLength'] > 0 ) {
                            $sqlVal .= " ($dbID, NULL, '" . $data['guid'] . "', 'POST-LENGTH', '" . $data['contentLength'] . "', '" . md5($cLen) . "'),";
                        }
                        if ( $data['attributes']['longitude'] != '' ) {
                            $sqlVal .= " ($dbID, NULL, '" . $data['guid'] . "', 'POST-GPS-LNG', '" . $data['attributes']['longitude'] . "', '" . md5($data['attributes']['longitude']) . "'),";
                        }
                        if ( $data['attributes']['latitude'] != '' ) {
                            $sqlVal .= " ($dbID, NULL, '" . $data['guid'] . "', 'POST-GPS-LAT', '" . $data['attributes']['latitude'] . "', '" . md5($data['attributes']['latitude']) . "'),";
                        }
                        if ( nullInt($data['attributes']['altitude']) != 0 ) {
                            $sqlVal .= " ($dbID, NULL, '" . $data['guid'] . "', 'POST-GPS-ALT', '" . $data['attributes']['altitude'] . "', '" . md5($data['attributes']['altitude']) . "'),";
                        }

                        // Add the Post Tags
                        if ( is_array( $data['tagGuids']) ) {
                            foreach ( $data['tagGuids'] as $Key=>$Name ) {
                                $sqlVal .= " ($dbID, NULL, '" . $data['guid'] . "', 'POST-TAG', '" . sqlScrub($Name) . "', '" . md5($Name) . "'),";
                            }
                        }

                        if ( $sqlVal != "" ) {
                            $sqlVal = substr($sqlVal, 0, strlen($sqlVal) - 1);
                            doSQLQuery( $sqlStr . $sqlVal );
                        }

    					// Set a Happy Boolean
    					$rVal = true;
                    }
                    break;
				
				case 2:
					/** NoteWorthy Store **/
					$RawDIR = $this->settings['ContentDIR'] . '/store';
					if ( checkDIRExists( $RawDIR ) ) {
						$FileName = $RawDIR . '/' . $NoteObj->guid . '.raw';
						$FileSize = file_put_contents($FileName, serialize($data));

						// Save the Index Record
						$indexKey = 'core_index' . $this->settings['EVERNOTE_POINTER'];
						saveSetting( $indexKey, $data['created'], $data['guid'] );

						// Set the Return Boolean to Something Happy if Good
						if ( $FileSize > 0 ) { $rVal = true; }
					}

					// Save the Update Sequence ID
					$settingKey = 'core_notes' . $this->settings['EVERNOTE_POINTER'];
					saveSetting( $settingKey, $note->guid, $UpdSeqID );

					// Set a Happy Boolean
					$rVal = true;
					break;
			}
			
		} catch (Exception $e) {
			writeNote( "Error Recording Note: " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_recordNote() | Error Recording Note: " . $e->getMessage() );
		}
		
		// Return a Boolean Response
		writeNote( "_recordNote: Completed Note [" . $NoteObj->guid . "]");
		return $rVal;
	}

    /**
     * Function Returns the Maximum Content.id Value for a given GUID
     */
    private function _getPostParentIDFromGUID( $NoteGUID ) {
        $rVal = 'NULL';

        if ( $NoteGUID ) {
        	$sqlStr = "SELECT max(`id`) as `MaxID` FROM `Content`" .
        			  " WHERE `isReplaced` = 'N' and `guid` = '$NoteGUID'";
        	$rslt = doSQLQuery( $sqlStr );
			if ( is_array($rslt) ) {
				foreach ( $rslt as $Key=>$Row ) {
					$rVal = nullInt( $Row['MaxID'] );
				}
			}

			// Eliminate any Items That Already Exist
			if ( nullInt($rVal) > 0 ) {
	            $sqlStr = "UPDATE `Content` SET `isReplaced` = 'Y', `UpdateDTS` = Now()" .
	            		  " WHERE `isReplaced` = 'N' and `guid` = '$NoteGUID'";
	            $rslt = doSQLExecute( $sqlStr );				
			}
        }

        // Return the Highest Content.id Value
        return $rVal;
    }
	
	/**
	 * Function Removes a Note from the Database or File System and Returns a Boolean Response
	 */
	private function _removeNote( $NoteGUID ) {
		$rVal = false;
		
		try {
			switch ( DB_TYPE ) {
				case 1:
					/** MySQL **/
					$sqlStr = "UPDATE `Content` SET `isReplaced` = 'Y', `UpdateDTS` = Now()" .
							  " WHERE `isReplaced` = 'N' and `guid` = '$NoteGUID'";
					$rslt = doSQLExecute( $sqlStr );

                    // Do Not Allow Invalid Numbers
                    if ( $rslt > 0 ) { $rVal = $rslt; }
					break;

				case 2:
					/** Noteworthy Store **/
					$settingKey = 'core_notes' . $this->settings['EVERNOTE_POINTER'];
					$rVal = deleteSetting( $settingKey, $NoteGUID );

					// If rVal is TRUE, it means the GUID Existed. Remove the File.
					if ( $rVal ) {
						$RawDIR = $this->settings['ContentDIR'] . '/store';
						$FileName = $RawDIR . "/$NoteGUID";
						if ( is_file($FileName) ) { unlink( $FileName ); }						
					}
					break;

				default:
					/** API Handling -- Not Yet Coded **/
			}
		} catch (Exception $e) {
			writeNote( "Error Removing Note: " . $e->getMessage() );
			$this->errors[] = formatErrorMessage( 'main.php', "_removeNote() | Error Removing Note: " . $e->getMessage() );
		}
	}

	/**
	 * Function Returns the PostURL
	 */	
	private function _getPostURL( $NoteDTL, $URLFormat = 'YYYY/MM/DD/TITLE' ) {
		$base = "[HOMEURL]/";
		$attribs = explode("/", $URLFormat);

		foreach ( $attribs as $item ) {
			switch( strtolower($item) ) {
				case 'yyyy':
				case 'yy':
				case 'y':
					$tURL[] = date("Y", $NoteDTL['created']);
					break;
				
				case 'mm':
				case 'm':
					$tURL[] = date("m", $NoteDTL['created']);
					break;
				
				case 'dd':
				case 'd':
					$tURL[] = date("d", $NoteDTL['created']);
					break;
					
				case 'created':
				case 'unix':
					$tURL[] = NoNull($NoteDTL['created']);
					break;
				
				case 'guid':
				case 'uuid':
					$tURL[] = NoNull($NoteDTL['guid']);
					break;
				
				case 'random':
				case 'rand':
					$tURL[] = getRandomString(8);
					break;
				
				case 'title':
					$tURL[] = sanitizeURL($NoteDTL['title']);
					break;
				
				default:
					// Nothing
			}
		}

		// Check to See if this URL Already Exists
		$ValidURL = false;
		$i = 0;
		while ( !$ValidURL ) {
			if ( is_array($tURL) ) {
				$rVal = $base . implode( "/", $tURL );
				$rVal .= ( $i > 0 ) ? "-$i/" : "/";
			} else {
				$rVal = $base . "$i/";
			}
			$ValidURL = $this->_isGoodPostURL($NoteDTL['guid'], $rVal);
			$i++;
		}

        // Return the Post URL Based on Preferences
        return $rVal;
	}

	/**
	 * Function Checks to Ensure the URL Passed is Unique or Already Assigned to a NoteGUID
	 */
	private function _isGoodPostURL( $NoteGUID, $PostURL ) {
		$rVal = true;
		$PostGUID = "";
		
		switch ( DB_TYPE ) {
    		case 1:
    		    $sqlStr = "SELECT `guid` FROM `Content`" . 
    		    		  " WHERE `isReplaced` = 'N' and `TypeCd` = 'POST'" . 
    		    		  "   and `PostURL` = '$PostURL'";
				$rslt = doSQLQuery($sqlStr);
                if ( is_array($rslt) ) {
                    foreach ( $rslt as $Key=>$Row ) {
                        $PostGUID = NoNull( $Row['guid'] );
                    }
                }
                break;

            case 2:
        		$settingKey = 'core_urls' . $this->settings['EVERNOTE_POINTER'];
        		$PostGUID = readSetting( $settingKey, $PostURL );
		        break;

		    default:
		        // Do Nothing
		}

		// If We Have a GUID and it's NOT the same as $NoteGUID, Set False
		if ( $PostGUID ) {
			if ( $PostGUID != $NoteGUID ) { $rVal = false; }
		} else {
			// Save the URL
			if ( DB_TYPE == 1) { saveSetting( $settingKey, $PostURL, $NoteGUID ); }
		}

		// Return the Boolean Response
		return $rVal;
	}

	/**
	 * Function Returns the Name Corresponding to the Tag Supplied. If the Tag is Unknown, the Name is Retrieved
	 *		from Evernote before being returned.
	 */
	private function _getTagName( $TagGUID ) {
		$rVal = "";
		
		switch ( DB_TYPE ) {
			case 1:
				// MySQL
				$sqlStr = "SELECT `Value` FROM `Meta` WHERE `TypeCd` = 'TAG' and `guid` = '$TagGUID'";
				$rslt = doSQLQuery($sqlStr);
                if ( is_array($rslt) ) {
                    foreach ( $rslt as $Key=>$Row ) {
                        $rVal = NoNull( $Row['Value'] );
                    }
                }
                
                if ( $rVal == "" ) {
                    $rVal = $this->_readTagNameFromEvernote( $TagGUID );

                    $sqlStr = "INSERT INTO `Meta` (`guid`, `TypeCd`, `Value`, `Hash`)
                               VALUES ('$TagGUID', 'TAG', '" . sqlScrub( $rVal ) . "', NULL)";
                    $metaID = doSQLExecute( $sqlStr );
                    writeNote( "Recorded Tag $rVal with guid $TagGUID to Meta.id($metaID)" );
                }
				break;

			case 2:
				// NoteWorthy Store
				$settingKey = 'core_tags' . $this->settings['EVERNOTE_POINTER'];
				$rVal = readSetting( $settingKey, $TagGUID );

				if ( $rVal == "" ) {
				    $rVal = $this->_readTagNameFromEvernote( $TagGUID );
					saveSetting( $settingKey, $TagGUID, $rVal);
				}
				break;

			default:
				// API Handling (Yet to be Coded)
		}

		// Return the Tag Name
		return $rVal;
	}
	
	/**
	 *  Function Connects the the Evernote Server to Collect the Tag Name
	 */
	private function _readTagNameFromEvernote( $TagGUID ) {
    	$rVal = "";
    	
        if ( $this->_prepNoteStore() ) {
            $tagData = $this->noteStore->getTag( $this->settings['DEVELOPER_TOKEN'], $TagGUID) ;
            $rVal = NoNull($tagData->name);

        } else {
            writeNote( "Could Not Read Tag Name [$TagGUID]" );
            $this->errors[] = formatErrorMessage( 'main.php', "_readTagNameFromEvernote() | Could Not Read Tag Name [$TagGUID]" );
        }
        
        // Return the Name
        return $rVal;
	}
	
	/**
	 * Function Collects the Content from the Evernote Servers and Returns a String
	 */
	private function _collectNoteContent( $NoteGUID, $NoteDTL ) {
		$rVal = "";
		
		if ( $this->_prepNoteStore() ) {
			$data = $this->noteStore->getNoteContent($this->settings['DEVELOPER_TOKEN'], $NoteGUID);

	        // Prep the finalized HTML
			$Content = $this->_scrubContent( $data );

	        // Collect any Resources (Images, Audio Files, Etc.) We Might Need
	        $Content = $this->_collectNoteResources( $NoteGUID, $Content, $NoteDTL['created'] );

	        // Wrap the Content in Proper HTML
	        $Content = $this->_wrapContentInHTML( $Content, $NoteDTL );

	        // Set the Return Value (in an Array)
	        $rVal = $Content;
		}

		// Return the Content
		return $rVal;
	}
	
	/**
	 * Function Wraps the Scrubbed Content in the Appropriate HTML and Returns an array
	 *    containing the Content and Footnote sections of the Post
	 */
	private function _wrapContentInHTML( $Content, $NoteDTL ) {
		$rVal = array();

		// Construct the Footnotes
    	if (preg_match_all('/\[(\d+\. .*?)\]/s', $Content, $matches)) {
        	$notes = array();
            $n = 1;

    		foreach($matches[0] as $fn) {
    			$note = preg_replace('/\[\d+\. (.*?)\]/s', '\1', $fn);
    			$notes[$n] = $note;

    			$Content = str_replace($fn, "<sup>$n</sup>", $Content);
    			$n++;
    		}

            $Footnotes = "<ol>";
    		for($i=1; $i<$n; $i++) {
    			$Footnotes .= "<li>$notes[$i]</li>";
    		}
    		$Footnotes .= "</ol>";
        }

        // Construct the Reutrn Array
        $rVal = array('content' => $Content,
                      'footnotes' => $Footnotes,
                      );

        // Return the Array
        return $rVal;
	}
	
	/**
	 * Function Constructs the Landing Page and Writes the HTML file to the
	 *		/html Cache Folder
	 */
	private function _buildLandingPage() {
		$ItemCount = nullInt( readSetting('core', 'PageItems'), 7 );
		$rVal = false;
		$i = 0;

		// Collect the Index of Items
		$settingKey = 'core_index' . $this->settings['EVERNOTE_POINTER'];
		$Posts = readSetting( $settingKey, '*' );
		uksort( $Posts, "arraySortDesc" );

		// Collect the .cache File Contents and Construct the Home Page
		while ( $i < $ItemCount ) {
			
		}

		// Return a Boolean Response
		return $rVal;		
	}

	/**
	 * Function Returns a Set of Filters and Their Replacement Strings
	 */
	private function _getFilters() {
		$rVal = array( "&nbsp;"		=> " ",		"&apos;"			=> "'",			"&quot;"	=> '"',
					   "<br/><br/>"	=> "\r\n",	"<div><br/></div>"	=> "\r\n",		"<br/>"		=> "\r\n",
					   "<p >"		=> "<p>",	"<span >"			=> "",			"<span>"	=> "",
					   "</span>"	=> "",		"&amp;"				=> "&",			"<p><p>"	=> "<p>",
					   "</p></p>"	=> "</p>",	"<div"				=> "<p",		"/div>"		=> "/p>",
					   "Â|"			=> "",		"  "				=> " ",			"â~@¦"		=> "...",
					   'style=""'   => "",		"<p> <a"			=> "<p><a",		"<p></p>"	=> "",
					   "<span"		=> "<p",	'<p style=" ">'		=> "",			"<p> <p>"	=> "<p>",
					   "<p><block"	=> "<block","</blockquote>"		=> "</blockquote><p>",
					   "<div><br clear=\"none\"/></div>"	=> "",
					  );

		// Return the Filters
		return $rVal;
	}
	
	/**
	 * Function Scrubs the Evernote Content Data of Evernote-specific markup and returns the core text
	 *		in a standardized HTML format.
	 *
	 *	Notes:	* Paragraphs are enclosed in <p> tags
	 *			* Empty Lines are Scrubbed Out Intentionally (No Repeating <br/> -- No <br/>)
	 *			* Style Information is Scrubbed Out completely
	 *			* Block Information is Retained as Much as Possible (Including Styling)
	 */
	private function _scrubContent( $Content ) {
		$rVal = "";
		$Filters = $this->_getFilters();
		$inBlockquote = false;
		$ScrubMax = 3;
		$i = 0;

		// Eliminate the <div style> Elements
        $pattern = "/<div(.*?)>(.*?)<\/p>/si";
        preg_match($pattern, $Content, $pStyles);
        if ( is_array($pStyles) ) {
	        $Content = str_replace($pStyles[1], "", $Content);
        }

		// Eliminate the <en-note> Elements
        $pattern = "/<en-note(.*?)>(.*?)<\/en-note>/si";
        preg_match($pattern, $Content, $matches);

        // Return the Content Contained within the Evernote Markup
        $Content = NoNull($matches[2]);

        // Set the Filters
		$FilterKey = array_keys( $Filters );
		$FilterVal = array_values( $Filters );
		
		// Clean Up the Content
		while ( $i < $ScrubMax ) {
			$Content = str_replace($FilterKey, $FilterVal, $Content);
			$i++;
		}

	    //Construct the Body Elements Line by Line
        foreach(preg_split("/(\r?\n)/", $Content) as $line) {
            if ( NoNull(strip_tags($line, "<en-media>")) ) {
		        // Wipe Out the Style Formatting
		        $pattern = "/style=\"(.*?)\"/si";
		        preg_match($pattern, $line, $matches);
		        if ( is_array($matches) ) {
		            if ( NoNull($matches[0]) != "" ) {
		                $line = str_replace(NoNull($matches[1]), "", $line);
		            }
		        }

		        // Determine if we're in a Blockquote
                if ( startsWith($line, '<blockquote>') ) {
	                $inBlockquote = true;
                }

                // Determine if the Blockquote Is Complete
                if ( endsWith($line, '<blockquote>') ) {
	                $inBlockquote = false;
                }

                // Ensure the String Starts with a <p> Tag
                if ( !$inBlockquote ) {
	                if ( !startsWith($line, '<p>') ) {
		                $line = "<p>" . NoNull($line);
	                }

	                // Ensure the String Ends with a </p> Tag
	                if ( !endsWith($line, '</p>') ) {
		                $line .= "</p>";
	                }
                }

                // Add the Line to the Outout
                $rVal .= ( NoNull($line) != "" ) ? NoNull($line) : "";
            }
        }

		// Clean Up the Content One Last Time
		$i = 0;
		while ( $i < $ScrubMax ) {
			$rVal = str_replace($FilterKey, $FilterVal, $rVal);
			$i++;
		}

		// Return the Cleaned up Content
		return str_replace($FilterKey, $FilterVal, $rVal);
	}

	/**
	 * Function Collects the Resources for a Note from the Evernote Servers and Returns
	 *		an Array containing the Hashes and Resource Locations
	 */
	private function _collectNoteResources( $NoteGUID, $Content, $CreateDTS ) {
        $rVal = $Content;
        $DTSPath = date("Y", $CreateDTS) . '/' . date("m", $CreateDTS);
        $pattern = "/<en-media (.*?)\/>/si";
        $haveRes = true;
        $i = 0;

        // Ensure all Resource Files are Downloaded
        while ( $haveRes ) {
	        preg_match($pattern, $rVal, $matches);

	        if ( count($matches) > 0 ) {
	            preg_match("/hash=\"(.*?)\"/si", $matches[1], $hashes);
	            preg_match("/title=\"(.*?)\"/si", $matches[1], $title);
	            preg_match("/width=\"(.*?)\"/si", $matches[1], $width);
	            preg_match("/type=\"(.*?)\"/si", $matches[1], $mimeType);

	            if ( count($hashes) > 0 ) {
	                $resHash = NoNull($hashes[1]);
	                writeNote("Hash: $resHash | MIME Type: " . $mimeType[1] );
	                $Link = $this->_getNoteAttachmentByHash( $NoteGUID, $resHash, $mimeType[1], $DTSPath );

	                if ( $Link ) {
	                    $resAlt = NoNull($title[1]);
	                    $resWidth = NoNull($width[1]);
	                    $resA = '';
	                    $resW = '';
	                    if ( $resAlt ) { $resA = " alt=\"$resAlt\""; }
	                    if ( $resWidth ) { $resW = " width=\"" . $resWidth . "px\""; }
	
	                    $resLink = "<img src=\"[MEDIA_URL]/$DTSPath/$Link\"" . NoNull( $resA . $resW ) . " />";
	                    $rVal = str_replace($matches[0], $resLink, $rVal);
	                    writeNote( "Replaced: " . $matches[0] . "\n   With: " . $resLink );
	                }
	            }
	        } else {
		        $haveRes = false;
	        }

	        // Don't Let an Infinite Loop get in the way of a good time
	        if ( $i > 100 ) { $haveRes = false; }
	        $i++;
        }

        // Return the Body Content
        return $rVal;
	}

	/**
	 * Function Collects the Resources for a Note from the Evernote Servers and Returns
	 *		an Array containing the Hashes and Resource Locations
	 */
    private function _getNoteAttachmentByHash( $noteGUID, $resourceHash, $mimeType, $DTSPath ) {
        $rVal = false;
        $FileName = $resourceHash . '.' . $this->_getExtensionType($mimeType);

        if ( $resourceHash ) {
            $fileData = false;
            $SaveDIR = $this->settings['ContentDIR'] . "/$DTSPath";
            $PropHash = pack('H*', $resourceHash);

            if ( checkDIRExists($SaveDIR) ) {
                if ( !file_exists("$SaveDIR/$FileName") ) {
                    try {
                        $fileInfo = $this->noteStore->getResourceByHash($this->settings['DEVELOPER_TOKEN'], $noteGUID, $PropHash, true, false, false);

                    } catch ( edam_error_EDAMNotFoundException $e ) {
                        writeNote( "Error Getting Attachment [Not Found]: " . $e->getMessage() );
                        $this->errors[] = formatErrorMessage( 'main.php', "_getNoteAttachmentByHash() | Error Getting Attachment [UserException] " . $e->getMessage() );

                    } catch ( edam_error_EDAMUserException $e ) {
                        writeNote( "Error Getting Attachment [User Exception]: " . $e->getMessage() );
                        $this->errors[] = formatErrorMessage( 'main.php', "_getNoteAttachmentByHash() | Error Getting Attachment [UserException] " . $e->getMessage() );
                    }

                    // If We Have File Data, Write It to the Content Directory
                    if ( $fileInfo ) {
                        $size = file_put_contents($SaveDIR . '/' . $FileName, $fileInfo->data->body);
                        if ( $size ) {
                            $rVal = $FileName;
                            writeNote( "Saved File: $SaveDIR/$FileName | Size: " . number_format($size) . " bytes" );

                            // Record the Meta Information (If DB Mode Enabled)
                            if ( DB_TYPE == 0 ) {
                                $ContentID = $this->_getContentIDFromGUID( $noteGUID );
                                $sqlStr = "INSERT INTO `Meta` (`ContentID`, `guid`, `TypeCd`, `Value`, `Hash`) ";

                                switch ( $FileType ) {
	                                case 'image':
	                                	$sqlStr .= "VALUES ($ContentID, '$noteGUID', 'IMAGE-MIME', '$mimeType'	   , '" . md5($mimeType) . "')," .
		                                                 " ($ContentID, '$noteGUID', 'IMAGE-SIZE', '$size'    	   , '" . md5($size) . "')," .
		                                                 " ($ContentID, '$noteGUID', 'IMAGE-ROOT', '$SaveDIR' 	   , '" . md5($SaveDIR) . "')," .
		                                                 " ($ContentID, '$noteGUID', 'IMAGE-HASH', '$resourceHash' , '" . md5($resourceHash) . "')," .
		                                                 " ($ContentID, '$noteGUID', 'IMAGE-FILE', '$FileName'	   , '" . md5($FileName) . "'),";
		                                break;

		                            case 'audio':
		                            	$sqlStr .= "VALUES ()";
		                            	// break;

		                            default:
		                            	// We Shouldn't Be Here. Wipe out the SQL Statement
		                            	$sqlStr = "";
                                }

                                // Perform the SQL Statement if Valid
                                if ( $sqlStr != "" ) { $MetaID = doSQLExecute( $sqlStr ); }
                            }
                        }
                    }

                } else {
                    $rVal = $FileName;
                }
            }
        }

        // Return the Resource Size in Bytes
        return $rVal;
    }

	/**
	 * Function Determines the Appropriate Extension Type for a File
	 */
    private function _getExtensionType( $MimeType ) {
        $rVal = '';

        switch( $MimeType ) {
            case 'image/bmp':
                $rVal = 'bmp';
                break;

            case 'image/gif':
                $rVal = 'gif';
                break;

            case 'image/jpg':
            case 'image/jpeg':
                $rVal = 'jpg';
                break;

            case 'image/png':
                $rVal = 'png';
                break;

            case 'image/tiff':
                $rVal = 'tiff';
                break;

            default:
        }

        // Return the Extension
        return $rVal;
    }

}

?>