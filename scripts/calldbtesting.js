//
// calldbtesting.js -- Copyright 2013 One Plus Love, Inc.
//
// Example routines for testing the dbCall.aspx page.  These routines require that jQuery and the calldb.js
// libraries be loaded.  These routines should be used as a guideline for writing HTML pages that can use
// the dbCall page.
//
// deleteDBTableRecord -- calls the DB with the deleteDBTableRecord command
// getDBTableList -- calls the gsiAPI with the getDBTableList command
// getDBTableRecord -- calls the gsiAPI with the getDBTableRecord command
// getDBTableStructure -- calls the DB with the getDBTableStructure command
// getFullListOfPermissions -- calls the DB with the getFullListOfPermissions command
// getSPList -- calls the DB with the getSPList command
// getSQLList -- calls the DB with the getSQLList command
// insertDBTableRecord -- calls the DB with the insertDBTableRecord command
// logMessage -- calls the DB with the logMessage command
// updateDBTableRecord -- calls the DB with the updateDBTableRecord command
//
// Micellaneous Methods:
//

var calldbtesting_debug	= false;

if ( calldbtesting_debug ) {
	trace( "In calldbtesting.js" );
}


//
// deleteDBTableRecord -- calls the DB with the deleteDBTableRecord command
//
function deleteDBTableRecord() {
	var debug	= false;
	if ( debug ) {
		trace( "In deleteDBTableRecord" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "ID", $( "#dr_ID" ).val());
	jsonStr		= addToJSONStr( jsonStr, "TableName", $( "#dr_TableName" ).val());
	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}
	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#deleteDBTableRecordResults" ).html( "" );

	callDB( "deleteDBTableRecord", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In deleteDBTableRecord anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "deleteDBTableRecordResults" );

	});

	if ( debug ) {
		trace( "Done deleteDBTableRecord" );
	}
}


//
// getDBTableList -- calls the gsiAPI with the getDBTableList command
//
function getDBTableList() {
	var debug	= false;
	if ( debug ) {
		trace( "In getDBTableList" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "TableName", $( "#gdtl_TableName" ).val());
	jsonStr		= addToJSONStr( jsonStr, "WhereClause", $( "#gdtl_WhereClause" ).val());

	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}
	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#getDBTableListResults" ).html( "" );

	callDB( "getDBTableList", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In getDBTableList anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "getDBTableListResults" );

	});

	if ( debug ) {
		trace( "Done getDBTableList" );
	}
}


//
// getDBTableNames -- calls the gsiAPI with the getDBTableNames command
//
function getDBTableNames() {
	var debug	= false;
	if ( debug ) {
		trace( "In getDBTableNames" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "TableName", $( "#gdtn_TableName" ).val());

	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}
	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#getDBTableNamesResults" ).html( "" );

	callDB( "getDBTableNames", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In getDBTableNames anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "getDBTableNamesResults" );

	});

	if ( debug ) {
		trace( "Done getDBTableNames" );
	}
}


//
// getDBTableRecord -- calls the gsiAPI with the getDBTableRecord command
//
function getDBTableRecord() {
	var debug	= false;
	if ( debug ) {
		trace( "In getDBTableRecord" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "TableName", $( "#gdtr_TableName" ).val());
	jsonStr		= addToJSONStr( jsonStr, "ID", $( "#gdtr_ID" ).val());

	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}
	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#getDBTableRecordResults" ).html( "" );

	callDB( "getDBTableRecord", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In getDBTableRecord anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "getDBTableRecordResults" );

	});

	if ( debug ) {
		trace( "Done getDBTableRecord" );
	}
}


//
// getDBTableStructure -- calls the DB with the getDBTableStructure command
//
function getDBTableStructure() {
	var debug	= false;
	if ( debug ) {
		trace( "In getDBTableStructure" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "TableName", $( "#gts_TableName" ).val());
	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}
	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#getDBTableStructureResults" ).html( "" );

	callDB( "getDBTableStructure", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In getDBTableStructure anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "getDBTableStructureResults" );

	});

	if ( debug ) {
		trace( "Done getDBTableStructure" );
	}
}


