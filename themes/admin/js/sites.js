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
    params['siteurl'] = siteURL;
    params['akismet-id'] = akismetKey;

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
	var _errMsg = "";

	if ( data.isGood == 'Y' ) {
		result = true;
		_errMsg = "<p>It's Good!</p>";
	} else {
		_errMsg = "<p>** API Key is Invalid. Please Confirm and Try Again. **</p>";
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

	return rVal + '/api/';
}

function displayDisqusDiv(doComments) {
    if (doComments == "Y") {
        document.getElementById("disqussed").style.display = 'block';
    } else {
        document.getElementById("disqussed").style.display = 'none';
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
		alert("API Key Does Not Appear Valid");
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
	var _errMsg = "";

	if ( data.isGood == "Y" && data.errors.length == 0 ) {
		alert( data.data.length + " " + (data.data.length == 1 ? "Notebook" : "Notebooks")  + " Will be Scanned" );
	} else {
		alert("Something Went Wrong Selecting the Notebooks!");
	}
}