var _canSubmit = true;

/* ******************************************* *
 *      General Settings Page Functions
 * ******************************************* */
function performUpdates() {
    var params = new Object();
    var method = 'settings/update';
    var apiPath = getAPIPath();

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
            parseUpdateResult( data.data );
            _canSubmit = true;
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
            _canSubmit = true;
        },
        dataType: "json"
    });
}

/* ******************************************* *
 *      Private Functions
 * ******************************************* */
function parseUpdateResult( data ) {
	var result = false;
	var _dispDiv = '<div class="sys-message [CLASS]"><p>[MESSAGE]</p></div>';

	if ( data.isGood == 'Y' ) {
		result = true;
		_dispDiv = _dispDiv.replace("[CLASS]", "sys-success");
	} else {
		_dispDiv = _dispDiv.replace("[CLASS]", "sys-error");
	}
	_dispDiv = _dispDiv.replace("[MESSAGE]", data.Message);
	document.getElementById("return-msg").innerHTML = _dispDiv;

    // Return the Parsed Result Message
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