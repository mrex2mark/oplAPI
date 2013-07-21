//
// main.js - Copyright 2013 One Plus Love
//
// Glossary
//
// document.ready -- this is the main document.ready function for all pages.
//
// applyLinkToContainer - make the container of a link click-able (i.e. an <li> containing an <a>)
// capitalizeFirstLetter -- capitalizes the first letter of a string
// clearSuitabilityReport -- clear fields, reset form
// compareTimeStrs -- Compares two time strings in the format "hh:mm:ss" and returns
// deleteSuitability -- deletes a suitability report from the DB by report ID
// downloadReport -- Downloads a report
// getParameterByName -- returns a request parameter by name
// getSessionValue -- calls the DB with the getSessionValue command
// getSuitability -- onclick, grabs the ID and gets that particular suitability report
// getSuitabilityList -- calls the DB with the getSuitabilityList command
// initReportList -- initializes the report datatable
// linkNavItems -- binds click event to nav containing the anchor text on the nav
// loginUser -- validates a username and password
// newLoginUser -- validates a username and password
// pauseWebsite -- Toggles pausing the website.  Displays a loading animation when paused
// populateUserForm -- click handler to open and display the user form with data populated
// resizeReport -- expands div containing datatable for expanded view of the data
// saveSuitability -- saves new suitability report to database
// sendForgotPasswordEmail -- Validates an Email Address.  Displays an error message, or
// setSubNavColumnHeight -- sets height of sub nav column to height of browsers
// setNavIndicator -- checks page variable
// setLanguage -- calls the DB with the setLanguage command
// setUpAccordion -- turns a definition list into an accordion by hiding answers, etc.
// setWrapperHeight -- sets height of wrapper div to height of browsers
// showHideDiv -- shows or hides the next div from a given element.  Works via the "hide" css class
// showGeneratedReport -- interupts mouse click on reports in subnav to send the link to the iframe instead
// showLogin -- Shows the login form
// showReportBtns -- shows/hide the correct buttons below the report
// $.smartresize -- jquery plugin 'smartresize' by Paul Irish, based on debounce by John Hann
// timeToHours -- converts a time string in generic formats like "1 PM", "8:30", "2:15 pm" etc. into
// trace -- Prints a text message to the console.  Optionally indents the message.
// traceDump -- Traces an object to the console, using console.log so the object can be examined
// updateDBFromForm -- Creates, updates or deletes a record in the database from a corresponding form.
// validateSuitability -- validates the suitability report form
//

var debug	= false;
if ( debug ) {
	trace( "Loading main.js..." );		// FARKLE -- this doesn't show up in the console
}


	//
	// Routines
	//

//
// document.ready -- this is the main document.ready function for all pages.  It runs
// before (???) any other document.ready functions
//
$( document ).ready( function() {

	linkNavItems();

	setNavIndicator();

	setWrapperHeight();

	$( window ).smartresize( function () {
		setWrapperHeight();
	});

	setSubNavColumnHeight();

	$( window ).smartresize( function() {
		setSubNavColumnHeight();
	});

});


//
// applyLinkToContainer - make the container of a link click-able (i.e. an <li> containing an <a>)
//
function applyLinkToContainer( container, link ){
	var debug = false;
	if ( debug ) {
		trace( "now in applyLinkToContainer" );
		trace( "link is [" + link + "]" );
		trace( "container is [" + container + "]" );
	}

	$( container ).click( function() {

		if ( debug ) {
			trace( "container clicked\n\n link HREF is [" + $( link ).attr( 'href' ) + "]" );
		}

		document.location = $( link ).attr( 'href' );

	});

	if ( debug ) {
		trace( "done in applyLinkToContainer" );
	}

}


//
// capitalizeFirstLetter -- capitalizes the first letter of a string
//
function capitalizeFirstLetter( string ) {
    return string.charAt( 0 ).toUpperCase() + string.slice( 1 );
}


//
// clearSuitabilityReport -- clear fields, reset form
//
function clearSuitabilityReport() {
	var debug = false;
	if ( debug ) {
		trace( "now in clearSuitabilityReport" );
	}

	var c = confirm( 'You will lose any unsaved information in the current report.\n\n' +
					 'Press "Cancel" to go back and review/save the current report, press "Ok" to clear the current report and continue.' );

	if ( c==true ) {
		if ( debug ) {
			trace( "user pressed OK" );
		}
		$( '#suitabilityTabs input' ).val( '' );
		$( '#suitabilityTabs textarea' ).val( '' );
		$( '#suitabilityTabs input:radio' ).attr( 'checked', false );
		showReportBtns( 'add' );

	} else {
		if ( debug ) {
			trace( "user pressed cancel" );
		}
		return false;
	}

	if ( debug ) {
		trace( "done in clearSuitabilityReport" );
	}
}


//
// compareTimeStrs -- Compares two time strings in the format "hh:mm:ss" and returns
// a string with:
//
//	"-1" if the first < second
//	"0" if the first = second
//	"1" if the first > second
//	"" if the strings can't be parsed
//
// WARNING: this is hard coded for good input.  This does NO checking of input
//
function compareTimeStrs( firstTime, secondTime ) {
	var debug	= false;
	var retVal	= "";
	if ( debug ) {
		trace( "In compareTimeStrs" );
		trace( "firstTime = [" + firstTime + "]", 1 );
		trace( "secondTime = [" + secondTime + "]", 1 );
	}

		// Break down firstTime & secondTime

	var firstHours	= firstTime.substr( 0, 2 );
	var firstMins	= firstTime.substr( 3, 2 );
	var firstSecs	= firstTime.substr( 6 );
	if ( debug ) {
		trace( "firstHours = [" + firstHours + "]", 1 );
		trace( "firstMins = [" + firstMins + "]", 1 );
		trace( "firstSecs = [" + firstSecs + "]", 1 );
	}

	var secondHours	= secondTime.substr( 0, 2 );
	var secondMins	= secondTime.substr( 3, 2 );
	var secondSecs	= secondTime.substr( 6 );
	if ( debug ) {
		trace( "secondHours = [" + secondHours + "]", 1 );
		trace( "secondMins = [" + secondMins + "]", 1 );
		trace( "secondSecs = [" + secondSecs + "]", 1 );
	}

		// Check for inequalities

	var hoursDiff	= parseInt( secondHours ) - parseInt( firstHours );
	var minsDiff	= parseInt( secondMins ) - parseInt( firstMins );
	var secsDiff	= parseInt( secondSecs ) - parseInt( firstSecs );
	if ( debug ) {
		trace( "hoursDiff = [" + hoursDiff + "]", 1 );
		trace( "minsDiff = [" + minsDiff + "]", 1 );
		trace( "secsDiff = [" + secsDiff + "]", 1 );
	}

	if ( hoursDiff > 0 ) {
		retVal	= "-1";				// firstHours < secondHours
	} else if ( hoursDiff < 0 ) {
		retVal	= "1";				// firstHours > secondHours
	} else if ( minsDiff > 0 ) {
		retVal	= "-1";				// firstMins < secondMins, hours equal
	} else if ( minsDiff < 0 ) {
		retVal	= "1";				// firstMins > secondMins, hours equal
	} else if ( secsDiff > 0 ) {
		retVal	= "-1";				// firstSecs < secondSecs, hours & mins equal
	} else if ( secsDiff < 0 ) {
		retVal	= "1";				// firstSecs > secondSecs, hours & mins equal
	} else {
		retVal	= "0";				// all vals equal
	}

	if ( debug ) {
		trace( "Done compareTimeStrs, retVal = [" + retVal + "]", 1 );
	}
	return retVal;
}


