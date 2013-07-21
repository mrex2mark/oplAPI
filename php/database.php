<?php
// 
// Database.php -- Copyright 2013 One Plus Love, Inc.
// 
// This class implements a simple DB connection, and methods to perform basic calls against the db.
// Uses mysqli library.
// 
// Constructor
// Destructor
//
// addFilterToQuery -- adds the filter into the current query
// closeDB -- Close a db connection
// closeResults -- aggressively makes sure all result sets are closed
// execInsert( $querystr ) -- Execute an SQL Insert command
// execProcedure -- executes a stored procedure.  Returns true if successful, false otherwise.  
// execQuery -- Execute an SQL command in the object's query string
// execQueryStr( $querystr ) -- Execute an SQL command
// getValuelist( $columname, $quoted ) -- Return all values from a column in an existing
// getValues( $querystr ) -- Get a row from the DB.
// openDB -- Open a db connection
// removePermissionsFilter() -- Removes permissions filtering
// setPermissionsFilter( filterType, $userID ) -- Creates a filter clause which is used to limit
// toSQLstr( $string ) -- Double all single quotes in a given string
//

require_once( "config.php" );
require_once( "logger.php" );

class Database {

		// Constants
		
	const ACT_PERM		= "Account";
	const UTYPE_PERM	= "UserType";
	const LIST_PERM		= "List";
	
		// Properties

	public $debug = false;

	public $query;
	public $result;
	public $success;
	public $numrows;			// Number of rows returned from running a query, 0 if error
	public $errormsg;
	public $usePermFilter;		// TRUE if must filter by permissions
	public $permFilter;			// Clause for filtering
	public $filterType;			// Set to one of the PERM constants

	private $dbConn;

	private $config;
	private $logger;

	
	//
	// Constructor
	//
	function __construct() {
		$debug	= $this->debug;
		
		$this->config			= new Config();
		$this->logger			= Logger::singleton();
		$this->usePermFilter	= false;
		$this->permFilter		= "";
		$this->filterType		= "";
		
		if ( $debug ) {
			$this->logger->log( "Debugging turned on in Database" );
		}
    }	
	
	
	//
	// Destructor
	//
	function __destruct() {
		$this->closeDB();
	}	
	
	
	
