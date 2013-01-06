var _canSubmit = true;

/* ******************************************* *
 *      General Sites Page Functions
 * ******************************************* */
function checkAkismet( apiKey, siteURL, akismetKey ) {
    var params = new Object();
    var method = 'akismet/validate';
    var apiPath = getAPIPath();

    // Set the Parameters
    params['accessKey'] = apiKey;
    params['txtHomeURL'] = siteURL;
    params['txtAkismetKey'] = akismetKey;

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseResult( data.data );
            _canSubmit = true;
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
            _canSubmit = true;
        },
        dataType: "json"
    });
}

function parseResult( data ) {
	var result = false;
	var _errMsg = "<p>** API Error. Please Try Again. **</p>";

	if( typeof data.isGood != "undefined" ) {
		if ( data.isGood == 'Y' ) {
			result = true;
			_errMsg = "<p>It's Good!</p>";
		} else {
			_errMsg = "<p>** API Key is Invalid. Please Confirm and Try Again. **</p>";
		}
	}
	document.getElementById("akismet-err").innerHTML = _errMsg;

    // Return the Parsed Timeline
	return result;
}

function getAPIPath() {
	var url = $(location).attr('href').replace($(location).attr('pathname'),'');
	var rVal = url;
	if ( url.indexOf('?') > 1 ) {
		rVal = url.substring(0, url.indexOf('?'));
	}
	rVal = rVal.replace("#", "");

	return rVal + '/api/';
}

function performUpdates() {
    var params = new Object();
    var method = 'settings/update';
    var apiPath = getAPIPath();
    var _dispDiv = '<div class="sys-message sys-info"><p>Updating the Cache Files. This May Take a Few Minutes.</p></div>';

    // Set the Parameters
    for( i = 0; i < document.primary.elements.length; i++ ) {
    	if ( document.primary.elements[i].id != "" ) {
	    	params[ document.primary.elements[i].id ] = document.primary.elements[i].value;
    	}
	}
	// Ensure 'doComments' is Properly Set
	params[ 'raWebCron' ] = findSelectionValue( 'doCron' );
	params[ 'raComments' ] = findSelectionValue( 'doComments' );
	params[ 'raTwitter' ] = findSelectionValue( 'doTwitter' );
	document.getElementById("return-msg").innerHTML = _dispDiv;

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseUpdateResult( data.data, "return-msg" );
            _canSubmit = true;
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
            _canSubmit = true;
        },
        dataType: "json"
    });
}

function performSocialUpdates() {
    var params = new Object();
    var method = 'settings/update';
    var apiPath = getAPIPath();
    var _dispDiv = '<div class="sys-message sys-info"><p>Updating the Cache Files. This May Take a Few Minutes.</p></div>';

    // Set the Parameters
    for( i = 0; i < document.socials.elements.length; i++ ) {
    	if ( document.socials.elements[i].id != "" ) {
	    	params[ document.socials.elements[i].id ] = document.socials.elements[i].value;
    	}
	}
	params[ 'chkSocShow01' ] = findSelectionValue( 'chkSocShow01' );
	params[ 'chkSocShow02' ] = findSelectionValue( 'chkSocShow02' );
	params[ 'chkSocShow03' ] = findSelectionValue( 'chkSocShow03' );
	params[ 'chkSocShow04' ] = findSelectionValue( 'chkSocShow04' );
	params[ 'chkSocShow05' ] = findSelectionValue( 'chkSocShow05' );

	// Ensure 'doComments' is Properly Set
	document.getElementById("social-msg").innerHTML = _dispDiv;

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseUpdateResult( data.data, "social-msg" );
            _canSubmit = true;
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
            _canSubmit = true;
        },
        dataType: "json"
    });
}

function findSelectionValue( field ) {
    var test = document.getElementsByName(field);
    var sizes = test.length;
    for (i=0; i < sizes; i++) {
            if (test[i].checked==true) {
            return test[i].value;
        }
    }
    return "";
}

function parseUpdateResult( data, msgTag ) {
	var result = false;
	var _dispDiv = '<div class="sys-message [CLASS]"><p>[MESSAGE]</p></div>';
	var _dispMsg = '** API Error. Please Try Again.';

	if( typeof data.isGood != "undefined" ) {
		if ( data.isGood == 'Y' ) {
			result = true;
			_dispDiv = _dispDiv.replace("[CLASS]", "sys-success");
		} else {
			_dispDiv = _dispDiv.replace("[CLASS]", "sys-error");
		}
		_dispMsg = data.Message;
	}
	_dispDiv = _dispDiv.replace("[MESSAGE]", _dispMsg);
	document.getElementById( msgTag ).innerHTML = _dispDiv;

    // Return the Parsed Result Message
	return result;
}

