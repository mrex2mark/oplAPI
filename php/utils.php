<?php
// 
// Utils.php -- Copyright 2013 One Plus Love, Inc.
// 	
// This class is a collection of static utility functions.  Since the methods are static, they should
// all be invoked with:
//
//	Utils::methodName();
//
// This class also contains global constants, which are *not* kept in Config.  They should be accessed with:
//
// 	Utils::CONSTANT;
// 
// addToCSVString -- adds an incoming string to a CSV string, possibly prepending a comma and space.  Returns
// addToJSONString -- adds an incoming key/value pair to a JSON string
// jsonify -- takes in a string and replaces control characters and quotes with properly
// getServerProtocol -- Return the Server protocol for http or https based on the Server Settings
// getTableAsJSONString -- Returns all the records in a table as a JSON string.  Takes in a stored 
//

require_once( "logger.php" );

class Utils {

		// Constants 
		
	const CRYPT_SALT	= '$6$';
	const WEB_EMAIL_ADR	= 'mrex@interport.net';
	const VERSION_NAME	= 'OPL REST API, Beta 1.0 (dev)';		// Change when publishing to (prod)
	
		// URLs
		
	const WEB_ROOT				= '/';
	
		// File locations
		
	const UPLOAD_PATH			= "tmp/";
	const DOWNLOAD_PATH			= "tmp/";
	const REST_API_LOC			= "php/oplAPI.php";
	const FILE_STORAGE			= "/mnt/oplfile";
	// const FILE_STORAGE			= "/opt/oplfile";
	const ZPH_DIR				= "zph/";
	
		// Members

	public static $debug		= false;

		// Methods

	//
	// addToCSVString -- adds an incoming string to a CSV string, possibly prepending a comma and space.  Returns
	// the new string
	//
	public static function addToCSVString( $csvString, $newVal ) {
		if ( strlen( $csvString ) > 0 ) {
			$csvString	.= ", ";
		}

		return $csvString . $newVal;
	}


	//
	// addToJSONString -- adds an incoming key/value pair to a JSON string
	//
	public static function addToJSONString( $jsonString, $key, $val ) {
		$debug	= self::$debug;

		if ( $debug ) {
			$logger	= Logger::singleton();
			$logger->log( "In Utils::addToJSONString" );
			$logger->log( "\$key = [$key]", 1 );
			$logger->log( "\$val = [$val]", 1 );
		}
		
		if ( strpos( $val, "{" ) === false ) {

			if ( $debug ) {
				$logger->log( "Utils: this is a plain value", 1 );
			}
				// Val is a string
				
			$val	= json_encode( $val );

			return self::addToCSVString( $jsonString, "\"" . $key . "\" : " . $val );
		} else {
			if ( $debug ) {
				$logger->log( "this is a JSON object", 1 );
			}

				// Val is a JSON object

			return self::addToCSVString( $jsonString, "\"" . $key . "\" : " . $val . "" );
		}
	}


	//
	// jsonify -- takes in a string and replaces control characters and quotes with properly
	// escaped JSON values
	//
	// FARKLE -- DON"T USE THIS.  Use json_encode instead
	//
	public static function jsonify( $val ) {
		$val	= str_replace( '\\', '\\\\', $val );		// convert backslashes first 
		$val	= str_replace( "\n", '\\n', $val );
		$val	= str_replace( "\r", '\\r', $val );
		$val	= str_replace( "\t", '\\t', $val );
		$val	= str_replace( "\v", '\\v', $val );
		$val	= str_replace( "\f", '\\f', $val );
		$val	= str_replace( "\n", '\\n', $val );
		$val	= str_replace( "\n", '\\n', $val );

		return $val;
	}


	//
	// getServerProtocol -- Return the Server protocol for http or https based on the Server Settings
	// FARKLE: $protocol is not used why is it here?
	//
	public static function getServerProtocol( $protocol = "" ) {
	
		if ( !empty($_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
	    	$protocol = "https://";
	  	} else {
	    	$protocol = "http://";
	  	}
	  	return $protocol;
	}

	
	//
	// getTableAsJSONString -- Returns all the records in a table as a JSON string.  Takes in a stored 
	// procedure name.  Uses an optional parameter to take in parameters for the stored procedure.
	//
	// FARKLE -- this is not returning the main list enclosed in {}
	//
	public static function getTableAsJSONString( $procName, $procArgs = array()) {
		$debug	= self::$debug;		
		$retVal	= "";
		
		if ( $debug ) {
			$logger	= Logger::singleton();
			$logger->log( "In getTableAsJSONString" );
			$logger->log( "\$procName = [$procName]", 1 );
			$logger->log( "getType( \$procArgs )= [" . getType( $procArgs ) . "]", 1 );
		}
		
			// Try to run the procedure

		$oplDB	= new Database();
		try {

			$oplDB->openDB();
			$oplDB->execProcedure( $procName, $procArgs );
			$jsonStr	= "";
			
			if ( $oplDB->numrows > 0 ) {
				if ( $debug ) {
					$logger->log( "DB results found", 1 );
				}

					// Build the JSON string
					
				$curRow		= 0;
				$oplDB->result->data_seek( 0 );				
				while ( $row = $oplDB->result->fetch_assoc()) {
						
						// Build a JSON string for the current row
						
					$curJSON	= "";
					foreach ( $row as $key => $value ) {
						$curJSON	= self::addToJSONString( $curJSON, $key, $value );
					}
					if ( $debug ) {
						$logger->log( "\$curJSON = [$curJSON]", 1 );
					}
					
						// Add the current row into the JSON string
						
					$curKey		= "list" . $curRow++;
					$jsonStr	= self::addToJSONString( $jsonStr, $curKey, ( "{ " . $curJSON . " }" ));
						
				}				


			} 
			if ( $jsonStr == "" ) {
				$jsonStr	= self::addToJSONString( $jsonStr, "results", "error" );
				$jsonStr	= self::addToJSONString( $jsonStr, "error", "No results obtained" );
			}
			$oplDB->closeDB();
			$retVal	= $jsonStr;

		} catch ( Exception $ex ) {
			if ( $debug ) {
				$logger->log( "Exception caught in Utils::getTableAsJSONString: [" . $ex->getMessage() . "]", 1 );
			}
			$oplDB->closeDB();
			throw $ex;
		}

		unset( $oplDB );

			// Return the string

		if ( $debug ) {
			$logger->log( "Done getTableAsJSONString", 1 );
			$logger->log( "\$retVal = [$retVal]", 1 );
		}
			
		
		return $retVal;
	}
		

}

?>