	//
	// addFilterToQuery -- adds the filter into the current query
	//
	public function addFilterToQuery( $queryStr ) {
		$debug	= $this->debug;
		$retVal	= $queryStr;
		
		if ( $debug ) {
			$this->logger->log( "In Database::addFilterToQuery" );
			$this->logger->log( "\$queryStr = [$queryStr]", 1 );
		}
		
			// Make sure this is not a stored procedure
			
			// Apply the filter string
		
		switch ( $this->filterType ) {
			case Database::ACT_PERM:
			
					// find the WHERE clause
					
				$wherePos	= strripos( $retVal, "where" );
				if ( $debug ) {
					$this->logger->log( "\$wherePos = [$wherePos]", 1 );
				}
				
					// Find any clauses after
					
				$whereEndPos	= strripos( $retVal, "GROUP BY" );
				$whereEndPos	= ( $whereEndPos === false ) ? strripos( $retVal, "HAVING" ) : $whereEndPos;
				$whereEndPos	= ( $whereEndPos === false ) ? strripos( $retVal, "ORDER BY" ) : $whereEndPos;
				$whereEndPos	= ( $whereEndPos === false ) ? strripos( $retVal, "LIMIT" ) : $whereEndPos;
				$whereEndPos	= ( $whereEndPos === false ) ? strripos( $retVal, "PROCEDURE" ) : $whereEndPos;
				$whereEndPos	= ( $whereEndPos === false ) ? strripos( $retVal, "INTO" ) : $whereEndPos;
				$whereEndPos	= ( $whereEndPos === false ) ? strripos( $retVal, "FOR UPDATE" ) : $whereEndPos;
				$whereEndPos	= ( $whereEndPos === false ) ? strripos( $retVal, "LOCK IN" ) : $whereEndPos;
				if ( $debug ) {
					$this->logger->log( "\$whereEndPos = [$whereEndPos]", 1 );
				}

					// Add the filter
					
				$permClause	= $this->permFilter;
				if ( $wherePos === false ) {
					if ( $debug ) {
						$this->logger->log( "No WHERE clause found, adding WHERE clause", 1 );
					}
					
					$permClause	= " WHERE " . $permClause;
					
						// Find the start point and add WHERE clause
						
					if ( $whereEndPos === false ) {
						if ( $debug ) {
							$this->logger->log( "No trailing clauses found, adding to end", 1 );
						}
						$retVal	.= $permClause;
					} else {
						if ( $debug ) {
							$this->logger->log( "Trailing clauses found, inserting at \$whereEndPos", 1 );
						}
						
						$retVal	= substr( $retVal, 0, $whereEndPos ) . 
									$permClause . " " . 
									substr( $retVal, $whereEndPos );
					}
					
				} else {
					if ( $debug ) {
						$this->logger->log( "WHERE clause found, adding parens and AND clause", 1 );
					}
					
						// Set as AND clause
						
					$permClause	= " AND ( " . $permClause . ") ";
											
						// Find the end pos
						
					if ( $whereEndPos === false ) {
						if ( $debug ) {
							$this->logger->log( "No trailing clauses found, adding to end", 1 );
						}
								
							// Add the clause to end
					
						$retVal	= substr( $retVal, 0, ( $wherePos + strlen( "WHERE" ))) . " ( " .
									substr( $retVal, ( $wherePos + strlen( "WHERE" ) + 1 )) . " ) " .
									$permClause;
						
					} else {
						if ( $debug ) {
							$this->logger->log( "Trailing clauses found, inserting at \$whereEndPos", 1 );
						}
						
							// Insert the clause
							// beginning to end of "where" keyword + ( +
							// just after "where" keyword to just before next clause + ) +
							// permClause +
							// remainder of string
							// 
							
						$retVal = substr( $retVal, 0, ( $wherePos + strlen( "WHERE" ))) . " ( " .
									substr( $retVal, 
											( $wherePos + strlen( "WHERE" ) + 1 ), 
											( $whereEndPos - $wherePos - strlen( "WHERE" ) - 1 )) . " ) " .
									$permClause .
									substr( $retVal, $whereEndPos );
									
					}
					
						// Add the AND clause
						
				}
				if ( $debug ) {
					$this->logger->log( "\$permClause = [$permClause]", 1 );
				}
				
				break;
				
			case Database::UTYPE_PERM:
				break;
				
			case Database::LIST_PERM:
				break;
		
			default:
				if ( $debug ) {
					$this->logger->log( "Unknown filter type [$this->filterType]", 1 );
				}
				break;
		}
		if ( $debug ) {
			$this->logger->log( "\$retVal = [$retVal]", 1 );
		}

		
		
		return $retVal;
	}
	
	

	//
	// closeDB -- Close a db connection
	//
	public function closeDB() {

		if ( $this->debug ) {
			$this->logger->log( "In Database::closeDB" );
		}

			// Check if its open

		if (( isset( $this->dbConn )) && ( $this->dbConn )) {
		
				// Check if a result is still open
				
			$this->closeResults();
			
				// Close the db
				
			if ( $this->debug ) {
				$this->logger->log( "Closing connection", 1 );
			}
			$this->dbConn->close();
			unset( $this->dbConn );
		} else {
			if ( $this->debug ) {
				$this->logger->log( "DB not open", 1 );
			}
		}
	}


	//
	// closeResults -- aggressively makes sure all result sets are closed
	//
	public function closeResults() {
		$debug	= $this->debug;
		if ( $debug ) {
			$this->logger->log( "In Database::closeResults" );
		}
		
			// Check if a previous result is still active

		if ( isset( $this->result )) {
			if ( $this->debug ) {
				$this->logger->log( "Freeing \$this->result", 1 );
				$this->logger->log( "gettype( \$this->result ) = [" . gettype( $this->result ) . "]", 1 );
			}
			if ( is_object( $this->result )) {
				$this->result->close();
			}
			unset( $this->result );
		}
		
			// Check for additional result sets

		if ( $this->debug ) {
			$this->logger->log( "Checking for previous result sets", 1 );
		}
		while ( $this->dbConn->more_results()) {
			if ( $result = $this->dbConn->store_result()) {
				if ( $this->debug ) {
					$this->logger->log( "Closing existing set", 1 );
				}
				$result->close();
			}
			$this->dbConn->next_result();
		}  

	}	