function displayDisqusDiv(doComments) {
    if (doComments == "Y") {
        document.getElementById("disqussed").style.display = 'block';
    } else {
        document.getElementById("disqussed").style.display = 'none';
    }
}

function displayTweetDiv(doTweets) {
    if (doTweets == "Y") {
        document.getElementById("twitname").style.display = 'block';
    } else {
        document.getElementById("twitname").style.display = 'none';
    }
}

function displayServerNote( radioID ) {
	var raSandbox = 'none',
		raProduct = 'none';
		
	if ( radioID == "raSandbox" ) {
		raSandbox = 'block';
	} else {
		raProduct = 'block';
	}

	// Update the Document Accordingly
	document.getElementById("note-sandbox").style.display = raSandbox;
	document.getElementById("note-production").style.display = raProduct;
}

function checkEvernote( apiKey, evernoteToken, useSandbox ) {
    var params = new Object();
    var method = 'evernote/testToken';
    var apiPath = getAPIPath();

    // Set the Parameters
    params['accessKey'] = apiKey;
    params['sandbox'] = useSandbox;
    params['ttoken'] = evernoteToken;

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseEvernoteResult( data.data, apiKey );
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
            _canSubmit = true;
        },
        dataType: "json"
    });
}

function parseEvernoteResult( data, apiKey ) {
	var result = false;
	var _errMsg = "";
	
	if ( data.isGood == "Y" ) {
		getNotebooks( apiKey );
	} else {
		document.getElementById("enNotebooks").innerHTML = "";
		alert( data.Message );
	}
}

function getNotebooks( apiKey ) {
    var params = new Object();
    var method = 'evernote/listNotebooks';
    var apiPath = getAPIPath();

    // Set the Parameters
    params['accessKey'] = apiKey;

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseEvernoteNotebooks( data.data );
            _canSubmit = true;
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
            _canSubmit = true;
        },
        dataType: "json"
    });
}

function parseEvernoteNotebooks( data ) {
	var result = "";
	var checked = "";
	var _errMsg = "";

	if( typeof data.errors != "undefined" ) {
		if ( data.errors.length == 0 ) {
			var ds = data.data;

			// Append the HTML to the Result String
			for ( var i = 0; i < ds.length; i++ ) {
				if ( ds[i].state == "checked" ) {
					checked = 'checked="' + ds[i].state + '" ';
				} else {
					checked = '';
				}

				result += '<tr><td><input type="checkbox" value="' + ds[i].guid + '" ' + checked + '/></td>' +
							  '<td>' + ds[i].name + '</td>' +
							  '<td>' + ds[i].defaultNotebook + '</td>' +
							  '<td>' + dateFormat(ds[i].serviceCreated, "mmmm dS, yyyy") + '</td>' +
							  '<td>' + dateFormat(ds[i].serviceUpdated, "mmmm dS, yyyy") + '</td></tr>';
			}
		}
	}

	// Write the HTML
	document.getElementById("enNotebooks").innerHTML = result;
}

function setSelectedNotebooks( apiKey, NotebookList ) {
    var params = new Object();
    var method = 'evernote/setSelectedNotebooks';
    var apiPath = getAPIPath();

    // Set the Parameters
    params['accessKey'] = apiKey;
    params['guidlist'] = NotebookList;

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseSelectedNotebooks( data.data );
            _canSubmit = true;
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
            _canSubmit = true;
        },
        dataType: "json"
    });
}

function parseSelectedNotebooks( data ) {
	var _dispDiv = '<div class="sys-message [CLASS]"><p>[MESSAGE]</p></div>';
	var _errMsg = "";

	if( typeof data.isGood != "undefined" ) {
		if ( data.isGood == "Y" && data.errors.length == 0 ) {
			_dispDiv = _dispDiv.replace("[CLASS]", "sys-success");
			_errMsg = data.data.length + " " + (data.data.length == 1 ? "Notebook" : "Notebooks")  + " Will be Regularly Scanned";
		} else {
			_errMsg = "Something Went Wrong Selecting the Notebooks!";
			_dispDiv = _dispDiv.replace("[CLASS]", "sys-error");
		}
	}
	_dispDiv = _dispDiv.replace("[MESSAGE]", _errMsg);
	document.getElementById( "notebook-msg" ).innerHTML = _dispDiv;
}