//
// deleteSuitability -- deletes a suitability report from the DB by report ID
//
function deleteSuitability() {
	var debug	= false;
	if ( debug ) {
		trace( "In deleteSuitability" );
	}

	var curID = $( "#is_SuitabilityID" ).val();

	var c = confirm( 'Are you sure you want to remove suitability report with ID ' + curID + '? This action cannot be undone.');

	if ( c != true ) {
		return false;
	}

    var jsonStr	= addToJSONStr( jsonStr, "SuitabilityID", curID );

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	callDB( "deleteSuitability", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In deleteSuitability anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );

		if ( returnObj['results'] == 'success' ) {
			trace( 'This suitability report has been removed from the database.');
			getSuitabilityList( true );
			clearSuitabilityReport();
		} else {
			trace( 'Sorry, there was an error while trying to remove the database. We will now reload the page and, if you still see the report listed, please try again to remove it.');
			location.reload(); // must reload clear an JS errors and let them try again ??
		}

	});

	if ( debug ) {
		trace( "Done deleteSuitability" );
	}
}


//
// downloadReport -- Downloads a report
//
function downloadReport( event, link ) {
	var debug	= false;
	if ( debug ) {
		trace( "In downloadReport" );
		trace( "link = [" + link + "]", 1 );
		traceDump( link );
	}

		// Get the previous href tag's url

	var url	= $( link ).closest( "li" )
							.find( "a" )
							.attr( "href" );
	if ( debug ) {
		trace( "url = [" + url + "]", 1 );
	}

		// Get the report name and download it

	if ( url ) {

			// Pause the website

		pauseWebsite( true );

			// Call downloadXMLReport command

		var reportName	= url.substring( url.indexOf( "=" ) + 1 );
		var jsonStr		= addToJSONStr( jsonStr, "FileName", reportName );
		callDB( "downloadXMLReport", jsonStr, function( data ) {
			if ( debug ) {
				trace( "In downloadXMLReport anon function, data = [" + data + "]" );
			}

				// Restart the web site

			pauseWebsite();

				// Get the filename and start download

			var returnObj	= createArray( returnObj, data );
			if ( returnObj[ 'success' ]) {
				if ( debug ) {
					trace( "Starting the download", 1 );
				}
				document.location.href = returnObj[ "fileName" ];
			} else {
				alert( "There was an error downloading your document.  Please try again." );
			}

		});

	}

	if ( debug ) {
		trace( "Done downloadReport", 1 );
	}
}


//
// getParameterByName -- returns a request parameter by name
//
function getParameterByName( name ) {
	var debug	= false;
	var retVal	= "";
	if ( debug ) {
		trace( "In getParameterByName" );
	}

	name 		= name.replace( /[\[]/, "\\\[" ).replace( /[\]]/, "\\\]" );
	var pattern	= "[\\?&]" + name + "=([^&#]*)";
	if ( debug ) {
		trace( "pattern = [" + pattern + "]" );
	}
	var regex 	= new RegExp( pattern );
	var results = regex.exec( window.location.search );

	if ( results != null ) {
		retVal	= decodeURIComponent( results[ 1 ].replace( /\+/g, " " ));
	}
	if ( debug ) {
		trace( "Done getParameterByName, retVal = [" + retVal + "]" );
	}

	return retVal;
}


//
// getSessionValue -- calls the DB with the getSessionValue command
//
function getSessionValue( key, targetDump, nextFunc ) {
	var debug	= false;
	if ( debug ) {
		trace( "In getSessionValue\n\n key is [" + key + "]" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "SessionValueKey", key );

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	callDB( "getSessionValue", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In getSessionValue anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );

		//return returnObj["Value"];

		if ( targetDump ) {
			$( targetDump ).val( returnObj["Value"] );
		} else {
			curUserID = returnObj["Value"];
			return returnObj["Value"];
		}

		if ( nextFunc ) {
			nextFunc();
		}

	});

	if ( debug ) {
		trace( "Done getSessionValue" );
	}
}