	//
	// execInsert( $querystr ) -- Execute an SQL Insert command
	//
	public function execInsert( $querystr ) {
		if ( $this->debug ) {
			$this->logger->log( "In Database::execInsert( \$querystr )" );
		}

			// Run the query

		$this->execQueryStr( $querystr );

			// Throw an exception if numrows == 0

		if ( $this->numrows == 0 ) {
			throw new Exception( "Can't insert into Database" );
		} else {
			return $this->numrows;
		}
	}


	//
	// execProcedure -- executes a stored procedure.  Returns true if successful, false otherwise.  
	// Parameters are passed in as an array.  Each element can be a simple value, or a two element 
	// array specifying the value and type.  Allowable types are:
	//		numeric
	//		boolean
	//		string
	//
	// If a parameter is a simple value, it is tested using is_numeric and is_boolean, and quoted if
	// both return false.
	//
	public function execProcedure( $procName, $params ) {
		$debug	= $this->debug;
		$retVal	= false;
	
		if ( $debug ) {
			$this->logger->log( "In Database::execProcedure" );
			$this->logger->log( "\$procName = [$procName]", 1 );
			$this->logger->log( "\$params = [" . print_r( $params, true ) . "]", 1 );
		}
		
			// Build the CALL statement
		
		$this->query	= "CALL $procName(";
		$addComma		= false;		
		foreach ( $params as $paramValue ) {
			if ( $debug ) {
				$this->logger->log( "\$paramValue = [$paramValue]", 1 );
			}
		
				// Check if we must prepend a comma
				
			if ( $addComma ) {
				$this->query	.= ", ";
			} else {
				$this->query	.= " ";
				$addComma		= true;
			}
			
				// Check if this is an array 
				
			$paramType	= "";
			if ( is_array( $paramValue )) {
				if ( $debug ) {
					$this->logger->log( "\$paramValue is an array", 1 );
				}
					
				$paramType	= $paramValue[ 1 ];
				$paramValue	= $paramValue[ 0 ];
				if ( $debug ) {
					$this->logger->log( "\$paramType = [$paramType]", 1 );
					$this->logger->log( "\$paramValue = [$paramValue]", 1 );
				}
			}
						
				// Add the value
				
			if ( $paramType == "nuermic" ) {
				$this->query	.= $paramValue;
			} else if ( $paramType == "boolean" ) {
				$this->query	.= ( $paramValue ) ? "1" : "0";
			} else if ( $paramType == "string" ) {
				$this->query	.= "'" . $this->toSQLstr( $paramValue ) . "'";
			} else if ( is_numeric( $paramValue )) {
				$this->query	.= $paramValue;
			} else if ( is_bool( $paramValue )) {
				$this->query	.= ( $paramValue ) ? "1" : "0";
			} else {
				$this->query	.= "'" . $this->toSQLstr( $paramValue ) . "'";
			}
		}
		$this->query	.= " )";

		if ( $debug ) {
			$this->logger->log( "\$this->query = [$this->query]", 1 );
		}

			// Run the exec statement

		$this->execQuery();
		$retVal	= true;
		
		return $retVal;
	}
	
	
	
