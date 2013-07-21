<?php
//
// oplAPI.php -- Copyright 2013 One Plus Love, Inc
//
// Handler for AJAX calls from the view layer.
//
// This page takes in a set of key/value pairs and returns a $response string that may contain key/value 
// pairs.  HTML pages call this page to save state in the database, and to restore state from the 
// database.  At least one key must be present, the $command key.  Additional keys may be required based 
// on the value for the $command.
//
// All routines return results in the form of JSON strings.  These strings will always contain two 
// key/value pairs:
//
//	$command -- echos the $command being invoked
//	results	-- [success|error] -- indicates if the call was successful or not
//
// If an error is encountered, a third key/value pair is added:
//
//	error -- [text message] -- additional error message
//
// Commands:
//
// doDeleteDBTableRecord -- Deletes a record from a given table by id
// doGetDBTableList -- returns a list of records from a table
// doGetDBTableNames -- returns all the field names for a table, and the requiredVals array as an embedded JSON
// doGetDBTableRecord -- returns a record from a table for a given id
// doGetDBTableStructure -- returns the fields and their properties for a table
// doGetSPList -- returns a result set from running a stored procedure 
// doGetSQLList -- returns a result set from running an SQL command.  If the command runs, but a 
// doInsertDBTableRecord -- Inserts a record in a table
// doLogMessage -- saves an incoming message into the logs
// doUpdateDBTableRecord -- Updates a record in a table
//
// Misc:
//
// getParameter -- returns a parameter from the parameters array, or an empty string if 
//

	// Connect to the current modx session
	
$modx;

	// Globals
	
$debug		= false;
$verbose	= false;
$response	= "";
$parameters	= array();

	// Set up autoloader

spl_autoload_extensions( '.php,.inc' );
spl_autoload_register();
 
	// Start logging

$logger = Logger::singleton();
if ( $debug ) {
	$logger->log( "*** Debugging turned on in oplAPI.php" );
	$logger->log( "session_id = [" . session_id() ."]", 1 );
}

	// Check incoming variables

if ( $debug ) {
	$logger->log( "Checking incoming key/value pairs", 1 );
}

$verbose	= false;
$command	= "";
foreach ( $_POST as $key => $uriValue ) {
	$value	= trim( htmlSpecialChars( $uriValue ));
	if ( $debug ) {
		$logger->log( "Key: [$key], " .	"Value: [" . $value . "]", 1 );
	}

	if ( $key == "command" ) {
		$command	= $value;
		if ( $debug ) {
			$logger->log( "\$command = [" . $command . "]", 1 );
		}
	} else if ( $key == "verbose" ) {
		$verbose	= true;
		if ( $debug ) {
			$logger->log( "\$verbose = [" . $verbose . "]", 1 );
		}
	} else {
		$parameters[ $key ] = $value;
		if ( $debug ) {
			$logger->log( "\$parameter[ $key ] = [$value]", 1 );
		}
	}
}

	// Build $response

if ( $verbose ) {
	$paramString	= "command=$command";
	foreach( $parameters as $key => $value ) {
		$paramString	.= "&" . $key . "=" . $value;
	}
	
	$response	= Utils::addToJSONString( $response, "POST", $paramString );
}

switch( $command ) {

	case "deleteDBTableRecord":
		$response	= Utils::addToCSVString( $response, doDeleteDBTableRecord());
		break;

	case "getDBTableList":
		$response	= Utils::addToCSVString( $response, doGetDBTableList());
		break;
	
	case "getDBTableNames":
		$response	= Utils::addToCSVString( $response, doGetDBTableNames());
		break;
	
	case "getDBTableRecord":
		$response	= Utils::addToCSVString( $response, doGetDBTableRecord());
		break;
	
	case "getDBTableStructure":
		$response	= Utils::addToCSVString( $response, doGetDBTableStructure());
		break;	

	case "getSPList":
		$response	= Utils::addToCSVString( $response, doGetSPList());
		break;
		
	case "getSQLList":
		$response	= Utils::addToCSVString( $response, doGetSQLList());
		break;
		
	case "insertDBTableRecord":
		$response	= Utils::addToCSVString( $response, doInsertDBTableRecord());
		break;		
			
	case "logMessage":
		$response	= Utils::addToCSVString( $response, doLogMessage());
		break;
		
	case "updateDBTableRecord":
		$response	= Utils::addToCSVString( $response, doUpdateDBTableRecord());
		break;	
		
	default:
		if ( $verbose ) {
		
			$response	= Utils::addToJSONString( $response, "results", "error" );
			$response	= Utils::addToJSONString( $response, "error", "unknown command" );
		}
		break;
}

	// Add the enclosing braces
	