//
// getSuitability -- onclick, grabs the ID and gets that particular suitability report
//
function getSuitability( curId ) {
	var debug	= false;
	if ( debug ) {
		trace( "In getSuitability" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "SuitabilityID", curId );

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	$( "#getSuitabilityResults" ).html( "" );

	callDB( "getSuitability", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In getSuitability anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );

		if ( returnObj['results'] == 'success' ) {

			$( '#is_SuitabilityID' 			).val(	curId 											);
			$( '#is_UserID' 				).val(	returnObj.Values['UserID'] 						);
			$( '#is_BuySell' 				).val( 	returnObj.Values['BuySell'] 					);
			$( '#is_CUSIP' 					).val( 	returnObj.Values['CUSIP'] 						);
			$( '#is_Quantity' 				).val( 	returnObj.Values['Quantity'] 					);
			$( '#is_Price' 					).val( 	returnObj.Values['Price'] 						);
			$( '#is_Solicited' 				).val( 	returnObj.Values['Solicited'] 					);
			$( '#is_Account' 				).val( 	returnObj.Values['Account'] 					);
			$( '#is_ECDTC' 					).val( 	returnObj.Values['ECDTC'] 						);
			$( '#is_DateAssessment' 		).val( 	returnObj.Values['DateAssessment'] 				);
			$( '#is_ClientName' 			).val( 	returnObj.Values['ClientName'] 					);
			$( '#is_AccountNumber' 			).val( 	returnObj.Values['AccountNumber'] 				);
			$( '#is_RepresentativeName' 	).val( 	returnObj.Values['RepresentativeName'] 			);
			$( '#is_InvestorProfile' 		).val( 	returnObj.Values['InvestorProfile'] 			);
			$( '#is_InvestmentNarrative' 	).val( 	returnObj.Values['InvestmentNarrative'] 		);
			$( '#is_SecurityType' 			).val( 	returnObj.Values['SecurityType'] 				);
			$( '#is_InvestmentCurrency' 	).val( 	returnObj.Values['InvestmentCurrency'] 			);
			$( '#is_InvestmentAmount' 		).val( 	returnObj.Values['InvestmentAmount'] 			);
			$( '#is_PortfolioPercentage' 	).val( 	returnObj.Values['PortfolioPercentage'] 		);
			$( '#is_ExpectedReturn' 		).val( 	returnObj.Values['ExpectedReturn'] 				);
			$( '#is_CreditRiskLevel' 		).val( 	returnObj.Values['CreditRiskLevel'] 			);
			$( '#is_LiquidityRiskLevel' 	).val( 	returnObj.Values['LiquidityRiskLevel'] 			);
			$( '#is_InterestRateRisk' 		).val( 	returnObj.Values['InterestRateRisk'] 			);
			$( '#is_MarketRisk1' 			).val( 	returnObj.Values['MarketRisk1'] 				);
			$( '#is_OtherRisks' 			).val( 	returnObj.Values['OtherRisks'] 					);
			$( '#is_ClientExperience' 		).val( 	returnObj.Values['ClientExperience'] 			);
			$( '#is_PreviousSimilarTrades' 	).val( 	returnObj.Values['PreviousSimilarTrades'] 		);
			$( '#is_ClientIdeaInstructions' ).val( 	returnObj.Values['ClientIdeaInstructions'] 		);
			$( '#is_GivenRecommendation' 	).val( 	returnObj.Values['GivenRecommendation'] 		);
			$( '#is_RepDueDiligence' 		).val( 	returnObj.Values['RepDueDiligence'] 			);
			$( '#is_RecommendedAgainst' 	).val( 	returnObj.Values['RecommendedAgainst'] 			);
			$( '#is_ExecutionPrice' 		).val( 	returnObj.Values['ExecutionPrice'] 				);
			$( '#is_ClientPrice' 			).val( 	returnObj.Values['ClientPrice'] 				);
			$( '#is_RisklessAccount' 		).val( 	returnObj.Values['RisklessAccount'] 			);
			$( '#is_AuthorizedBy' 			).val( 	returnObj.Values['AuthorizedBy'] 				);

			$( "#suitabilityTabs" ).tabs("option", "selected", 0);

		} else {
			trace( 'Sorry,  this report no longer exists or we have encountered an error.');
		}
	});

	if ( debug ) {
		trace( "Done getSuitability" );
	}
}