	//
	// execQuery -- Execute an SQL command in the object's query string
	//
	public function execQuery() {
		if ( $this->debug ) {
			$this->logger->log( "In Database::execQuery" );
		}

			// Check if we have a dbConnection

		if ( !isset( $this->dbConn )) {
			throw new Exception( "DB not open" );

			// Check if we have a query string

		} elseif ( !is_string( $this->query )) {
			throw new Exception( "Query is not a string" );

		} elseif ( !isset( $this->query )) {
			throw new Exception( "Query is not set" );

		} elseif ( $this->query === "" ) {
			throw new Exception( "Empty query string" );

		} else {
			if ( $this->debug ) {
				$this->logger->log( "\$this->query = [$this->query]", 1 );
			}

				// Check if a previous result is still active

  			$this->closeResults();
  			
  				// Check if we must filter the query
  				
  			if ( $this->usePermFilter == true ) {
  				$this->query	= $this->addFilterToQuery( $this->query );
  			}
  			
				// Execute the query string

			$this->numrows	= 0;
			$this->success	= false;
			if ( $this->debug ) {
				$this->logger->log( "executing query...", 1 );
			}
			if ( $this->result = $this->dbConn->query( $this->query )) {
				if ( $this->debug ) {
					$this->logger->log( "gettype( \$this->result ) = [" . gettype( $this->result ) . "]", 1 );
				}
				
				if ( is_object( $this->result )) {
					$this->numrows	= $this->result->num_rows;
				}
				$this->success	= true;

				if ( $this->debug ) {
					$this->logger->log( "\$this->numrows = [$this->numrows]", 1 );
					$this->logger->log( "\$this->success = [$this->success]", 1 );
				}

				return $this->numrows;
			} else {
				$this->errormsg = "Error: [" . $this->dbConn->error . "]";
				throw new Exception( $this->errormsg );
			}
			
		}
	}


	//
	// execQueryStr( $querystr ) -- Execute an SQL command
	//
	public function execQueryStr( $querystr ) {
		if ( $this->debug ) {
			$this->logger->log( "In Database::execQueryStr( \$querystr )" );
		}

		if ( !$querystr ) {

			throw new Exception( "Empty query string" );

		} else {
			$this->query = $querystr;
			return $this->execQuery();
		}
	}


	//
	// getValuelist( $columname, $quoted ) -- Return all values from a column in an existing
	// result set as comma delimited list.  If $quoted is true, surround the values with single
	// quotes
	//
	public function getValuelist( $column, $quoted ) {
		if ( $this->debug ) {
			$this->logger->log( "In Database::getValues( \$column, \$quoted )" );
		}

		$retval = "";

			// Check if we have a result set

		if ( !$this->result ) {
			throw new Exception( "Invalid or empty result set" );

			// Check if we have rows

		} elseif ( $this->numrows == 0 ) {
			throw new Exception( "Missing or invalid result set" );

			// Check if this is a valid column name for the result

		} elseif ( !odbc_field_num( $this->result, $column )) {
			throw new Exception( "Column not found in this result set" );

			// Interate the result set for this column

		} else {

			while ( odbc_fetch_row( $this->result )) {

					// Add a comma if this is not the first value

				if ( $retval != "" ) {
					$retval .= ", ";
				}

					// Add the value

				if ( $quoted ) {
					$retval .= "'";
				}

				$retval .= odbc_result( $this->result, $column );

				if ( $quoted ) {
					$retval .= "'";
				}


			}

				// Return the row pointer to the first result

			odbc_fetch_row( $this->result, 0 );

		}

		return $retval;
	}


	//
	// getValues( $querystr ) -- Get a row from the DB.
	// Return true if the row can be fetched, false otherwise
	//
	public function getValues( $querystr ) {
		if ( $this->debug ) {
			$this->logger->log( "In Database::getValues( \$querystr )" );
		}

			// Run the query

		$this->execQueryStr( $querystr );

			// Try to fetch a row

		return $this->result->fetch_assoc();

	}


	//
	// openDB -- Open a db connection
	//
	public function openDB() {

		if ( $this->debug ) {
			$this->logger->log( "In Database::openDB" );
		}

			// We should check if this is already open

		if ( !isset( $this->dbConn )) {
			if ( $this->debug ) {
				$this->logger->log( "Connecting to DB...", 1 );
			}

			$this->dbConn = new mysqli( $this->config->dbServer,
										$this->config->dbUser, 
										$this->config->dbPass,
										$this->config->dbName ); 

			if ( $this->dbConn->connect_errno ) {
				throw new Exception( "Failed to connect to MySQL: " . $mysqli->connect_error );
			}
		} else {
			if ( $this->debug ) {
				$this->logger->log( "DB already open", 1 );
			}
		}

		if ( !$this->dbConn ) {
			throw new Exception( "Cannot open DB" );
		}
	}