$response	= "{" . $response . "}";

	// Return the response

if ( $debug ) {
	// $logger->log( "Done, returning \$response = [" . $response . "]", 1 );
	$logger->log( "Done oplAPI.php", 1 );
}
echo $response;


	// ********************************************************************************
	//
	// Commands
	//
	// ********************************************************************************


//
// doDeleteDBTableRecord -- Deletes a record from a given table by id
//
function doDeleteDBTableRecord() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "deleteDBTableRecord" );
	
	if ( $debug ) {
		$logger->log( "In doDeleteDBTableRecord" );
	}
	
		// Get the table name
		
	$tableName	= getParameter( "TableName" );
	if ( $tableName == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "TableName parameter missing or empty" );
		return $retVal;
	}

		// Check for ID
		
	$id	= getParameter( "ID" );
	if ( $id == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "ID parameter missing or empty" );
		return $retVal;
	} else if ( !is_numeric( $id )) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "ID is not a number" );
		return $retVal;
	} else if ( $id < 1 ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "ID is not a valid id" );
		return $retVal;
	}
	
		// Delete the record
		
	$dbTable	= new DBTable();
	$dbTable->setID( $id );
	$dbTable->setTableName( $tableName );

	try {
		if ( !$dbTable->deleteRecord()) {
			$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
			$retVal	= Utils::addToJSONString( $retVal, "error", "Can't delete record: " . $dbTable->getErrorMessage());
			return $retVal;
		}
	} catch ( Exception $ex ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Exception caught in DBTable::insertDBTableRecord: [" . $ex->getMessage() . "]" );
		return $retVal;
	}
		
	
	$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
	$retVal	= Utils::addToJSONString( $retVal, "success", "Record deleted" );
	if ( $debug ) {
		$logger->log( "Done doDeleteDBTableRecord", 1 );
	}
	
	return $retVal;
	
}


//
// doGetDBTableList -- returns a list of records from a table
//
function doGetDBTableList() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	global $modx;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "getDBTableList" );
	
	if ( $debug ) {
		$logger->log( "In doGetDBTableList" );
	}
	
		// Check for Table Name
		
	$tableName	= getParameter( "TableName" );
	if ( $tableName == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "TableName parameter missing or empty" );
		return $retVal;
	}
		
		// Check for Where Clause
		
	$whereClause	= getParameter( "WhereClause" );
				
		// Create a table object and try to get the records for it
		
	$dbTable	= new dbTable();
	$dbTable->setTableName(  $tableName );
	if ( $whereClause != "" ) {
		$dbTable->setWhereClause( $whereClause );
	}
	
	
	$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
	$retVal	= Utils::addToJSONString( $retVal, "success", "JSON string created" );
	$retVal	= Utils::addToCSVString( $retVal, $dbTable->getTableList());
	
	if ( $debug ) {
		$logger->log( "\$retVal: [$retVal]", 1 );
	}	
	if ( $debug ) {
		$logger->log( "Done doGetDBTableList", 1 );
	}
	
	return $retVal;
	
}


