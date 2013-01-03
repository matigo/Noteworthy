var _canSubmit = true;

/* ******************************************* *
 *      General Login Functions
 * ******************************************* */
function checkLogin( email, token, apiKey ) {
    var params = new Object();
    var method = 'users/login';
    var apiPath = getAPIPath();

    // Set the Parameters
    params['accessKey'] = apiKey;
    params['token'] = token;
    params['email'] = email;
    params['route'] = $(location).attr('pathname').replace('/', '');

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseLogin( data.data );
            _canSubmit = true;
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
            _canSubmit = true;
        },
        dataType: "json"
    });
    
    return false;
}

function parseLogin( data ) {
	if ( data.isGood == 'Y' ) {
		alert( "Redirect to: " + data.redir );
		if(window.top==window) {
			window.setTimeout( 'location.reload()' );
		}
	}

	return false;
}

/**
 * Function isValidEmailAdress
 *
 *	Works as advertised. Will do a very, very simple sanity check on a string and return a boolean response.
 */
function isValidEmailAddress( email ) {
    var rVal = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    return rVal.test( email );
};

function getAPIPath() {
	return $(location).attr('href').replace($(location).attr('pathname'),'') + '/api/';
}