//
// getSuitabilityList -- calls the DB with the getSuitabilityList command
//
function getSuitabilityList( refresh ) {
	var debug	= false;
	if ( debug ) {
		trace( "In getSuitabilityList" );
	}

	var gsvJsonStr	= addToJSONStr( gsvJsonStr, "SessionValueKey", 'userID' );
	if ( debug ) {
		trace( "gsvJsonStr = [" + gsvJsonStr + "]" );
	}

		// Get the user ID

	callDB( "getSessionValue", gsvJsonStr, function( data ) {
		if ( debug ) {
			trace( "In getSessionValue anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		var gslJsonStr	= addToJSONStr( gslJsonStr, "UserID", returnObj[ "Value" ] );
		if ( debug ) {
			trace( "gslJsonStr = [" + gslJsonStr + "]" );
		}

		callDB( "getSuitabilityList", gslJsonStr, function( data2 ) {
			if ( debug ) {
				trace( "In getSuitabilityList anon function, data2 = [" + data2 + "]" );
			}

			var gslReturnObj	= createArray( gslReturnObj, data2 );
			if ( debug ) {
				trace( "data2 is [" + data2 + "]" );
			}

			if ( refresh ) {
				if ( debug ) {
					trace( "now clearing rows and destroying datatables" );
				}
				reportTbl.fnDestroy();
				$( 'tr.reportRow' ).remove();
			}

			var curReport;
			var numReports = -1;

			for ( key in gslReturnObj ) {
				if ( key.indexOf( 'list' ) > -1 ) {
					numReports++;
				}
			}

	    		// For each report

	        for ( curReport = 0; curReport < numReports; curReport++ ) {

	        	if ( debug ) {
	        		trace( "in for loop" );
	        	}

	            var curReportKey 			= "list" + curReport
	            var curReportObj 			= gslReturnObj[ curReportKey ];

	            var curID   				= curReportObj[ "ID"];
	            var curClientName 			= curReportObj[ "ClientName" ];
	           	var curAccountNumber 		= curReportObj[ "AccountNumber" ];
	           	var curRepresentativeName	= curReportObj[ "RepresentativeName" ];
	           	var curDateAdded			= curReportObj[ "DateAdded" ];

	            if ( debug ) {
	            	trace(	"curReportKey is 			[" + curReportKey + 			"]" +
	            			"curID is 					[" + curID + 					"]" +
	            			"curClientName is 			[" + curClientName + 			"]" +
	            			"curAccountNumber is 		[" + curAccountNumber + 		"]" +
	            			"curRepresentativeName is 	[" + curRepresentativeName + 	"]" +
	            			"curDateAdded is 			[" + curDateAdded + 			"]" );
	            }

	            $( '#suitabilityReportList > tbody:last' ).append( '' +
	            	'<tr class="reportRow">' +
	            		'<td class="" >' + curID 					+ '</td>' +
	            		'<td class="" >' + curClientName		 	+ '</td>' +
	            		'<td class="" >' + curAccountNumber 		+ '</td>' +
	            		'<td class="" >' + curRepresentativeName 	+ '</td>' +
	            		'<td class="" >' + curDateAdded 			+ '</td>' +
	            	'</tr>' );

	        }

	        initReportList();

		});

	});

	if ( debug ) {
		trace( "Done getSuitabilityList" );
	}
}


//
// displayBlankForm -- show blank form based on template to submit
//
function displayBlankForm() {
	var debug = false;

	if ( debug ) {
		trace( "now in displayBlankForm" );
	}

	//

	if ( debug ) {
		trace( "done in displayBlankForm" );
	}
}


//
// initReportList -- initializes the report datatable
//
function initReportList() {

	reportTbl = $( '#suitabilityReportList' ).dataTable({
		"bJQueryUI": true,
		"bProcessing": true,
		"sScrollY": '80px',
		"bPaginate": false,
		"bAutoWidth": false,
		"bScrollCollapse": true
	});

	$( 'tr.reportRow' ).click( function() {
		getSuitability( $( this ).children( 'td' ).eq( 0 ).text().trim());
		showReportBtns( 'update' );
	});

}


//
// linkNavItems -- binds click event to nav containing the anchor text on the nav
//
function linkNavItems() {
	var debug = false;
	if ( debug ) {
		trace( "now in linkNavItems" );
	}

	$( '#nav ul li' ).click( function() {

		var target = $( this ).children( 'a' ).attr( 'href' );

		if ( debug ) {
			trace( "target is [" + target + "]" );
		}

		document.location = ( target );

	});

	if ( debug ) {
		trace( "now in linkNavItems" );
	}

}


//
// loginUser -- validates a username and password
//
function loginUser() {
	var debug	= false;
	if ( debug ) {
		trace( "In loginUser" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "Username", $( "#username" ).val());
	var jsonStr	= addToJSONStr( jsonStr, "Password", $( "#password" ).val());

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	callDB( "loginUser", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In loginUser anon function, data = [" + data + "]" );
		}

			// Check for success

		if ( data.indexOf( "success" ) > -1 ) {
			location.href = "dashboard.html";
		} else {

			var returnObj	= createArray( returnObj, data );
			var errMsg		= "Unknown error.";
			if ( returnObj[ "error" ]) {
				errMsg	= returnObj[ "error" ];
			}
			$( "#loginResults" ).html( errMsg );
			$( "#username" ).focus();
		}

	});

	if ( debug ) {
		trace( "Done loginUser" );
	}
}


//
// logMessage -- Saves a message to the logs
//
function logMessage( message ) {
	var debug	= false;
	if ( debug ) {
		trace( "In logMessage" );
		trace( "message = [" + message + "]", 1 );
	}

	var jsonStr	= addToJSONStr( jsonStr, "Message", message );
	callDB( "logMessage", jsonStr );

}


//
// newLoginUser -- validates a username and password
//
function newLoginUser() {
	var debug	= false;
	if ( debug ) {
		trace( "In newLoginUser" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "Username", $( "#username" ).val());
	var jsonStr	= addToJSONStr( jsonStr, "Password", $( "#password" ).val());

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	callDB( "newLogin", jsonStr, function( data ) {
		if ( debug ) {
			trace( "In newLoginUser anon function", 1 );
			trace( "data = [" + data + "]", 1 );
		}

		var returnObj	= createArray( returnObj, data );
		if ( returnObj[ "success" ]) {
			if ( debug ) {
				trace( "Valid login", 1 );
			}
			location.href	= "dashboard.html";
		} else if ( returnObj[ "error" ]) {

			var errMsg		= returnObj[ "error" ];
			if ( debug ) {
				trace( "errMsg = [" + errMsg + "]", 1 );
			}
			switch ( errMsg ) {
				case "Username parameter missing or empty":
					$( "#loginResults" ).html( "The Username field is empty.  Please enter your Username." );
					break;

				case "Password parameter missing or empty":
					$( "#loginResults" ).html( "The Password field is empty.  Please enter your Password." );
					break;

				case "Incorrect Username or Password":
					$( "#loginResults" ).html( "The Username or Password is Incorrect.  Please enter your Username and Password." );
					break;

				case "Login outside of schedule":
					$( "#loginResults" ).html( "Your account is not allowed to login at this time.  Please contact your System Administrator." );
					break;

				case "No login schedule":
					$( "#loginResults" ).html( "Your account does not have a login schedule.  Please contact your System Administrator." );
					break;

				case "New IP Address":
					$( loginMessage ).hide();
					$( submitLogIn ).hide();
					var displayMsg	= "We can't find this computer or location in our records.  " +
										"We have sent an email to you so that you can verify this location.  Please check " +
										"your mail before trying to log in again.  Please contact <a href = " +
										"\"mailto:support@gscorporation.com\">support@gscorporation.com</a> if you have any questions.<p />";
										// + "<input id = 'submitLogIn' type = 'button' value = 'Login' " +
										//"onClick = 'javascript: showLogin();' class='big'>";
					$( "#loginResults" ).html( displayMsg );
					$( "#loginTable" ).hide();
					break;

				case "Cannot insert new IP Address":
					$( "#loginResults" ).html( errMsg );
					break;

				case "New user, must change password":
					//$( "#loginResults" ).html( errMsg );
					location.href	= encodeURI( "newuser.html?userID=" + returnObj[ "UserID" ]);
					break;

				case "Password has expired.":
					// $( "#loginResults" ).html( errMsg );
					location.href	= encodeURI( "passwordexpired.html?userID=" + returnObj[ "UserID" ]);
					break;

				default:
					$( "#loginResults" ).html( "Unknown results in newLoginUser" );
					break;
			}

		} else {
			if ( debug ) {
				trace( "Invalid JSON returned", 1 );
			}
			$( "#loginResults" ).html( "Invalid JSON returned" );
		}

	});

	if ( debug ) {
		trace( "Done newLoginUser" );
	}
}


//
// pauseWebsite -- Toggles pausing the website.  Displays a loading animation when paused
//
function pauseWebsite( pause ) {
	var debug	= false;
	if ( debug ) {
		trace( "In pauseWebsite" );
	}

	if (( pause == true )
			|| ( pause == 'stop' )
			|| ( pause == 'pause' )) {

		$( 'body' ).addClass( "loading" );
		var imgURL	= "/modx/img/loading.gif?p" +new Date().getTime();
		$( '#pause' ).css({ backgroundImage: "url(" + imgURL + ")" });

	} else {
		$( 'body' ).removeClass( "loading" );
		$( '#pause' ).css({ backgroundImage: "none" });
	}

	if ( debug ) {
		trace( "Done pauseWebsite", 1 );
	}
}


//
// populateUserForm -- click handler to open and display the user form with data populated
// FARKLE -- this should be generic, and it should take in optional functions to populate
// the form.  The default function should work for most forms, sensing database and field type,
// and setting values appropriately, including nulls
//
function populateUserForm() {
	var debug	= false;
	if ( debug ) {
		trace( "In populateUserForm:\n" +
						"this = [" + this + "]\n" +
						"" );
	}

		// Get the user id for this row

	$( '.curTR' ).removeClass( 'curTR' );
	var id	= $.trim( $( this ).parent().addClass( 'curTR' ).find( 'td' ).first().text());
	if ( debug ) {
		trace( id );
	}

		// Get the user from the database

	var jsonStr	= addToJSONStr( jsonStr, "UserID", id );
	callDB( "getUser", jsonStr, getUserReturn );


	//
	// getUserReturn -- callBack when the getUser command has returned
	//
	function getUserReturn( data ) {
		if ( debug ) {
			trace( "In populateUserForm.getUserReturn, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );
		if ( returnObj[ "results" ] == "success" ) {
			if ( debug ) {
				trace( "call was successful" );
			}

				// Fill the form fields -- FARKLE -- make this generic

			$( "#ufID" ).val( returnObj[ "Values" ][ "ID" ]);
			$( "#ufUserName" ).val( returnObj[ "Values" ][ "UserName" ]);
			$( "#ufExpires" ).val( returnObj[ "Values" ][ "Expires" ]);
			$( "#ufTypeID" ).val( returnObj[ "Values" ][ "TypeID" ]);
			$( "#ufEmail" ).val( returnObj[ "Values" ][ "Email" ]);
			$( "#ufPassword" ).val( returnObj[ "Values" ][ "Password" ]);
			$( "#ufTempLink" ).val( returnObj[ "Values" ][ "TempLink" ]);
			$( "#ufSupervisorID" ).val( returnObj[ "Values" ][ "SupervisorID" ]);
			$( "#ufDateChanged" ).html( returnObj[ "Values" ][ "DateChanged" ]);
			$( "#ufDateAdded" ).html( returnObj[ "Values" ][ "DateAdded" ]);

				// Display the form

			showHideDiv( 'manageUserToggle', 'manageUserList', true );

		} else {
			trace( "Error: can't load this user" );
		}
	}

	if ( debug ) {
		trace( "Done populateUserForm" );
	}
}


//
// resizeReport -- expands div containing datatable for expanded view of the data
//
function resizeReport( resizeAction ) {
	var debug = false;

	if ( debug ) {
		trace( "now in resizeReport\n\n" +
		"resizeAction is [" + resizeAction + "]" );
	}

	if ( resizeAction == 'expand' ) {

			// shrink the height of the nav bar

		$( '#mainContent .subNavColumn' ).css({
												'overflow':'hidden',
												'min-height':'0px'
											}).addClass( 'reduced' );


		$( '#mainContent .subNavColumn' ).animate({
		    height: '0px'
		}, 700, function() {

				// now widen the main content area

			$( '#reportsContent' ).animate({
			    'width': '99%',
			    'padding' : '2.5% .5% 1%'
			}, 2000, function() {
				// Animation complete.
			});

			$("#generateReport").contents().find( ".reportDiv" )
												.addClass( 'expandedReport' )
												.children( '.reportTitle' )
												.append( '<span id = "reduceReport"></span>' );

			$( "#generateReport" ).contents().find( '#reduceReport' ).click( function(e) {
				resizeReport( 'reduce' );
			});

		});


	} else if ( resizeAction == 'reduce' ) {

		$( '#mainContent .subNavColumn' ).css( 'overflow', 'hidden' ).removeClass( 'reduced' );


		$( '#reportsContent' ).animate({
		    'width': '71%',
			'padding' : '2%'
		}, 2000, function() {
			$( '#mainContent .subNavColumn' ).animate({
		    	height: '100%'
	    	}, 700, function() {
		    	setSubNavColumnHeight();
	    	});
		});

		$( "#generateReport" ).contents().find( ".reportDiv" ).removeClass( 'expandedReport' );
		$( "#generateReport" ).contents().find( '#reduceReport' ).remove();

		//$( '#reportControls' ).hide();

	}

	//$( '#expand' ).toggle();
	//$( '#reduce' ).toggle();

	if ( debug ) {
		trace( "done in resizeReport" );
	}

}


//
// saveSuitability -- saves new suitability report to database
//					param - reportType will be 'new' or 'update'
//
function saveSuitability( reportType ) {
	var debug	= false;
	if ( debug ) {
		trace( "In saveSuitability" );
		trace( "reportType is [" + reportType + "]" );
	}

	var command;
	if ( reportType == 'add') {
		command = 'insertSuitability';
	} else if ( reportType == 'update' ) {
		command = 'updateSuitability';
	}

	var jsonStr;

    jsonStr	= addToJSONStr( jsonStr, "UserID", 					$( "#is_UserID" ).val());
    jsonStr	= addToJSONStr( jsonStr, "SuitabilityID", 			$( "#is_SuitabilityID" ).val());
    jsonStr	= addToJSONStr( jsonStr, "BuySell", 				$( "#is_BuySell" ).val());
    jsonStr	= addToJSONStr( jsonStr, "CUSIP", 					$( "#is_CUSIP" ).val());
    jsonStr	= addToJSONStr( jsonStr, "Quantity", 				$( "#is_Quantity" ).val());
    jsonStr	= addToJSONStr( jsonStr, "Price", 					$( "#is_Price" ).val());
    jsonStr	= addToJSONStr( jsonStr, "Solicited", 				$( "#is_Solicited" ).val());
    jsonStr	= addToJSONStr( jsonStr, "Account", 				$( "#is_Account" ).val());
    jsonStr	= addToJSONStr( jsonStr, "ECDTC", 					$( "#is_ECDTC" ).val());
    jsonStr	= addToJSONStr( jsonStr, "DateAssessment", 			$( "#is_DateAssessment" ).val());
    jsonStr	= addToJSONStr( jsonStr, "ClientName", 				$( "#is_ClientName" ).val());
    jsonStr	= addToJSONStr( jsonStr, "AccountNumber",			$( "#is_AccountNumber" ).val());
    jsonStr	= addToJSONStr( jsonStr, "RepresentativeName", 		$( "#is_RepresentativeName" ).val());
    jsonStr	= addToJSONStr( jsonStr, "InvestorProfile",			$( "#is_InvestorProfile" ).val());
    jsonStr	= addToJSONStr( jsonStr, "InvestmentNarrative", 	$( "#is_InvestmentNarrative" ).val());
    jsonStr	= addToJSONStr( jsonStr, "SecurityType", 			$( "#is_SecurityType" ).val());
    jsonStr	= addToJSONStr( jsonStr, "InvestmentCurrency", 		$( "#is_InvestmentCurrency" ).val());
    jsonStr	= addToJSONStr( jsonStr, "InvestmentAmount", 		$( "#is_InvestmentAmount" ).val());
    jsonStr	= addToJSONStr( jsonStr, "PortfolioPercentage", 	$( "#is_PortfolioPercentage" ).val());
    jsonStr	= addToJSONStr( jsonStr, "ExpectedReturn", 			$( "#is_ExpectedReturn" ).val());
    jsonStr	= addToJSONStr( jsonStr, "CreditRiskLevel", 		$( "#is_CreditRiskLevel" ).val());
    jsonStr	= addToJSONStr( jsonStr, "LiquidityRiskLevel", 		$( "#is_LiquidityRiskLevel" ).val());
    jsonStr	= addToJSONStr( jsonStr, "InterestRateRisk", 		$( "#is_InterestRateRisk" ).val());
    jsonStr	= addToJSONStr( jsonStr, "MarketRisk1", 			$( "#is_MarketRisk1" ).val());
    jsonStr	= addToJSONStr( jsonStr, "OtherRisks", 				$( "#is_OtherRisks" ).val());
    jsonStr	= addToJSONStr( jsonStr, "ClientExperience", 		$( "#is_ClientExperience" ).val());
    jsonStr	= addToJSONStr( jsonStr, "PreviousSimilarTrades", 	$( "#is_PreviousSimilarTrades" ).val());
    jsonStr	= addToJSONStr( jsonStr, "ClientIdeaInstructions", 	$( "#is_ClientIdeaInstructions" ).val());
    jsonStr	= addToJSONStr( jsonStr, "GivenRecommendation", 	$( "#is_GivenRecommendation" ).val());
    jsonStr	= addToJSONStr( jsonStr, "RepDueDiligence", 		$( "#is_RepDueDiligence" ).val());
    jsonStr	= addToJSONStr( jsonStr, "RecommendedAgainst", 		$( "#is_RecommendedAgainst" ).val());
    jsonStr	= addToJSONStr( jsonStr, "ExecutionPrice", 			$( "#is_ExecutionPrice" ).val());
    jsonStr	= addToJSONStr( jsonStr, "AuthorizedBy", 			$( "#is_AuthorizedBy" ).val());

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}

	callDB( command, jsonStr, function( data ) {
		if ( debug ) {
			trace( "In saveSuitability anon function, data = [" + data + "]" );
		}

		var returnObj	= createArray( returnObj, data );

		if ( returnObj['results'] == 'success' ) {
			if ( reportType == 'add') {
				$( '#is_SuitabilityID' ).val( returnObj.Values['ID'] ); // set the new report ID from the return
				trace( 'This suitability report has been saved.');
				showReportBtns( 'update' );
				getSuitabilityList( true );
			} else if ( reportType == 'update' ) {
				trace( 'This suitability report has been updated and saved.');
			}
			$( "#suitabilityTabs" ).tabs("option", "selected", 0);
		} else {
			trace( 'Sorry, we encountered an error.\n\n' +
					'Error is [' + returnObj["error"] + ']\n\n' +
					'Result is [' + returnObj["results"] + ']' );
			if ( debug ) {
				trace( 'returnObj["results"] 	is [' + returnObj["results"] 	+']' );
				trace( 'returnObj["error"] 		is [' + returnObj["error"] 		+']' );
			}
		}
	});

	if ( debug ) {
		trace( "Done saveSuitability" );
	}
}


//
// sendForgotPasswordEmail -- Validates an Email Address.  Displays an error message, or
//	Sends an email
//
function sendForgotPasswordEmail( email, msgDivId ) {
	var debug	= false;
	var regMail = /^([_a-zA-Z0-9-]+)(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,3})$/;

	if ( debug ) {
		trace( "In sendForgotPasswordEmail:\n" +
				"email = [" + email + "]\n" +
				"" );
	}

		// Check Email Address
		// If not valid

	if ( regMail.test( email ) == false ) {
		if ( debug ) {
			trace( "Email not valid" );
		}

			// Display Error

		$( "#" + msgDivId ).html( "Sorry, but we can't find that email. Please try again." );

		// Else

	} else {
		if ( debug ) {
			trace( "Email is valid" );
		}

			// Check the database for the Email

		var jsonStr	= addToJSONStr( jsonStr, "Email", email );
		if ( debug ) {
			trace( "jsonStr = [" + jsonStr + "]" );
		}

		callDB( "getUserByEmail", jsonStr, function( data ) {
			if ( debug ) {
				trace( "In getUserByEmail anon function, data = [" + data + "]" );
			}

				// Check the results key

			var returnObj	= createArray( returnObj, data );
			if ( debug ) {
				trace( "returnObj[ \"results\" ] = [" + returnObj[ "results" ] + "]" );
			}
			if ( returnObj[ "results" ] == "success" ) {
				if ( debug ) {
					trace( "Found email in db" );
				}

					// Send the email

				callDB( "sendForgotPasswordEmail", jsonStr, function( data ) {
					if ( debug ) {
						trace( "In sendForgotPasswordEmail anon function, data = [" + data + "]" );
					}

					$( ".initShow" ).hide();
					$( "#" + msgDivId ).show();

						// Tell user to check his email -- FARKLE -- Use jQuery to change color from red to green

					$( "#" + msgDivId ).removeClass("errorStyle").addClass( "successStyle" ).html( "We have " +
														"sent you an email with further instructions" +
														". Please check it now" );

				});
				if ( debug ) {
					trace( "Done Call db command" );
				}

			} else {
				if ( debug ) {
					trace( "Email not found in db" );
				}

					// Tell the user, email not found

				$( "#" + msgDivId ).html( "Sorry, but we can't find that email. Please try again." );
			}

		});


	}
	if ( debug ) {
		trace( "Done sendForgotPasswordEmail" );
	}
}


//
// setSubNavColumnHeight -- sets height of sub nav column to height of browsers
//
function setSubNavColumnHeight() {
	$( '.subNavColumn' ).css( 'min-height', $( window ).height() - $( '#header' ).height());
}


//
// setNavIndicator -- checks page variable
//
function setNavIndicator() {
	var debug = false;
	if ( debug ) {
		trace( "now in setNavIndicator" );
	}

	var thisNav = 'navID';

	if ( debug ) {
		trace( "curCategory is [" + curCategory + "]" );
	}

	switch( curCategory ) {
		case "dashboard":
			thisNav += '8';			// Consider using strings for these -- why are they numbers?
			break;

		case "tradingfloor":
			thisNav += '11';
			break;

		case "research":
			thisNav += '';
			break;

		case "messanges":
			thisNav += '';
			break;

		case "settings":
			thisNav += '18';
			break;

		case "help":
			thisNav += '';
			break;

		case "testing":
			thisNav += '7';
			break;

		case "broker":
			thisNav += '';
			break;

		case "compliance":
			thisNav += '45';
			break;

		case "reports":
			thisNav += '9';
			break;

		case "admin":
			thisNav += '17';
			break;

		default:
			//
			break;
	}

	if ( debug ) {
		trace( "thisNav is [" + thisNav + "]" );
	}

	$( '#' + thisNav ).addClass( 'current' );


	if ( debug ) {
		trace( "done in setNavIndicator" );
	}

}


//
// setLanguage -- calls the DB with the setLanguage command
//
function setLanguage() {
	var debug	= false;
	if ( debug ) {
		trace( "In setLanguage" );
	}

	var jsonStr	= addToJSONStr( jsonStr, "SessionKey", "curLang" );
	var jsonStr	= addToJSONStr( jsonStr, "SessionValue", $( "#langSwitch" ).val());

	if ( debug ) {
		trace( "jsonStr = [" + jsonStr + "]" );
	}


	callDB( "setSessionValue", jsonStr, function( data ) {
		var debug	= false;
		if ( debug ) {
			trace( "In setLanguage anon function, data = [" + data + "]" );
		}

		location.reload();
	});

	if ( debug ) {
		trace( "Done setLanguage" );
	}
}


//
// setUpAccordion -- turns a definition list into an accordian by hiding answers, etc.
//	First param is the ID of the list
//	Second param is a boolean for whether the first answer should be showing or not
//
function setUpAccordion( listID, showFirst, allowShowMultiple ) {
	var debug	= false;
	if ( debug ) {
		trace( "In setUpAccordion" );
	}

		// hide all dd, then show just the first and give it the class 'showing'

	$( 'dl#' + listID + ' dd' ).hide();
	if ( showFirst == true ){
		if ( debug ) {
			trace( "Showing first dd", 1 );
		}
		$( 'dl#' + listID + ' dd' ).eq( 0 ).show().addClass( 'showing' ).prev().addClass( 'showingHeader' );
	}

		// on click of dt

	$( 'dl#' + listID + ' dt' ).click( function() {
		if ( debug ) {
			trace( "In dt onClick function" );
		}
		var curDt = $( this );

			// if the next element after the clicked dd is 'showing' -

		if ( $( curDt ).next().hasClass( 'showing' )){
			if ( debug ) {
				trace( "Next dt has class 'showing', hiding it and removing class", 1 );
			}

				// then hide it and remove the class

			$( curDt ).removeClass( 'showingHeader' ).next().slideToggle( 'slow' ).removeClass( 'showing' );

		} else {
			if ( debug ) {
				trace( "Next dt DOES NOT HAVE class 'showing'", 1 );
			}

			if ( !allowShowMultiple ) {
				if ( debug ) {
					trace( "hiding any other dds", 1 );
				}

					// else hide any showing dds, remove the class 'showing'

				$( '.showing' ).slideToggle( 'slow' ).removeClass( 'showing' );
				$( '.showingHeader' ).removeClass( 'showingHeader' );
			}

				// show the next element and add the class 'showing'

			if ( debug ) {
				trace( "Showing next dd", 1 );
			}
			$( curDt ).next().slideToggle( 'slow' ).addClass( 'showing' );
			$( curDt ).addClass( 'showingHeader' );

		}

	});

	if ( debug ) {
		trace( "done in setUpAccordion" );
	}
}


//
// setWrapperHeight -- sets height of wrapper div to height of browsers
//
function setWrapperHeight() {
	$( '#wrapper' ).css( 'min-height', $( window ).height());
}


//
// showHideDiv -- shows or hides the next div from a given element.  Works via the "hide" css class.
// Note the optional boolean show parameter, can be used to force a div visible or hidden
//
function showHideDiv( boxId, divId, show ) {
	var debug	= false;
	if ( debug ) {
		trace( "In showHideDiv:\n" +
				"boxId = [" + boxId + "]\n" +
				"divId = [" + divId + "]\n" +
				"show = [" + show + "]\n" +
				"" );
	}

		// Check if show is defined

	if ( show == undefined ) {

			// Check if div has hide class

		if ( $( "#" + divId ).hasClass( "hide" )) {
			show	= true;
		} else {
			show	= false;
		}
	}

		// Set visibility

	if ( show ) {
		$( "#" + boxId ).removeClass( 'openBox' ).addClass( 'closeBox' );
		$( "#" + divId ).removeClass( "hide" );
	} else {
		$( "#" + boxId ).removeClass( 'closeBox' ).addClass( 'openBox' );
		$( "#" + divId ).addClass( "hide" );
	}

	if ( debug ) {
		trace( "Done showHideDiv" );
	}
}


//
// showGeneratedReport -- interupts mouse click on reports in subnav to send the link to the iframe instead
//
function showGeneratedReport( e, target ) {
	var debug 	= false;

	if ( debug ) {
		trace( 'report sub nav item clicked' );
	}

		// prevent the link from being followed

	e.preventDefault();

	$( '#preSelect' ).hide();

	if ( debug ) {
		trace( 'target is [' +  target + ']' );
	}

	$( '#generateReport' ).attr( 'src', '' + target );

	var frame = document.getElementById( 'generateReport' );

	frame.src = frame.src;     //force reload

	if ( debug ) {
		trace( 'reloaded, done' );
	}

}


//
// showLogin -- Shows the login form
//
function showLogin() {
	$( "#loginResults" ).html( "" );
	$( "#loginTable" ).show();
}


//
// showReportBtns -- shows/hide the correct buttons below the report
//
function showReportBtns( nowShowing ) {
	if ( nowShowing == 'add' ) {
		$( "#suitabilityTabs" ).tabs( "option", "selected", 0 );
		$( '#saveReport' ).hide();
		$( '#deleteReport' ).hide();
		$( '#addReport' ).show();
		$( '#is_FormType' ).val( 'add' );
	} else if ( nowShowing == 'update' ) {
		$( '#addReport' ).hide();
		$( '#saveReport' ).show();
		$( '#deleteReport' ).show();
		$( '#is_FormType' ).val( 'update' );
	}
}


//
// $.smartresize -- jquery plugin 'smartresize' by Paul Irish, based on debounce by John Hann
//
//		This is a fix on the native window.resize, which is glitchy and not the same across browsers
// 		John Hann's site: http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
// 		Paul Irish's site: http://paulirish.com/2009/throttled-smartresize-jquery-event-handler/
//
// FARKLE -- This should be refactored.  Also, we should doc how this hangs off jQuery.
//

(function($,sr) {

  // debouncing function from John Hann
  var debounce = function (func, threshold, execAsap) {
      var timeout;

      return function debounced () {
          var obj = this, args = arguments;
          function delayed () {
              if (!execAsap)
                  func.apply(obj, args);
              timeout = null;
          };

          if (timeout)
              clearTimeout(timeout);
          else if (execAsap)
              func.apply(obj, args);

          timeout = setTimeout(delayed, threshold || 100);
      };
  }
	// smartresize
	jQuery.fn[sr] = function(fn) {  return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr); };

})( jQuery, 'smartresize' );


//
// timeToHours -- converts a time string in generic formats like "1 PM", "8:30", "2:15 pm" etc. into
// a string formatted "hh:mm:ss".
//
function timeToHours( pTime ) {
	var debug	= false;
	var retVal	= "";

	if ( debug ) {
		trace( "In timeToHours" );
		trace( "pTime = [" + pTime + "]", 1 );
	}

		// ??? the incoming time by regexing it
		// Note that changes need to be made to this regex as pointed out on stack overflow
		//
		// "I also noticed that parseInt was choking on strings like ':30' or ':00' so I changed the
		// regex to capture the minutes without the colon"
		//
		// Any number of digits possibly followed by a colon and 2 digits possibly followed by a colon and 2
		// digits possibly followed by a p or P

	var time	= pTime.match( /(\d+)(?::(\d\d))?\s*(p?)/i );
	if ( debug ) {
		trace( "time = [" + time + "]", 1 );
	}

	if ( time ) {
		var d	= new Date();
		d.setHours( parseInt( time[ 1 ], 10 ) + (( parseInt( time[ 1 ], 10 ) < 12 && time[ 3 ] ) ? 12 : 0 ))
		d.setMinutes( parseInt( time[ 2 ], 10 ) || 0 );
		d.setSeconds( 0 );
		if ( debug ) {
			trace( "d = [" + d + "]", 1 );
		}

		retVal	= d.toTimeString();

		if ( debug ) {
			trace( "retVal.indexOf( \" \" ) = [" + retVal.indexOf( " " ) + "]", 1 );
		}
		retVal	= retVal.substr( 0, retVal.indexOf( " " ));

	}

	if ( debug ) {
		trace( "Done timeToHours, retVal = [" + retVal + "]", 1 );
	}

	return retVal;
}


//
// trace -- Prints a text message to the console.  Optionally indents the message.
//
function trace( message, tabstop ) {

	if ( tabstop == undefined ) {
		tabstop	= 0;
	}

	if ( window[ 'console' ] !== undefined ) {
		for ( var curTab = 0; curTab < tabstop; curTab++ ) {
			message	= "    " + message;
		}
		console.log( message );
	}
}


//
// traceDump -- Traces an object to the console, using console.log so the object can be examined
//
function traceDump( object ) {

	if ( window[ 'console' ] !== undefined ) {
		console.log( "*** Dumped to Console:" );
		console.log( object );
	}
}


//
// updateDBFromForm -- Creates, updates or deletes a record in the database from a corresponding form.
//
function updateDBFromForm( updateType, updateCommand, formName, idPrefix, idSuffix ) {
	var debug	= false;
	if ( debug ) {
		trace( "In updateDBFromForm:\n" +
				"updateType = [" + updateType + "]\n" +
				"updateCommand = [" + updateCommand + "]\n" +
				"formName = [" + formName + "]\n" +
				"idPrefix = [" + idPrefix + "]\n" +
				"idSuffix = [" + idSuffix + "]\n" +
				"" );
	}

		// Build the json String

		// run the command via call db

		// if insert or update
			// repopuplate the form
		// else
			// remove the form

	if ( debug ) {
		trace( "Done updateDBFromForm" );
	}

}


//
// validateSuitability -- validates the suitability report form
//
//
function validateSuitability() {
	var debug = false;

	if ( debug ) {
		trace( "now in validateSuitability" );
	}

	$( "#suitabilityForm" ).validate({

		submitHandler: function( form ) {

				if ( debug ) {
					trace( "now in submit handler" );
					trace( "FormType is [" + $( '#is_FormType' ).val() + "]" );
				}

					//submit type is either 'add' or 'update'
				saveSuitability( $( '#is_FormType' ).val());
			},

		invalidHandler: function( form, validator ) {

				if ( debug ) {
					trace( "now in error handler" );
				}

				var errors = validator.numberOfInvalids();

				trace( 'Please fill in all required fields. You missed ' + errors + ' fields.' );
			},

		rules: {

			is_BuySell: "required",
		    is_CUSIP: "required",
		    is_Quantity: {
				required: true,
				number: true
			},
		    is_Price: {
				required: true,
				number: true
			},
		    is_Solicited: "required",
		    is_Account: {
			    required: function(element) {
				    return $( '#account' ).attr( 'checked' ) == 'checked';
				}
      		},
		    is_ECDTC: {
				required: function(element) {
				    return $( '#ecdtc' ).attr( 'checked' ) == 'checked';
				}
      		},
		    is_DateAssessment: {
				required: true,
				date: true
			},
		    is_ClientName: "required",
		    is_AccountNumber: "required",
		    is_RepresentativeName: "required",
		    is_InvestorProfile: "required",
		    is_InvestmentNarrative: "required",
		    is_SecurityType: "required",
		    is_InvestmentCurrency: "required",
		    is_InvestmentAmount: {
				required: true,
				number: true
			},
		    is_PortfolioPercentage: {
				required: true,
				number: true
			},
		    is_ExpectedReturn: "required",
		    is_CreditRiskLevel: "required",
		    is_LiquidityRiskLevel: "required",
		    is_InterestRateRisk: "required",
		    is_MarketRisk1: "required",
		    is_OtherRisks: "required",
		    is_ClientExperience: "required",
		    is_PreviousSimilarTrades: "required",
		    is_ClientIdeaInstructions: "required",
		    is_GivenRecommendation: "required",
		    is_RepDueDiligence: "required",
		    is_RecommendedAgainst: "required",
		    is_ExecutionPrice: {
				required: true,
				number: true
			},
		    is_AuthorizedBy: "required"

		}

	});

	if ( debug ) {
		trace( "done in validateSuitability" );
	}

}

if ( debug ) {
	trace( "Done loading main.js" );
}