//
// doGetDBTableNames -- returns all the field names for a table, and the requiredVals array as an embedded JSON
// string
//
function doGetDBTableNames() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	global $modx;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "getDBTableNames" );
	
	if ( $debug ) {
		$logger->log( "In doGetDBTableNames" );
	}
	
		// Check for Table Name
		
	$tableName	= getParameter( "TableName" );
	if ( $tableName == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "TableName parameter missing or empty" );
		return $retVal;
	}
				
		// Create a table object
		
	$dbTable	= new dbTable();
	$dbTable->setTableName( $tableName );
	
		// Load the structure
		
		// call the loadTableStructure method
		
	try {
		if ( !$dbTable->loadTableStructure()) {
			$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
			$retVal	= Utils::addToJSONString( $retVal, "error", "Can't get table structure, table doesn't exist or DB error" );
			return $retVal;
		}
	} catch ( Exception $ex ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Exception caught in DBTable::loadDBTableStructure: [" . $ex->getMessage() . "]" );
		return $retVal;
	}
	
		
		// Return the names and the required vals
		
	$fieldNames	= array();
	$curField	= 0;
	foreach ( $dbTable->getTableStructure() as $key => $value ) {
		if ( $debug ) {
			$logger->log( "\$key = [$key], $value = [$value]", 1 );
		}
		$fieldNames[ $curField++ ]	= $key;
	}
	
	$reqValList	= array();
	foreach (  $dbTable->getRequiredVals() as $curKey => $curValue ) {
		$reqValList[ $curKey ]	= "required";
	}
	$fieldNames[ "RequiredValues" ]	= json_encode( $reqValList );	

	$retVal	= Utils::addToJSONString( $retVal, "Values", json_encode( $fieldNames ));
	$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
	$retVal	= Utils::addToJSONString( $retVal, "success", "Table structure loaded" );

	if ( $debug ) {
		$logger->log( "\$retVal: [$retVal]", 1 );
	}
		
	if ( $debug ) {
		$logger->log( "Done doGetDBTableNames", 1 );
	}
	
	return $retVal;
	
}


//
// doGetDBTableRecord -- returns a record from a table for a given id
//
function doGetDBTableRecord() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	global $modx;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "getDBTableRecord" );
	
	if ( $debug ) {
		$logger->log( "In doGetDBTableRecord" );
	}
	
		// Check for Table Name
		
	$tableName	= getParameter( "TableName" );
	if ( $tableName == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "TableName parameter missing or empty" );
		return $retVal;
	}
		
	
		// Check for id
		
	$id	= getParameter( "ID" );
	if ( $id == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "ID parameter missing or empty" );
		return $retVal;
	}
		
		// Create a table object and try to get the records for it
		
	$dbTable	= new dbTable();
	$dbTable->setTableName( $tableName );
	if ( !$dbTable->loadRecordByID( $id )) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Can't find this record" );
	} else {
		$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
		$retVal	= Utils::addToJSONString( $retVal, "Values", $dbTable->toJSONStr());
	}
		
	if ( $debug ) {
		$logger->log( "\$retVal: [$retVal]", 1 );
	}
		
	if ( $debug ) {
		$logger->log( "Done doGetDBTableRecord", 1 );
	}
	
	return $retVal;
	
}


//
// doGetDBTableStructure -- returns the fields and their properties for a table
//
function doGetDBTableStructure() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "getDBTableStructure" );
	
	if ( $debug ) {
		$logger->log( "In doGetDBTableStructure" );
	}
	
		// Check for TableName
		
	$tableName	= getParameter( "TableName" );
	if ( $tableName == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "TableName parameter missing or empty" );
		return $retVal;
	}
	
		// Create a DBTable object
		
	$dbTable	= new DBTable();
	$dbTable->setTableName( $tableName );
		
		// call the loadTableStructure method
		
	try {
		if ( !$dbTable->loadTableStructure()) {
			$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
			$retVal	= Utils::addToJSONString( $retVal, "error", "Can't get table structure, table doesn't exist or DB error" );
			return $retVal;
		}
	} catch ( Exception $ex ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Exception caught in DBTable::loadDBTableStructure: [" . $ex->getMessage() . "]" );
		return $retVal;
	}
	
		// Return the tableStructure array
		
	$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
	$retVal	= Utils::addToJSONString( $retVal, "success", "Table structure loaded" );
	$retVal	= Utils::addToJSONString( $retVal, "Values", json_encode( $dbTable->getTableStructure()));
	
		
	if ( $debug ) {
		$logger->log( "Done doGetDBTableStructure", 1 );
	}
	
	return $retVal;
	
}


