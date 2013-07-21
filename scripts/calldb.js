//
// calldb.js -- Copyright 2013 One Plus Love, Inc.
//
// Provides AJAX support for html pages to query the GSI Trading Platform database.  This library requires
// that jQuery is loaded.
//
// callDB -- calls the database via jQuery
// createArray -- creates a Javascript associative array from an incoming JSON
// dumpArray -- dumps a Javascript associative array to an incoming HTML div.  Prepends a
// addToCSVStr -- adds a value to a csv string
// addToJSONStr -- adds a key/value pair to a json string
// getTimeStamp -- returns a timestamp formatted as H:MM:SS M/D/YY
// JSONify -- takes in a string and prepends control characters and quotes with forward slashes
// uploadFile -- this method takes in a file upload form and a dbCall command, and sets the form
// aim -- This object handles file uploads via a hidden iframe and ajax calls.
//

var debug	= false;

if ( debug ) {
	trace( "Loading calldb.js" );
}

var returnObj	= new Object;			// This object contains the returned key/value pairs


//
// callDB -- calls the database via jQuery
//
function callDB( command, params, handler, verbose, handlerParams ) {
	var debug	= false;

	if ( debug ) {
		trace( "In callDB:\n" +
				"command = [" + command + "]\n" +
				"params = [" + params + "]\n" +
				"handler = [" + handler + "]\n" +
				"verbose = [" + verbose + "]\n" +
				"handlerParams = [" + handlerParams + "]\n" +
				"" );
	}

		// Get the parameters into JSON notation
		// Add the command parameter

	var fullParams	= '"command" : "' + command + '"';

		// Add any additional parameters

	if (( params != "" )
			&& ( params != undefined )
			&& ( params != null		 )) {

			// We must check if params' last character is either a comma or a comma then a space
			// This is because IE will crash the page if you submit a json string ending in a comma

		params = $.trim( params );		// trim whitespace or line breaks from beginning or end of string

			// check to see if the final character is a comma, if it is: remove it

		paramsLastChar 			= params.charAt( params.length - 1 );

		if ( paramsLastChar == ',' ) {
			if ( debug ) {
				trace( "final character is a comma, now removing" );
			}

				// remove the last char

			params = params.slice( 0, params.length - 1 );

			if ( debug ) {
				trace( "final comma was removed, params is now [" + params + "]" );
			}
		}

		fullParams	+= ", " + params;
	}

		// Append the verbose parameter

	if ( verbose != null ) {
		fullParams += ', "verbose" : "true"';
	}

		// Add curly brackets

	fullParams = "{" + fullParams + "}";
	if ( debug ) {
		trace( "fullParams = [" + fullParams + "]" );
	}

		// Convert to object

	try {
		var postObj = $.parseJSON( fullParams );
	} catch ( ex ) {
		if ( debug ) {
			trace( "Exception trying to parseJSON: ex.description = [" + ex.description + "]" );
		}
	}
	if ( debug ) {
		traceDump( postObj );
	}

		// Call the dbCall page and parse the results

	if ( debug ) {
		trace( "Calling [" + API_URL + "]" );
	}
	$.post( API_URL, postObj, function( data ) {

		if ( debug ) {
			trace( "In callback function" );
		}

			// Call any passed in function

		if ( handler ) {
			if ( debug ) {
				trace( "Calling handler\n\n data is [" + data + "]" );
			}
			if ( handlerParams ){
				handler( data, handlerParams );
			} else {
				handler( data );
			}

		}

	});

}


//
// createArray -- creates a Javascript associative array from an incoming JSON
// string
//
function createArray( arrayObj, data ) {
	var debug	= true;
	if ( debug ) {
		trace( "In createArray:\n" +
				"arrayObj = [" + arrayObj + "]\n" +
				"data = [" + data + "]\n" +
				"" );
		if ( arrayObj ) {
			$.each( arrayObj, function( key, value ) {
				trace( "arrayObj[ \"" + key + "\" ] = [" + arrayObj[ key ] + "]" );
			});
		}
	}

		//
		// Convert the result into a Javascript object
		//

	var JSONStr	= $.trim( data );

	// JSONStr = "{" + JSONStr + "}";		// FARKLE -- REST API should return the braces
	if ( debug ) {
		trace( "JSONStr = [" + JSONStr + "]" );
	}

	try {
		arrayObj = $.parseJSON( JSONStr );
		if ( debug ) {
		 	$.each( arrayObj, function( key, value ) {
				trace( "arrayObj[ \"" + key + "\" ] = [" + arrayObj[ key ] + "]" );
			});
		}
	} catch( e ) {
		if ( debug ) {
			trace( "Error: " + e );
		}
		arrayObj	= { "results" : "error",
						"error" : "Invalid JSON in create array in calldb.js",
						"exception" : e,
						"json" : data };
	}

	if ( debug ) {
		trace( "Done createArray" );
	}

	return arrayObj;
}


