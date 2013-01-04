var _canSubmit = true;

/* ******************************************* *
 *      General Settings Page Functions
 * ******************************************* */
function getPostsList( Page ) {
    var params = new Object();
    var method = 'content/listPosts';
    var apiPath = getAPIPath();

    params['accessKey'] = window.accessKey;
    params['Page'] = Page;

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

function refreshPost( guid ) {
    var params = new Object();
    var method = 'evernote/refreshNote';
    var apiPath = getAPIPath();

    params['accessKey'] = window.accessKey;
    params['guid'] = guid;

    $.ajax({
        url: apiPath + method,
        data: params,
        success: function( data ) {
            parseRefreshResult( data.data );
            _canSubmit = true;
        },
        error: function (xhr, ajaxOptions, thrownError){
            alert(xhr.status + ' | ' + thrownError);
            _canSubmit = true;
        },
        dataType: "json"
    });
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

/* ******************************************* *
 *      Parsing Functions
 * ******************************************* */
function parsePostsResult( data ) {
	var result = false;
	var _rows = '',
		_row = '';
	var _homeURL = getAPIPath();
	var _dispDiv = '<div class="sys-message [CLASS]"><p>[MESSAGE]</p></div>',
		_returnDiv = '';
	var _rowTemplate = '<tr id="[GUID]"><td>[ID]</td>' +
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
		var _cPage = 1,
			_Results = 25,
			_RecTotal = 1;

		// Append the HTML to the Result String
		for ( var i = 0; i < ds.length; i++ ) {
			if ( ds[i].PostURL != null ) {
				_postURL = '<a href="' + ds[i].PostURL + '" target="_blank">' + ds[i].Title + '</a>';
				_refresh = "<button class=\"btn-tiny silver\" onclick=\"refreshPost('" + ds[i].guid + "')\"><span>Refresh</span></button>";

			} else {
				_postURL = ds[i].Title;
				_refresh = "<button class=\"btn-tiny red\" onclick=\"refreshPost('" + ds[i].guid + "')\"><span>Repair</span></button>";
			}
			
			_cPage = ds[i].PageNo;
			_Results = ds[i].Results;
			_RecTotal = ds[i].RecordTotal;

			_row = _rowTemplate.replace("[ID]", ds[i].id);
			_row = _row.replace("[GUID]", ds[i].guid);
			_row = _row.replace("[TITLE]", _postURL);
			_row = _row.replace("[CREATEDTS]", dateFormat(ds[i].CreateDTS * 1000, "mmmm dS, yyyy"));
			_row = _row.replace("[UPDATEDTS]", dateFormat(ds[i].UpdateDTS * 1000, "mmmm dS, yyyy"));
			_row = _row.replace("[METAS]", ds[i].MetaRecords);
			_row = _row.replace("[REFRESH]", _refresh);
			_row = _row.replace("[HOMEURL]", _homeURL);
			_row = _row.replace("[HOMEURL]", _homeURL);

			_rows += _row;
		}
		
		if ( ds.length == 0 || ds === false ) {
			showZeroPostDiv();
		}

		// Add Pagination
		parsePageNavigation( _cPage, _Results, _RecTotal );

	} else {
		_returnDiv = _dispDiv.replace("[CLASS]", "sys-error");
	}
	_dispDiv = _dispDiv.replace("[MESSAGE]", data.Message);
	document.getElementById("return-msg").innerHTML = _returnDiv;
	document.getElementById("post-data").innerHTML = _rows;
	
	// Return Appropriately
	return false;
}

function parsePageNavigation( CurrentPage, ResultsPerPage, RecordCount ) {
	var result = '',
		isActive = '';
	var loops = 0,
		PageNo = 1;
	
	if ( CurrentPage > 1 ) {
		result = "<a href=\"#\" onclick=\"getPostsList(" + (i - 1) + ");\" class=\"left-arr\">&larr;</a>";
	}

	for ( var i = 1; i <= RecordCount; i + ResultsPerPage ) {
		isActive = '';
		if ( PageNo == CurrentPage ) {
			isActive = ' class="active"';
		}
		result += "<a href=\"#\"" + isActive + " onclick=\"getPostsList(" + PageNo + ");\">" + PageNo + "</a>";

		i += ResultsPerPage;		
		PageNo++;
	}

	result += "<a href=\"#\" onclick=\"getPostsList(" + (CurrentPage + 1) + ");\" class=\"right-arr\">&rarr;</a>";

	// Update the Pagination
	document.getElementById("paginate").innerHTML = result;
}

function parseRefreshResult( data ) {
	var result = false;
	var _rows = '',
		_row = '';
	var _homeURL = getAPIPath();
	var _dispDiv = '<div class="sys-message [CLASS]"><p>[MESSAGE]</p></div>',
		_returnDiv = '';
	var _rowTemplate = '<tr id="[GUID]"><td>[ID]</td>' +
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
		var ds = data.data;
		var _postURL = '',
			_refresh = '';

		// Append the HTML to the Result String
		for ( var i = 0; i < ds.length; i++ ) {
			if ( ds[i].PostURL != null ) {
				_postURL = '<a href="' + ds[i].PostURL + '" target="_blank">' + ds[i].Title + '</a>';
				_refresh = "<button class=\"btn-tiny silver\" onclick=\"refreshPost('" + ds[i].guid + "')\"><span>Refresh</span></button>";

			} else {
				_postURL = ds[i].Title;
				_refresh = "<button class=\"btn-tiny red\" onclick=\"refreshPost('" + ds[i].guid + "')\"><span>Repair</span></button>";
			}

			_row = _rowTemplate.replace("[ID]", ds[i].id);
			_row = _row.replace("[GUID]", ds[i].guid);
			_row = _row.replace("[TITLE]", _postURL);
			_row = _row.replace("[CREATEDTS]", dateFormat(ds[i].CreateDTS * 1000, "mmmm dS, yyyy"));
			_row = _row.replace("[UPDATEDTS]", dateFormat(ds[i].UpdateDTS * 1000, "mmmm dS, yyyy"));
			_row = _row.replace("[METAS]", ds[i].MetaRecords);
			_row = _row.replace("[REFRESH]", _refresh);
			_row = _row.replace("[HOMEURL]", _homeURL);
			_row = _row.replace("[HOMEURL]", _homeURL);

			document.getElementById( ds[i].guid ).innerHTML = _row;
		}

	} else {
		_returnDiv = _dispDiv.replace("[CLASS]", "sys-error");
	}
	_dispDiv = _dispDiv.replace("[MESSAGE]", data.Message);
	document.getElementById("return-msg").innerHTML = _returnDiv;

    // Return Appropriately
	return false;
}

function showZeroPostDiv() {
	document.getElementById("posts-block").style.display = 'none';
	document.getElementById("first-block").style.display = 'block';
}