//
// doGetSPList -- returns a result set from running a stored procedure.  If the procudure runs, but a 
// result set is not returned by the DB, it is not an error, but a warning is placed in the JSON return
// string.
//
function doGetSPList() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "getSPList" );
	
	if ( $debug ) {
		$logger->log( "In doGetSPList" );
	}
	
		// Check for Stored Procedure name
		
	$storedProc	= getParameter( "StoredProcedure" );
	if ( $storedProc == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "StoredProcedure parameter missing or empty" );
		return $retVal;
	}
	if ( $debug ) {
		$logger->log( "\$storedProc = [$storedProc]", 1 );
	}
	
		// Check for parameters
		
		// Get the key/value pairs
		
	$procParams	= array();
	foreach ( $_POST as $curKey => $curValue ) {
		if ( $debug ) {
			$logger->log( "\$curKey = [$curKey]", 1 );
			$logger->log( "\$curValue = [$curValue]", 1 );
		} 
		
			// Skip properties that are not part of the user object
			
		if (( $curKey == "command" ) 
				|| ( $curKey == "verbose" )
				|| ( $curKey == "StoredProcedure" )) {
			if ( $debug ) {
				$logger->log( "Skipping this key...", 2 );
			}
			continue;
		}
		
		$hasProperties	= true;
		
		if ( $curValue == "" ) {
			$procParams[]	= $curKey;
		} else {
			$procParams[]	= array( $curKey, $curValue );
		}
	}
	if ( $debug ) {
		$logger->log( "\$procParams = [" . print_r( $procParams, true ) . "]", 1 );
	}

		// Run the stored procedure
		
	$curRow		= 0;
	$jsonStr	= "";
	$oplDB		= new Database();
	try {

		$oplDB->openDB();
		$oplDB->execProcedure( $storedProc, $procParams );

		if ( $oplDB->numrows > 0 ) {
			if ( $debug ) {
				$logger->log( "Procedure call returned results", 1 );
			}
			
			while ( $row = $oplDB->result->fetch_assoc()) {
			
					// Build a JSON string out of this row
					
				$rowJSON	= "";
				foreach ( $row as $key => $value ) {
					$rowJSON	= Utils::addToJSONString( $rowJSON, $key, $value );
				}
				if ( $debug ) {
					$logger->log( "\$rowJSON = [$rowJSON]", 1 );
				}

					// Add the current row into the full JSON string

				$rowKey		= "list" . $curRow++;
				$jsonStr	= Utils::addToJSONString( $jsonStr, $rowKey, ( "{ " . $rowJSON . " }" ));
			}
			
		} else {
			$retVal	= Utils::addToJSONString( $retVal, "warning", "Procedure didn't return a result set" );
		}

		$oplDB->closeDB();
		unset( $oplDB );
	} catch ( Exception $ex ) {
		$logger->log( "Exception caught in doGetSPList: [" . $ex->getMessage() . "]", 1 );
		$oplDB->closeDB();
		unset( $oplDB );
		
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Exception caught: " . $ex->getMessage());
		return $retVal;
	}

	if ( $debug ) {
		$logger->log( "\$jsonStr = [$jsonStr]", 1 );
	}

	$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
	$retVal	= Utils::addToJSONString( $retVal, "success", "Stored procedure executed" );
	$retVal	= Utils::addToCSVString( $retVal, $jsonStr );
	
	if ( $debug ) {
		$logger->log( "\$jsonStr = [$jsonStr]", 1 );
		$logger->log( "\$retVal = [$retVal]", 1 );
	}
	if ( $debug ) {
		$logger->log( "Done doGetSPList", 1 );
	}
	
	return $retVal;
	
}