//
// dumpArray -- dumps a Javascript associative array to an incoming HTML div.  Prepends a
// timestamp.
//
function dumpArray( arrayObj, divid ) {
	var debug	= false;

	if ( debug ) {
		trace( "In dumpArray:\n" +
				"arrayObj = [" + arrayObj + "]\n" +
				"divid = [" + divid + "]" );
		$.each( arrayObj, function( key, value ) {
			trace( "arrayObj[ \"" + key + "\" ] = [" + arrayObj[ key ] + "]" );
		});
	}

		// Dump the array to the trace field

	var todayStr	= getTimeStamp();
	var arrayText = "Returned at " + todayStr + ":<br>\n";
	$.each( arrayObj, function( key, value ) {
		if ( debug ) {
			trace( "key = [" + key + "]\n" +
					"value = [" + value + "]" );
		}

		if ( typeof arrayObj[ key ] == "object" ) {

			arrayText 	+= "&nbsp;&nbsp;arrayObj[ \"" + key + "\" ] = [<br>\n";

			var valObj	= arrayObj[ key ];
			$.each( valObj, function( key, value ) {
				arrayText 	+= "&nbsp;&nbsp;&nbsp;&nbsp;valObj[ \"" + key + "\" ] = [" +
								valObj[ key ] + "]<br>\n";

			});
			arrayText	+= "&nbsp;&nbsp;]<br>\n";

		} else {
			arrayText	+= "&nbsp;&nbsp;arrayObj[ \"" + key + "\" ] = [" + arrayObj[ key ] + "]<br>\n";
		}
	});

	if ( debug ) {
		trace( "arrayText = [" + arrayText + "]" );
	}

	$( "#" + divid ).html( arrayText );

	if ( debug ) {
		trace( "Done dumpArray" );
	}

}


//
// dumpData -- dumps the raw data from a jQuery ajax return into a results div
//
function dumpData( data, divid ) {
	var debug	= false;
	if ( debug ) {
		trace( "In dumpData:\n" +
				"data = [" + data + "]\n" +
				"divid = [" + divid + "]" );
	}

		// Dump the array to the trace field

	var todayStr	= getTimeStamp();
	var returnText = "Returned at " + todayStr + ":<br>\n[" + data + "]<br>\n";

	if ( debug ) {
		trace( "returnText = [" + returnText + "]" );
	}

	$( "#" + divid ).html( returnText );

	if ( debug ) {
		trace( "Done dumpData" );
	}

}


//
// addToCSVStr -- adds a value to a csv string, prepending a comma before the new value if
// necessary
//
function addToCSVStr( csvString, newVal ) {
	var debug	= false;
	if ( debug ) {
		trace( "In addToCSVStr:\n" +
				"csvString = ["+ csvString + "]\n"+
				"newVal = ["+ newVal + "]\n" +
				"" );
	}

	if ( csvString == undefined ) {
		csvString	= "";
	}

	if ( csvString.length > 0 ) {
		csvString	+= ", ";
	}
	csvString	+= newVal;

	if ( debug ) {
		trace( "Done addToCSVStr, csvString = [" + csvString + "]" );
	}
	return csvString;
}


//
// addToJSONStr -- adds a key/value pair to a json string.  Note that this does not add curly brackets.
// If the incoming value is another JSON string, it should already have the curly brackets added.
//
function addToJSONStr( jsonString, key, value ) {
	var debug	= false;
	if ( debug ) {
		trace( "In addToJSONStr:\n" +
				"jsonString = [" + jsonString + "]\n" +
				"key = [" + key + "]\n" +
				"value = [" + value + "]\n" +
				"" );
	}

	var retVal	= "";

	if ( jsonString == undefined ) {
		jsonString = "" ;
	}

	if ( value == undefined ) {
		value = "" ;
	}
	var valTrimmed	= $.trim( value );


		// Check if adding a simple value or if nesting another JSON string

	if (( valTrimmed.substr( 0, 1 ) == "{" )
			&& ( valTrimmed.substr( valTrimmed.length - 1 ) == "}" )) {
		if ( debug ) {
			trace( "value is a JSON string" );
		}

			// value is a JSON object

		retVal	= addToCSVStr( jsonString, "\"" + key + "\" : " + value + "" );
	} else {
		if ( debug ) {
			trace( "value is a normal string" );
		}

			// value is a string

		value = JSONify( value );

		retVal	= addToCSVStr( jsonString, "\"" + key + "\" : \"" + value + "\"" );
	}

	if ( debug ) {
		trace( "Done addToJSONStr, retVal = [" + retVal + "]" );
	}
	return retVal;
}


