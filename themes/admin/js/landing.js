var _canSubmit = true;

/* ******************************************* *
 *      General Settings Page Functions
 * ******************************************* */
function getPostsList( apiKey ) {
    var params = new Object();
    var method = 'content/listPosts';
    var apiPath = getAPIPath();

    params['accessKey'] = apiKey;

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parsePostsResult( data.data );
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
function parsePostsResult( data ) {
	var result = false;
	var _rows = '',
		_row = '';
	var _homeURL = getAPIPath();
	var _dispDiv = '<div class="sys-message [CLASS]"><p>[MESSAGE]</p></div>',
		_returnDiv = '';
	var _rowTemplate = '<tr><td>[ID]</td>' +
					   '<td>[TITLE]</td>' +
					   '<td>[CREATEDTS]</td>' +
					   '<td>[UPDATEDTS]</td>' +
					   '<td>[METAS]</td>' +
					   '<td class="table-icons">' +
						   '[REFRESH]' +
					   '</td></tr>';

	if ( data.isGood == 'Y' ) {
		_homeURL = _homeURL.replace("/api/", '');
		result = true;
		var ds = data.posts;
		var _postURL = '',
			_refresh = '';

		// Append the HTML to the Result String
		for ( var i = 0; i < ds.length; i++ ) {
			if ( ds[i].PostURL != null ) {
				_postURL = '<a href="' + ds[i].PostURL + '" target="_blank">' + ds[i].Title + '</a>';
				_refresh = '';

			} else {
				_postURL = ds[i].Title;
				_refresh = '<a href="[HOMEURL]/api/"><img class="table-icon" src="[HOMEURL]/themes/admin/img/icons/error.png" alt="Refresh This Item"></a>';
			}

			_row = _rowTemplate.replace("[ID]", ds[i].id);
			_row = _row.replace("[TITLE]", _postURL);
			_row = _row.replace("[CREATEDTS]", dateFormat(ds[i].CreateDTS * 1000, "mmmm dS, yyyy"));
			_row = _row.replace("[UPDATEDTS]", dateFormat(ds[i].UpdateDTS * 1000, "mmmm dS, yyyy"));
			_row = _row.replace("[METAS]", ds[i].MetaRecords);
			_row = _row.replace("[REFRESH]", _refresh);
			_row = _row.replace("[HOMEURL]", _homeURL);
			_row = _row.replace("[HOMEURL]", _homeURL);
			
			_rows += _row;
		}

	} else {
		_returnDiv = _dispDiv.replace("[CLASS]", "sys-error");
	}
	_dispDiv = _dispDiv.replace("[MESSAGE]", data.Message);
	document.getElementById("return-msg").innerHTML = _returnDiv;
	document.getElementById("post-data").innerHTML = _rows;

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