//
// doGetSQLList -- returns a result set from running an SQL command.  If the command runs, but a 
// result set is not returned by the DB, it is not an error, but a warning is placed in the JSON return
// string.
//
function doGetSQLList() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "getSQLList" );
	
	if ( $debug ) {
		$logger->log( "In doGetSQLList" );
	}
	
		// Check for Stored Procedure name
		
	$queryStr	= getParameter( "SQL" );
	if ( $queryStr == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "SQL parameter missing or empty" );
		return $retVal;
	}
	
		// Strip any embedded tabs and crlfs
		
	$queryStr	= str_replace( "\n", " ", $queryStr );	// map ctrl char to space
	$queryStr	= str_replace( '\n', '', $queryStr );	// map actual chars to nothing
	$queryStr	= str_replace( "\t", "", $queryStr );
	
	if ( $debug ) {
		$logger->log( "\$queryStr = [$queryStr]", 1 );
	}
	
		// Run the stored procedure
		
	$curRow		= 0;
	$jsonStr	= "";
	$oplDB		= new Database();
	try {

		$oplDB->openDB();
		$oplDB->execQueryStr( $queryStr );

		if ( $oplDB->numrows > 0 ) {
			if ( $debug ) {
				$logger->log( "SQL returned results", 1 );
			}
			
			while ( $row = $oplDB->result->fetch_assoc()) {
			
					// Build a JSON string out of this row
					
				$rowJSON	= "";
				foreach ( $row as $key => $value ) {
					$rowJSON	= Utils::addToJSONString( $rowJSON, $key, $value );
				}
				if ( $debug ) {
					$logger->log( "\$rowJSON = [$rowJSON]", 1 );
				}

					// Add the current row into the full JSON string

				$rowKey		= "list" . $curRow++;
				$jsonStr	= Utils::addToJSONString( $jsonStr, $rowKey, ( "{ " . $rowJSON . " }" ));
			}
			
		} else {
			$retVal	= Utils::addToJSONString( $retVal, "warning", "SQL didn't return a result set" );
		}

		$oplDB->closeDB();
		unset( $oplDB );
	} catch ( Exception $ex ) {
		$logger->log( "Exception caught in doGetSQLList: [" . $ex->getMessage() . "]", 1 );
		$oplDB->closeDB();
		unset( $oplDB );
		
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Exception caught: " . $ex->getMessage());
		return $retVal;
	}

	if ( $debug ) {
		$logger->log( "\$jsonStr = [$jsonStr]", 1 );
	}

	$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
	$retVal	= Utils::addToJSONString( $retVal, "success", "SQL command executed" );
	$retVal	= Utils::addToCSVString( $retVal, $jsonStr );
	
	if ( $debug ) {
		$logger->log( "\$jsonStr = [$jsonStr]", 1 );
		$logger->log( "\$retVal = [$retVal]", 1 );
	}
	if ( $debug ) {
		$logger->log( "Done doGetSQLList", 1 );
	}
	
	return $retVal;
	
}


//
// doInsertDBTableRecord -- Inserts a record in a table
//
function doInsertDBTableRecord() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "insertDBTableRecord" );
	
	if ( $debug ) {
		$logger->log( "In doInsertDBTableRecord" );
	}
	
		// Get the table name
		
	$tableName	= getParameter( "TableName" );
	if ( $tableName == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "TableName parameter missing or empty" );
		return $retVal;
	}
	
		// Get the key/value pairs
		
	$tableFields	= array();
	foreach ( $_POST as $curKey => $curValue ) {
		if ( $debug ) {
			$logger->log( "\$curKey = [$curKey]", 1 );
			$logger->log( "\$curValue = [$curValue]", 1 );
		} 
		
			// Skip properties that are not part of the user object
			
		if (( $curKey == "command" ) 
				|| ( $curKey == "verbose" )
				|| ( $curKey == "TableName" )) {
			if ( $debug ) {
				$logger->log( "Skipping this key...", 2 );
			}
			continue;
		}
		
		$hasProperties	= true;
		
		$tableFields[ $curKey ] = $curValue;
	}
	if ( count( $tableFields ) == 0 ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Key/value pairs missing" );
		return $retVal;
	}
	
		// Insert the record
		
	$dbTable	= new DBTable();
	$dbTable->setTableName( $tableName );
	$dbTable->setTableFields( $tableFields );
	
	try {

		if ( !$dbTable->insertRecord()) {
			$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
			$retVal	= Utils::addToJSONString( $retVal, "error", "Insert failed: " . $dbTable->getErrorMessage());
			return $retVal;
		}

	} catch ( Exception $ex ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Exception caught in DBTable::insertRecord: [" . $ex->getMessage() . "]" );
		return $retVal;
	}
	

	
	$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
	$retVal	= Utils::addToJSONString( $retVal, "success", "Record inserted" );
	$retVal	= Utils::addToJSONString( $retVal, "Values", $dbTable->toJSONStr());


	if ( $debug ) {
		$logger->log( "Done doInsertDBTableRecord", 1 );
	}
	
	return $retVal;

}