//
// getTimeStamp -- returns a timestamp formatted as H:MM:SS M/D/YY
//
function getTimeStamp() {

	var today		= new Date();
	var todayStr	= "";

	todayStr	+= today.getHours() + ":";
	todayStr	+= ( today.getMinutes().toString().length == 1 ) ? "0" + today.getMinutes() : today.getMinutes()
	todayStr	+= ":";
	todayStr	+= ( today.getSeconds().toString().length == 1 ) ? "0" + today.getSeconds() : today.getSeconds();
	todayStr	+= " ";
	todayStr		+= ( today.getMonth() + 1 ) + "/" +
						today.getDate() + "/" +
						today.getFullYear();

	if ( debug ) {
		trace( "todayStr = [" + todayStr + "]" );
	}

	return todayStr;
}

//
// JSONify -- takes in a string and prepends control characters and quotes with forward slashes
// this code replaces back slashes, quotes and line breaks
//
// This should handle control characters and other characters as per json.org
//
function JSONify( string ) {
	var debug = false;

	if ( debug ) {
		trace( "now in JSONify\n\n" +
		"string is [" + string  + "]" );
	}

	string 	= string + ""; 						// make sure its a string

	string  = string.replace( /[\x00-\x08\x0B-\x0C\x0E-\x1f\x7F]/g, '' );	// Strip most control chars
	string 	= string.replace( /\t/g, "\\t" );								// Escape tab
	string 	= string.replace( /\n/g, "\\n" );								// Escape new line
	string 	= string.replace( /\r/g, "\\r" );								// Escape carriage return
	string 	= string.replace( /\\/g, "\\\\" );								// Escape Backslash
	string 	= string.replace( /\"/g, "\\\"" );								// Escape double quote

	if ( debug ) {
		trace( "string is now [" + string + "]" );
	}

	return string;

	if ( debug ) {
		trace( "done in JSONify" );
	}


}


if ( debug ) {
	trace( "Done loading calldb.js" );
}


//
// uploadFile -- this method takes in a file upload form and a dbCall command, and sets the form
// to upload via upload.php.  Upload.php will then call dbCall to process the file based on the incoming
// dbCall command.
//
function uploadFile( command, formID, handler, verbose ) {
	var debug	= true;

	if ( debug ) {
		trace( "In uploadFile:" );
		trace( "command = [" + command + "]", 1 );
		trace( "formID = [" + formID + "]", 1 );
		trace( "handler = [" + handler + "]", 1 );
		trace( "verbose = [" + verbose + "]", 1 );
	}

		// Get the incoming form

	var formObj	= document.getElementById( formID );
	if ( debug ) {
		trace( "formObj = [" + formObj + "]", 1 );
	}

		// Add the command as a hidden input field

	var cmdField	= document.createElement( "input" );
	cmdField.type	= "hidden";
	cmdField.name	= "command";
	cmdField.value	= command;
	formObj.appendChild( cmdField );

		// Add MAX_FILE_SIZE as a hidden input field

	var mfsField	= document.createElement( "input" );
	mfsField.type	= "hidden";
	mfsField.name	= "MAX_FILE_SIZE";
	mfsField.value	= "1100000000";
	formObj.appendChild( mfsField );

		// Add the action

	formObj.action	= UPLOAD_URL;

		// Call aim to handle the submit

	aim.submit( formObj, { 'onStart' : null, 'onComplete' : function( data ) {
		if ( debug ) {
			trace( "In onComplete from aim" );
			trace( "data = [" + data + "]", 1 );
		}

			// Remove the hidden fields

		try {
			if ( debug ) {
				trace( "Trying to remove command and MAX_FILE_SIZE fields", 1 );
			}
			formObj.removeChild( cmdField );
			formObj.removeChild( mfsField );
		} catch ( err ) {
			if ( debug ) {
				trace( "Exception caught: [" + err.message + "]", 1 );
			}
		}

			// Call the callback

		if ( handler ) {
			if ( debug ) {
				trace( "Calling handler", 1 );
			}
			handler( data );
		}

	}});

	if ( debug ) {
		trace( "Done uploadFile", 1 );
	}

	return false;

}


