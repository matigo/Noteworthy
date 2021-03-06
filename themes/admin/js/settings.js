var _canSubmit = true;

/* ******************************************* *
 *      General Settings Page Functions
 * ******************************************* */
function performUpdates() {
    var params = new Object();
    var method = 'settings/update';
    var apiPath = window.apiURL;

    // Set the Parameters
    for( i = 0; i < document.primary.elements.length; i++ ) {
    	if ( document.primary.elements[i].id != "" ) {
	    	params[ document.primary.elements[i].id ] = document.primary.elements[i].value;
    	}
	}

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseUpdateResult( "return-msg", data.data );
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
        },
        dataType: "json"
    });
}

function performUpdateMail() {
    var params = new Object();
    var method = 'settings/update';
    var apiPath = window.apiURL;

    // Set the Parameters
    for( i = 0; i < document.secondary.elements.length; i++ ) {
    	if ( document.secondary.elements[i].id != "" ) {
	    	params[ document.secondary.elements[i].id ] = document.secondary.elements[i].value;
    	}
	}

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseUpdateResult( "email-msg", data.data );
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
        },
        dataType: "json"
    });
}

function performUpdateMail() {
    var params = new Object();
    var method = 'settings/update';
    var apiPath = window.apiURL;

    // Set the Parameters
    for( i = 0; i < document.secondary.elements.length; i++ ) {
    	if ( document.secondary.elements[i].id != "" ) {
	    	params[ document.secondary.elements[i].id ] = document.secondary.elements[i].value;
    	}
	}

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseUpdateResult( "email-msg", data.data );
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
        },
        dataType: "json"
    });
}

function performRemindMail() {
    var params = new Object();
    var method = 'settings/update';
    var apiPath = window.apiURL;
    document.getElementById("remind-mail").disabled = true;

    // Set the Parameters
    for( i = 0; i < document.reminder.elements.length; i++ ) {
    	if ( document.reminder.elements[i].id != "" ) {
	    	params[ document.reminder.elements[i].id ] = document.reminder.elements[i].value;
    	}
	}

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseUpdateResult( "remind-msg", data.data );
            document.getElementById("remind-mail").disabled = false;
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
        },
        dataType: "json"
    });
}

/* ******************************************* *
 *      Private Functions
 * ******************************************* */
function findSelectionValue( field ) {
    var test = document.getElementsByName(field);
    var sizes = test.length;
    for (i=0; i < sizes; i++) {
            if (test[i].checked==true) {
            return test[i].value;
        }
    }
}

function parseUpdateResult( msgID, data ) {
	var result = false;
	var _dispDiv = '<div class="sys-message [CLASS]"><p>[MESSAGE]</p></div>';

	if ( data.isGood == 'Y' ) {
		result = true;
		_dispDiv = _dispDiv.replace("[CLASS]", "sys-success");
	} else {
		_dispDiv = _dispDiv.replace("[CLASS]", "sys-error");
	}
	_dispDiv = _dispDiv.replace("[MESSAGE]", data.Message);
	document.getElementById( msgID ).innerHTML = _dispDiv;

    // Return the Parsed Result Message
	return result;
}

/* ******************************************* *
 *      Auto-Fill Functions
 * ******************************************* */
function fillServerInfo( provider ) {
	document.getElementById("txtMailHost").value = "";
	document.getElementById("cmbMailSSL").value = "N";
	document.getElementById("txtMailPort").value = "25";
	document.getElementById("username-msg").innerHTML = "";

	switch ( provider ) {
		case 'gmail':
			document.getElementById("txtMailHost").value = "smtp.gmail.com";
			document.getElementById("cmbMailSSL").value = "Y";
			document.getElementById("txtMailPort").value = "465";
			document.getElementById("username-msg").innerHTML = "yourname@gmail.com (include the @ domain information)";
			break;
		
		case 'hotmail':
			document.getElementById("txtMailHost").value = "smtp.live.com";
			document.getElementById("cmbMailSSL").value = "Y";
			document.getElementById("txtMailPort").value = "587";
			document.getElementById("username-msg").innerHTML = "yourname@live.com (include the @ domain information)";
			break;

		case 'yahoo':
			document.getElementById("txtMailHost").value = "smtp.mail.yahoo.com";
			document.getElementById("cmbMailSSL").value = "Y";
			document.getElementById("txtMailPort").value = "465";
			document.getElementById("username-msg").innerHTML = "your.name (without @yahoo.com)";
			break;

		default:
			// Do Nothing
	}
}