//
// doLogMessage -- saves an incoming message into the logs
//
function doLogMessage() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "logMEssage" );
	
	if ( $debug ) {
		$logger->log( "In doLogMessage" );
	}
	
		// Check for Message & tabstop
		
	$message	= getParameter( "Message" );
	$tabstop	= getParameter( "Tabstop" );
	if ( $debug ) {
		$logger->log( "\$message = [$message]", 1 );
		$logger->log( "\$tabstop = [$tabstop]", 1 );
	}
	if ( $tabstop == "" ) {
		if ( $debug ) {
			$logger->log( "Setting \$tabstop to 0", 1 );
		}
		$tabstop	= 0;
	}
	$tabstop	= ( int )$tabstop;

	$logger->log( "doLogMessage: [$message]", $tabstop );
	
	$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
	return $retVal;
}


//
// doUpdateDBTableRecord -- Updates a record in a table
//
function doUpdateDBTableRecord() {
	$debug	= false;
	$retVal	= "";
	
	global $logger;
	global $parameters;
	
	$retVal	= Utils::addToJSONString( $retVal, "command", "updateDBTableRecord" );
	
	if ( $debug ) {
		$logger->log( "In doUpdateDBTableRecord" );
	}
	
		// Get the table name
		
	$tableName	= getParameter( "TableName" );
	if ( $tableName == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "TableName parameter missing or empty" );
		return $retVal;
	}
	
		// Get the record id
		
	$id	= getParameter( "ID" );
	if ( $id == "" ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "ID parameter missing or empty" );
		return $retVal;
	} else if ( !is_numeric( $id )) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "ID is not a number" );
		return $retVal;
	} else if ( $id < 1 ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "ID is not a valid id" );
		return $retVal;
	}
	
		// Get the key/value pairs
		
	$tableFields	= array();
	foreach ( $_POST as $curKey => $curValue ) {
		if ( $debug ) {
			$logger->log( "\$curKey = [$curKey]", 1 );
			$logger->log( "\$curValue = [$curValue]", 1 );
		} 
		
			// Skip properties that are not part of the user object
			
		if (( $curKey == "command" ) 
				|| ( $curKey == "verbose" )
				|| ( $curKey == "TableName" )
				|| ( $curKey == "ID" )) {
			if ( $debug ) {
				$logger->log( "Skipping this key...", 2 );
			}
			continue;
		}
		
		$tableFields[ $curKey ] = $curValue;
	}
	if ( count( $tableFields ) == 0 ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Key/value pairs missing" );
		return $retVal;
	}
	
		// Update the record
		
	$dbTable	= new DBTable();
	$dbTable->setTableName( $tableName );
	$dbTable->setID( $id );
	$dbTable->setTableFields( $tableFields );
	
	try {

		if ( !$dbTable->updateRecord()) {
			$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
			$retVal	= Utils::addToJSONString( $retVal, "error", "Update failed: " . $dbTable->getErrorMessage());
			return $retVal;
		}

	} catch ( Exception $ex ) {
		$retVal	= Utils::addToJSONString( $retVal, "results", "error" );
		$retVal	= Utils::addToJSONString( $retVal, "error", "Exception caught in DBTable::updateDBTableRecord: [" . $ex->getMessage() . "]" );
		return $retVal;
	}
	

	
	$retVal	= Utils::addToJSONString( $retVal, "results", "success" );
	$retVal	= Utils::addToJSONString( $retVal, "success", "Record updateed" );
	$retVal	= Utils::addToJSONString( $retVal, "Values", $dbTable->toJSONStr());


	if ( $debug ) {
		$logger->log( "Done doUpdateDBTableRecord", 1 );
	}
	
	return $retVal;

}



	// ********************************************************************************
	//
	// Misc
	//
	// ********************************************************************************
	

//
// getParameter -- returns a parameter from the parameters array, or an empty string if 
// it is not set or empty
//
function getParameter( $paramName ) {
	$debug	= false;

	global $logger;
	global $parameters;

	if ( $debug ) {
		$logger->log( "In getParameter, \$paramName = [$paramName]" );
	}
	
	$retVal	= ( isset( $parameters[ $paramName ])) ? html_entity_decode( $parameters[ $paramName ]) : "";
	
	if ( $debug ) {
		$logger->log( "Done getParameter, \$retVal = [$retVal]" );
	}
	return $retVal;
		
}

 

?>