//
// global.js -- Copyright 2013 One Plus Love, LLC
//
//
// Holds global constants and variables
//
if ( globals != "globals" ) {
	var globals			= "globals";		// Prevent multiple includes

	var debug			= false;
	if ( debug ) {
		alert( "In globals" );
	}


		//
		// Constants
		//


	var API_URL			= window.location.protocol + "//" +
							window.location.host +
							"/php/oplAPI.php";
	var DOMAIN_NAME		= window.location.host.toString();
	var UPLOAD_URL		= window.location.protocol + "//" +
							window.location.host +
							"/php/upload.php";

	var GET_SVAL		= "SessionValueKey";
	var SKEY_UID		= "userID";


		//
		// Globals
		//


	var curUserID;					// Use getSessionValue to get this from the server

	var curCategory;				// Main nav icon
	var subNavAccrdParent 	= 0; 	// default is 0 -- what is this? Secondary Nav

		// datatable names, need to be accessible globally

	var reportTbl;
	var formListTbl;

	if ( debug ) {
		alert( "Done globals" );
		debug	= false;
	}

}
//
//* End Global Variables