	//
	// removePermissionsFilter() -- Removes permissions filtering
	//
	public function removePermissionsFilter() {
		$debug	= $this->debug;
		if ( $debug ) {
			$this->logger->log( "In Database::removePermissionsFilter" );
		}
		
		$this->usePermFilter	= false;
		$this->permFilter		= "";
		$this->filterType		= "";
		
	}
	
	
	//
	// setPermissionsFilter( filterType, $userID ) -- Creates a filter clause which is used to limit
	// results sets by permission.
	//
	public function setPermissionsFilter( $filterType, $userID ) {
		$debug	= $this->debug;
		if ( $debug ) {
			$this->logger->log( "In Database::setPermissionsFilter" );
			$this->logger->log( "\$filterType = [$filterType]", 1 );
			$this->logger->log( "\$userID = [$userID]", 1 );
		}

			// Make sure we have a userID
			
		if (( !isset( $userID )) || ( $userID == "" )) {
			throw new Exception( "Empty or unset userID in setPermissionsFilter" );
			return;
		}
		
			// Make sure we have a valid filter type
			
		switch ( $filterType ) {
		
			
				//
				// Get list of valid accounts
				//
		
		
			case Database::ACT_PERM:					
				$isOpen	= false;
				if (( isset( $this->dbConn )) && ( $this->dbConn )) {
					$isOpen	= true;
				}
				
				try {

						// Open a db connection if needed

					if ( $isOpen === false ) {
						if ( $debug ) {
							$this->logger->log( "DB connection not open, opening it", 1 );
						}
						$this->openDB();
					}
					
						// Run up_GetAccountsForUser query
						
					if ( $debug ) {
						$this->logger->log( "running stored procedure", 1 );
					}
					$this->execProcedure( "up_GetAccountsForUser", array( "UserID" => $userID ));
					
					
						// Build list
					
					$this->permFilter	= "";
					if ( $this->result->data_seek( 0 )) {
						if ( $debug ) {
							$this->logger->log( "data_seek returned true", 1 );
						}
						
						while ( $row = $this->result->fetch_assoc()) {
							$curAccount	= $row[ "AccountNumber" ];
							if ( $debug ) {
								$this->logger->log( "\$curAccount = [$curAccount]", 2 );
							}
							
							if ( $curAccount == "*" ) {
								$this->permFilter	= "";
								break;
							} else {
								if ( $this->permFilter == "" ) {
									$this->permFilter	= "AccountNumber IN ( '";
								} else {
									$this->permFilter	.= "', '";
								}
								
								$this->permFilter	.= $curAccount;
							}
						}
						
						if ( $this->permFilter != "" ) {
							$this->permFilter		.= "' )";
							$this->usePermFilter	= true;
							$this->filterType		= $filterType;
						}
					}
					if ( $debug ) {
						$this->logger->log( "\$this->permFilter = [$this->permFilter]", 1 );
					}
						
						// Close connection if needed 

					if ( $isOpen === false ) {
						if ( $debug ) {
							$this->logger->log( "DB wasn't open at start, closing it", 1 );
						}
						$this->closeDB();
					}
					

				} catch ( Exception $ex ) {
					if ( $debug ) {
						$this->logger->log( "Exception caught:", 1 );
						$this->logger->log( "\$ex->getMessage() = [$ex->getMessage()]", 1 );
					}
					throw $ex;
					return;
				}
			
			
				break;
				
			case Database::UTYPE_PERM:
				break;
				
			case Database::LIST_PERM:
				break;
		
			default:
				throw new Exception( "Invalid fitlerType in setPermisssionsFilter" );
				return;
				break;
		}
		
		$this->usePermFilter	= true;
		
	}
	
	
	//
	// toSQLstr( $string ) -- Double all single quotes in a given string
	//
	public function toSQLstr( $string ) {
		$debug	= $this->debug;
		if ( $debug ) {
			$this->logger->log( "In Database::toSQLstr( \$string )" );
			$this->logger->log( "\$string = [$string]", 1 );
		}

		$retval = "";

			// If the string is not empty

		if ( $string != "" ) {

				// Replace all single quotes with two single quotes

			$retval = str_replace( "'", "''", $string );
		}

		if ( $debug ) {
			$this->logger->log( "\$retval = [$retval]", 1 );
		}
		return $retval;
	}
	
	
}


?>