//
// getSPList -- calls the DB with the getSPList command
//
function getSPList() {
	var debug	= false;
	if ( debug ) {
		trace( "In getSPList" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "StoredProcedure", $( "#gspl_StoredProcedure" ).val());
	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}

		// Get key value pairs
		// FARKLE -- splitting an empty string gives a false pair.  Also, use trim function to allow
		// arbitrary spacing

	if ( $( "#gspl_keyValuePairs" ).val().length > 0 ) {

			// Get the lines into an array

		var keyValuePairs	= $( "#gspl_keyValuePairs" ).val().split( "\n" );
		if ( debug ) {
			trace( "keyValuePairs = [" + keyValuePairs + "]", 1 );
		}
		for ( var curKeyValue = 0; curKeyValue < keyValuePairs.length; curKeyValue++ ) {
			var tempKeyValue	= keyValuePairs[ curKeyValue ].split( "=" );
			if ( tempKeyValue.length == 2 ) {
				jsonStr	= addToJSONStr( jsonStr, tempKeyValue[ 0 ].trim(), tempKeyValue[ 1 ].trim());
			} else {
				jsonStr	= addToJSONStr( jsonStr, tempKeyValue[ 0 ].trim(), "" );
			}
		}
	}

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#getSPListResults" ).html( "" );

	callDB( "getSPList", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In getSPList anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "getSPListResults" );

	});

	if ( debug ) {
		trace( "Done getSPList" );
	}
}


//
// getSQLList -- calls the DB with the getSQLList command
//
function getSQLList() {
	var debug	= false;
	if ( debug ) {
		trace( "In getSQLList" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "SQL", $( "#gsqll_SQL" ).val());
	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#getSQLListResults" ).html( "" );
	callDB( "getSQLList", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In getSQLList anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "getSQLListResults" );

	});

	if ( debug ) {
		trace( "Done getSQLList" );
	}
}


//
// insertDBTableRecord -- calls the DB with the insertDBTableRecord command
//
function insertDBTableRecord() {
	var debug	= false;
	if ( debug ) {
		trace( "In insertDBTableRecord" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "TableName", $( "#ir_TableName" ).val());
	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}

		// Get key value pairs
		// FARKLE -- splitting an empty string gives a false pair.  Also, use trim function to allow
		// arbitrary spacing

	if ( $( "#ir_keyValuePairs" ).val().length > 0 ) {
		var keyValuePairs	= $( "#ir_keyValuePairs" ).val().split( "\n" );
		if ( debug ) {
			trace( "keyValuePairs = [" + keyValuePairs + "]", 1 );
		}
		for ( var curKeyValue = 0; curKeyValue < keyValuePairs.length; curKeyValue++ ) {
			var tempKeyValue	= keyValuePairs[ curKeyValue ].split( "=" );
			if ( tempKeyValue.length == 2 ) {
				var jsonStr			= addToJSONStr( jsonStr, tempKeyValue[ 0 ].trim(), tempKeyValue[ 1 ].trim());
			}
		}
	}

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#insertDBTableRecordResults" ).html( "" );

	callDB( "insertDBTableRecord", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In insertDBTableRecord anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "insertDBTableRecordResults" );

	});

	if ( debug ) {
		trace( "Done insertDBTableRecord" );
	}
}


//
// logMessage -- calls the DB with the logMessage command
//
function logMessage() {
	var debug	= true;
	if ( debug ) {
		trace( "In logMessage" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "Message", $( "#lm_Message" ).val());
	var jsonStr	= addToJSONStr( jsonStr, "Tabstop", $( "#lm_Tabstop" ).val());

	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}
	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#logMessageResults" ).html( "" );
	callDB( "logMessage", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In logMessage anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "logMessageResults" );

	});

	if ( debug ) {
		trace( "Done logMessage" );
	}
}


//
// updateDBTableRecord -- calls the DB with the updateDBTableRecord command
//
function updateDBTableRecord() {
	var debug	= false;
	if ( debug ) {
		trace( "In updateDBTableRecord" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "ID", $( "#udtr_ID" ).val());
	var jsonStr	= addToJSONStr( jsonStr, "TableName", $( "#udtr_TableName" ).val());
	if ( $( "#verbose" ).is( ":checked" )) {
		jsonStr	= addToJSONStr( jsonStr, "verbose", "true" );
	}

		// Get key value pairs
		// FARKLE -- splitting an empty string gives a false pair.  Also, use trim function to allow
		// arbitrary spacing

	if ( $( "#udtr_keyValuePairs" ).val().length > 0 ) {
		var keyValuePairs	= $( "#udtr_keyValuePairs" ).val().split( "\n" );
		for ( var curKeyValue = 0; curKeyValue < keyValuePairs.length; curKeyValue++ ) {
			var tempKeyValue	= keyValuePairs[ curKeyValue ].split( "=" );
			if ( tempKeyValue.length > 1 ) {
				var jsonStr			= addToJSONStr( jsonStr, tempKeyValue[ 0 ].trim(), tempKeyValue[ 1 ].trim());
			}
		}
	}

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#updateDBTableRecordResults" ).html( "" );

	callDB( "updateDBTableRecord", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In updateDBTableRecord anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		dumpArray( returnObj, "updateDBTableRecordResults" );

	});

	if ( debug ) {
		trace( "Done updateDBTableRecord" );
	}
}

if ( calldbtesting_debug ) {
	trace( "Done calldbtesting.js" );
}