//
// aim -- This object handles file uploads via a hidden iframe and ajax calls.
//
// Developed from AJAX IFRAME METHOD ( AIM ) at http://www.webtoolkit.info/
//
aim	= {

 	//
 	// frame -- creates a hidden iFrame and adds the onComplete method to it
 	//
	frame : function( uplForm ) {
		var debug	= false;
		if ( debug ) {
			trace( "In aim.frame" );
			trace( "uplForm = [" + uplForm + "]", 1 );
		}

			// Generate random name

		var iFrameName	= 'f' + Math.floor( Math.random() * 99999 );
		if ( debug ) {
			trace( "iFrameName = [" + iFrameName + "]", 1 );
		}

			// Add a hidden iframe to the document

		var newDiv			= document.createElement( 'DIV' );
		newDiv.innerHTML	= '<iframe style = "display:none" ' +
								'src = "about:blank" ' +
								'id = "' + iFrameName + '" ' +
								'name = "' + iFrameName + '" ' +
								'onload = "aim.loaded( \'' + iFrameName + '\' );">' +
								'</iframe>';
		if ( debug ) {
			trace( "newDiv.innerHTML = [" + newDiv.innerHTML + "]", 1 );
		}

		document.body.appendChild( newDiv );

			// Get a handle to the iframe

		var iFrame	= document.getElementById( iFrameName );

			// If the form object exists and has a function named onComplete
			// Copy the onComplete method from the form to the mystery element

		if ( uplForm && typeof( uplForm.onComplete ) == 'function' ) {
			if ( debug ) {
				trace( "Copying onComplete method to new iframe", 1 );
			}
			iFrame.onComplete	= uplForm.onComplete;
		}

		if ( debug ) {
			trace( "Done aim.frame", 1 );
		}
		return iFrameName;
	},


 	//
 	// form -- sets the incoming form's target to the hidden iframe
 	//
	form : function( formObj, iFrameName ) {
		formObj.setAttribute( 'target', iFrameName );
	},


 	//
 	// submit -- calls frame to create the hidden iframe and hook in the callback methods.
 	// Calls form to set the target to the name of the form to the new hidden iframe.
 	// Calls the onStart method if it exists and returns its value, or true.
 	//
	submit : function( formObj, callbackObj ) {
		var debug	= false;
		if ( debug ) {
			trace( "In aim.submit" );
			trace( "formObj = [" + formObj + "]", 1 );
			trace( "callbackObj = [" + callbackObj + "]", 1 );
		}

		aim.form( formObj, aim.frame( callbackObj ));

		if ( callbackObj && typeof( callbackObj.onStart ) == 'function' ) {
			return callbackObj.onStart();
		} else {
			return true;
		}

		if ( debug ) {
			trace( "Done aim.submit", 1 );
		}

	},


 	//
 	// loaded
 	//
	loaded : function( iFrameID ) {
		var debug	= false;
		if ( debug ) {
			trace( "In aim.loaded" );
			trace( "iFrameID = [" + iFrameID + "]", 1 );
		}

			// Get a handle to the incoming frame

		var iFrame	= document.getElementById( iFrameID );

			// Get the inner document in the frame

		if ( iFrame.contentDocument ) {
			var innerDoc	= iFrame.contentDocument;
		} else if ( iFrame.contentWindow ) {
			var innerDoc	= iFrame.contentWindow.document;
		} else {
			var innerDoc	= window.frames[ iFrameID ].document;		// Is this cross browser?
		}

		if ( debug ) {
			trace( "innerDoc = [" + innerDoc + "]", 1 );
		}

			// Get return data, or set error if none

		var data	= innerDoc.body.innerHTML;
		if ( innerDoc.location.href == "about:blank" ) {
			if ( debug ) {
				trace( "about:blank found in iFrame", 1 );
			}
			data	= "";
			data	= addToJSONStr( data, "results", "error" );
			data	= addToJSONStr( data, "error", "No results from server" );
		}

			// Call onComplete

		if ( typeof( iFrame.onComplete ) == 'function' ) {
			if ( debug ) {
				trace( "Calling iFrame.onComplete", 1 );
			}
			iFrame.onComplete( data );
		}

		if ( debug ) {
			trace( "Done aim.loaded", 1 );
		}
	}

}


