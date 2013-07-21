<?php
//
// DBTable.php -- Copyright 2013 One Plus Love Inc.
//
// This class is a wrapper around database tables in the OPL REST API.  It allows easy high-level access
// to tables from PHP.
//
// __construct() -- Constructs empty object
// deleteRecord -- Deletes a record from a table
// getRequiredVals -- Returns a list of required fields and their missing messages as a JSON string
// getTableList -- Returns all records from the table as a JSON str
// insertRecord -- inserts a record into the table.  Returns TRUE if successful, false otherwise
// loadRecordByID -- Loads a single record by ID
// loadTableStructure -- loads the structure of a table from the database.  Returns TRUE if successful.
// trace -- traces out the current properties of this object to the log
// toJSONStr -- Returns a JSON string with all the values for this object
//
// To do:
//
// addTableField
// removeTableField
//

require_once( "logger.php" );
require_once( "database.php" );
require_once( "utils.php" );

class DBTable {

		// DBTable table -- fields in DB

	private $id				= -1;	// ID
	private $dateAdded		= "";	// DateAdded
	private $dateChanged	= "";	// DateChanged
	
		// Additional properties

	private $wasLoaded		= false;
	private $tableName		= "";
	private $whereClause	= "";
	private $tableFields	= array();
	private $tableStructure	= array();
	private $requiredVals	= array();
	private $errorMessage	= "";

		// Debug and Logger variables

	private	$debug				= false;
	private	$logger;
		

		// Public accessor methods

	public function getID() { return $this->id; }
	public function setID( $newID ) { $this->id = $newID; }
	
	public function getDateAdded() { return $this->dateAdded; }
	public function setDateAdded( $newDateAdded ) { $this->dateAdded = $newDateAdded; }
	
	public function getDateChanged() { return $this->dateChanged; }
	public function setDateChanged( $newDateChanged ) { $this->dateChanged = $newDateChanged; }
	
	public function getTableName() { return $this->tableName; }
	public function setTableName( $newTableName ) { $this->tableName = $newTableName; }
	
	public function getWhereClause() { return $this->whereClause; }
	public function setWhereClause( $newWhereClause ) { $this->whereClause = $newWhereClause; }
	
	public function getTableFields() { return $this->tableFields; }
	public function setTableFields( $newTableFields ) { $this->tableFields = $newTableFields; }
	
	public function getTableStructure() { return $this->tableStructure; }
	public function setTableStructure( $newTableStructure ) { $this->tableStructure = $newTableStructure; }
	
	public function getErrorMessage() { return $this->errorMessage; }
	public function setErrorMessage( $newErrorMessage ) { $this->errorMessage = $newErrorMessage; }
	

	//
	// __construct() -- Constructs empty object
	//
	public function __construct() {
		$debug	= $this->debug;
		
		$this->logger	= Logger::singleton();
		if ( $debug ) {
			$this->logger->log( "*** Debugging turned on in DBTable" );
		}

	}


	//
	// deleteRecord -- Deletes a record from a table
	//
	public function deleteRecord() {
		$debug	= $this->debug;
		$retVal	= false;

		if ( $debug ) {
			$this->logger->log( "In DBTable::deleteRecord" );
		}
		
			// FARKLE -- implement deleting child records
			
		// $this->deleteChildRecords();
			
			// Make sure we have a table name and an id
			
		if ( $this->tableName == "" ) {
			if ( $debug ) {
				$this->logger->log( "tableName not set, exiting", 1 );
			}
			$this->errorMessage	= "TableName not set";
			return $retVal;
		}
		
		
		if ( $this->id <= 0 ) {
			if ( $debug ) {
				$this->logger->log( "ID not set, exiting", 1 );
			}
			$this->errorMessage	= "ID not set";
			return $retVal;
		}
		
			// Build the query string
			
		$queryStr	= "DELETE FROM " . $this->tableName . " " .
						"WHERE ID = " . $this->id;
			
			// Delete the record
		
		$gsiDB		= new Database();
		try {

			$gsiDB->openDB();
			$gsiDB->execQueryStr( $queryStr );

			$retVal	= true;				
			$gsiDB->closeDB();

		} catch ( Exception $ex ) {
			$this->logger->log( "Exception caught in Record::deleteRecord: [" . $ex->getMessage() . "]", 1 );
			$gsiDB->closeDB();
			throw $ex;
		}

			// Always mark the object as unloaded

		$this->wasLoaded	= false;

		unset( $gsiDB );
		if ( $debug ) {
			$this->logger->log( "Done DBTable::deleteRecord" );
		}
		
		return $retVal;

	}
	
	
	//
	// getRequiredVals -- Returns a list of required fields and their missing messages as a JSON string
	//
	public function getRequiredVals() { 
		$debug	= $this->debug;
		$retVal	= "";
		if ( $debug ) {
			$this->logger->log( "In DBTable::getRequiredVals" );
		}
		
		if ( count( $this->tableStructure ) == 0 ) {
			$this->loadTableStructure();
		}
		
		if ( $debug ) {
			$this->logger->log( "Done getRequiredVals", 1 );
		}
		return $this->requiredVals;
	}
	
	
	//
	// getTableList -- Returns all records from the table as a JSON str
	//
	public function getTableList() {
		$debug	= $this->debug;
		$retVal	= "";
		if ( $debug ) {
			$this->logger->log( "In DBTable::getTableList" );
		}
		
		if ( $this->tableName != "" ) {

				// Build the query string

			$queryStr	= "SELECT * FROM $this->tableName ";
			if ( $this->whereClause != "" ) {
				$queryStr	.= $this->whereClause;
			}
			if ( $debug ) {
				$this->logger->log( "\$queryStr = [$queryStr]", 1 );
			}

				// Try to run the procedure

			$gsiDB	= new Database();
			try {

				$gsiDB->openDB();
				$gsiDB->execQueryStr( $queryStr );
				$jsonStr	= "";

				if ( $gsiDB->numrows > 0 ) {
					if ( $debug ) {
						$this->logger->log( "DB results found", 1 );
					}

						// Build the JSON string

					$curRow		= 0;
					$gsiDB->result->data_seek( 0 );				
					while ( $row = $gsiDB->result->fetch_assoc()) {

							// Build a JSON string for the current row

						$curJSON	= "";
						foreach ( $row as $key => $value ) {
							$curJSON	= Utils::addToJSONString( $curJSON, $key, $value );
						}
						if ( $debug ) {
							$this->logger->log( "\$curJSON = [$curJSON]", 1 );
						}

							// Add the current row into the JSON string

						$curKey		= "list" . $curRow++;
						$jsonStr	= Utils::addToJSONString( $jsonStr, $curKey, ( "{ " . $curJSON . " }" ));
					}				


				} 
				if ( $jsonStr == "" ) {
					$jsonStr	= Utils::addToJSONString( $jsonStr, "results", "error" );
					$jsonStr	= Utils::addToJSONString( $jsonStr, "error", "No results obtained" );
				}
				$gsiDB->closeDB();
				$retVal	= $jsonStr;

			} catch ( Exception $ex ) {
				if ( $debug ) {
					$this->logger->log( "Exception caught in DBTable::getTableList: [" . $ex->getMessage() . "]", 1 );
				}
				$gsiDB->closeDB();
				throw $ex;
			}

			unset( $gsiDB );
			$jsonStr	= Utils::addToJSONString( $jsonStr, "results", "success" );
					
		} else {
			$jsonStr	= Utils::addToJSONString( $jsonStr, "results", "error" );
			$jsonStr	= Utils::addToJSONString( $jsonStr, "error", "Table Name is not set" );
		}
		
		if ( $debug ) {
			$this->logger->log( "Done DBTable::getTableList, \$retVal = [$retVal]", 1 );
		}
		return $retVal;
	}


	//
	// insertRecord -- inserts a record into the table.  Returns TRUE if successful.  Sets the errorMessage property
	// on failure.
	//
	public function insertRecord() {
		$debug	= $this->debug;
		$retVal	= false;
		if ( $debug ) {
			$this->logger->log( "In DBTable::insertRecord" );
		}
		
			// Make sure the table name is set
			
		if ( $this->tableName == "" ) {
			$this->errorMessage	= "tableName property not set.";
			return $retVal;
		} 
		
			// Make sure the table fields are set
			
		if ( count( $this->tableFields ) == 0 ) {
			$this->errorMessage	= "tableFields property not set.";
			return $retVal;
		}
		
			// Make sure we have required parameters
		
		if ( !$this->loadTableStructure()) {
			return $retVal;
		}
		$hasReqFields	= true;
		$missingFields	= "";
		foreach ( $this->tableStructure as $fieldName => $fieldProps ) {
			if ( $debug ) {
				$this->logger->log( "\$fieldName = [$fieldName]", 1 );
			}
			if (( $fieldProps[ "Null" ] == "NO" ) 
					&& ( $fieldProps[ "Default" ] == "" ) 
					&& ( $fieldProps[ "Extra" ] != "auto_increment" )) {
				if ( $debug ) {
					$this->logger->log( "This is a required field", 2 );
				}
				
					// Check if the field exists in the tableFields array
					
				if ( !array_key_exists( $fieldName, $this->tableFields )) {
					$hasReqFields	= false;
					$missingFields	= Utils::addToCSVString( $missingFields, $fieldName );
				}
				
				
			}
		}
		if ( !$hasReqFields ) {
			$this->errorMessage	= "One.or more required fields [$missingFields] are missing";
			return $retVal;
		}
			
			// Build the query string
			
		$fieldStr	= "\tDateAdded";
		$valueStr	= "\tNULL";
		foreach ( $this->tableFields as $key => $value ) {
			if ( $debug ) {
				$this->logger->log( "\$key = [$key]", 1 );
				$this->logger->log( "\$value = [$value]", 1 );
			}
		
				// Add the field name
				
			if ( !array_key_exists( $key, $this->tableStructure )) {
				$this->errorMessage	= "Field [$key] does not exist in this table";
				return $retVal;
			}
			$fieldStr	.= ", \n\t" . $key;
			
				// Add the value
				
			$valType	= $this->tableStructure[ $key ][ "Type" ];
			if ( $debug ) {
				$this->logger->log( "\$valType = [$valType]", 1 );
			}

			if (( $valType == "datetime" ) 
					|| ( $valType == "timestamp" )
					|| ( strpos( $valType, "varchar" ) > -1 )
					|| ( strpos( $valType, "text" ) > -1 )) {
				$valueStr	.= ", \n\t'" . $value . "'";
			} else {
			
				if ( $value != "" ) {
					$valueStr	.= ", \n\t" . $value;
				} else {
					$valueStr	.= ", \n\tnull";			// Note: this will return an error if NOT NULL, but should
															// have been caught above when checking required params
				}
			}
			
		}
		
		
		$queryStr	= "INSERT " . $this->tableName . "( \n" .
						$fieldStr . " \n" .
						") VALUES ( \n" .
						$valueStr . " \n)";
						
		if ( $debug ) {
			$this->logger->log( "\$queryStr = [$queryStr]", 1 );
		}
						
			// Insert the record
			
		$gsiDB	= new Database();
		try {

			$gsiDB->openDB();
			$gsiDB->execQueryStr( $queryStr );
			$gsiDB->execQueryStr( "SELECT LAST_INSERT_ID() AS `ID`" );

			if ( $gsiDB->numrows > 0 ) {
				if ( $debug ) {
					$this->logger->log( "DB results found", 1 );
				}

					// Build the JSON string

				$gsiDB->result->data_seek( 0 );				
				$row = $gsiDB->result->fetch_assoc();
				$this->id	= $row[ "ID" ];
				if ( $debug ) {
					$this->logger->log( "\$this->id = [$this->id]", 1 );
				}
				
			} 
			$gsiDB->closeDB();
			$retVal	= true;

		} catch ( Exception $ex ) {
			if ( $debug ) {
				$this->logger->log( "Exception caught in DBTable::insertRecord: [" . $ex->getMessage() . "]", 1 );
				$this->logger->log( "\$queryStr = [$queryStr]", 1 );
			}
			$this->errorMessage	= "Exception caught in DBTable::insertRecord: [" . $ex->getMessage() . "]";
			$gsiDB->closeDB();
			throw $ex;
		}

		unset( $gsiDB );
		
			// Load the record
			
		$this->loadRecordById( $this->id );
		if ( $debug ) {
			$this->trace();
		}

		if ( $debug ) {
			$this->logger->log( "\$retVal = [$retVal]", 1 );
			$this->logger->log( "Done DBTable::insertRecord", 1 );
		}
		return $retVal;
	}



	//
	// loadRecordByID -- Loads a single record by ID
	//
	public function loadRecordByID( $pID ) {
		$debug	= $this->debug;
		$retVal	= false;
		if ( $debug ) {
			$this->logger->log( "In DBTable::loadRecordByID" );
			$this->logger->log( "\$pID = [$pID]", 1 );
			$this->logger->log( "\$this->tableName = [$this->tableName]", 1 );
		}
		
		if ( $this->tableName != "" ) {
		
				// Build the query string
				
			$queryStr	= "SELECT * FROM " . $this->tableName . " WHERE ID = $pID";
			
				// Get the reoord
				
			$gsiDB	= new Database();
			try {
				$gsiDB->openDB();
				$gsiDB->execQueryStr( $queryStr );

				if ( $gsiDB->numrows > 0 ) {
					if ( $debug ) {
						$this->logger->log( "Record found", 1 );
					}

						// set properties to incoming data from DB

					$gsiDB->result->data_seek( 0 );
					$row	= $gsiDB->result->fetch_assoc();

						// Save the fields 

					$this->id				= $row[ "ID" ];
					$this->dateAdded		= $row[ "DateAdded" ];
					$this->dateChanged		= $row[ "DateChanged" ];

					$this->tableFields	= array();
					foreach ( $row as $key => $value ) {
						if ( $debug ) {
							$this->logger->log( "\$key = [$key], \$value = [$value]", 1 );
						}
					
						if (( $key != "ID" )
								&& ( $key != "DateAdded" )
								&& ( $key != "DateChanged" )) {
							$this->tableFields[ $key ]	= $value;
						}
					}
						
						// Mark the object loaded

					$retVal				= true;
					$this->wasLoaded	= true;

					if ( $debug ) {
						$this->trace();
					}
				} 

				$gsiDB->closeDB();

			} catch ( Exception $ex ) {
				$this->logger->log( "Exception caught in DBTable::loadRecordByID: [" . $ex->getMessage() . "]", 1 );
				$gsiDB->closeDB();
				throw $ex;
			}

			unset( $gsiDB );



		} else {
			if ( $debug ) {
				$this->logger->log( "\$this->tableName not set, exiting", 1 );
			}
		}
		
		if ( $debug ) {
			$this->logger->log( "Done DBTable::loadRecordByID, \$retVal = [$retVal]", 1 );
		}
		return $retVal;

	}


	// 
	// loadTableStructure -- loads the structure of a table from the database.  Returns TRUE if successful.
	//
	public function loadTableStructure() {
		$debug	= $this->debug;
		$retVal	= false;
		if ( $debug ) {
			$this->logger->log( "In DBTable::loadTableStructure" );
		}
		
			// Make sure the table name is set
			
		if ( $this->tableName == "" ) {
			if ( $debug ) {
				$this->logger->log( "\$tableName is not set, exiting", 1 );
			}
			return $retVal;
		} 
		
			// Build the query string

		$queryStr	= "DESCRIBE $this->tableName";

			// Get the reoord

		$gsiDB	= new Database();
		try {
			$gsiDB->openDB();
			$gsiDB->execQueryStr( $queryStr );

			if ( $gsiDB->numrows > 0 ) {
				if ( $debug ) {
					$this->logger->log( "Record found", 1 );
				}

					// Save the structure

				$gsiDB->result->data_seek( 0 );
				while ( $row = $gsiDB->result->fetch_assoc()) {
					$this->tableStructure[ $row[ "Field" ]] = array( "Type" => $row[ "Type" ],
																		"Key" => $row[ "Key" ],
																		"Null" => $row[ "Null" ],
																		"Default" => $row[ "Default" ],
																		"Extra" => $row[ "Extra" ]);
					if ( $debug ) {
						foreach ( $this->tableStructure[ $row[ "Field" ]] as $col => $value ) {
							$this->logger->log( "\$this->tableStructure[ " . 
												$row[ "Field" ] . 
												" ][ $col ] = [" .
												$value . "]", 1 );
						}
					}
					
						// Check if this is a required value
						
					if (( $this->tableStructure[ $row[ "Field" ]][ "Null" ] == "NO" )
							&& ( is_null( $this->tableStructure[ $row[ "Field" ]][ "Default" ]))
							&& ( $this->tableStructure[ $row[ "Field" ]][ "Extra" ] != "auto_increment" )) {
						$this->requiredVals[ $row[ "Field" ]]	= $row[ "Field" ] . " value missing";
					}
				}

				$retVal	= true;
			} else {
				$this->errorMessage	= "Can't find this table in the database";
				if ( $debug ) {
					$this->logger->log( "Can't find this table in the database", 1 );
				}
			}
			
			$gsiDB->closeDB();
		} catch ( Exception $ex ) {
			$this->logger->log( "Exception caught in DBTable::loadTableStructure: [" . $ex->getMessage() . "]", 1 );
			$gsiDB->closeDB();
			throw $ex;
		}
			
		unset( $gsiDB );

		if ( $debug ) {
			$this->logger->log( "\$retVal = [$retVal]", 1 );
			$this->logger->log( "Done DBTable::loadTableStructure", 1 );
		}
		return $retVal;
	}


	//
	// updateRecord -- updates a record into the table.  Returns TRUE if successful.  Sets the errorMessage property
	// on failure.
	//
	public function updateRecord() {
		$debug	= $this->debug;
		$retVal	= false;
		if ( $debug ) {
			$this->logger->log( "In DBTable::updateRecord" );
		}
		
			// Make sure the table name is set
			
		if ( $this->tableName == "" ) {
			$this->errorMessage	= "tableName property not set.";
			return $retVal;
		} 
		
			// Make sure the id is
			
		if ( $this->id == "" ) {
			$this->errorMessage	= "id property not set.";
			return $retVal;
		} 
		
			// Make sure the table fields are set
			
		if ( count( $this->tableFields ) == 0 ) {
			$this->errorMessage	= "tableFields property not set.";
			return $retVal;
		}
		
			// Get the structure of this table
		
		if ( !$this->loadTableStructure()) {
			return $retVal;
		}
			
			// Build the query string
			
		$setStr	= "";
		foreach ( $this->tableFields as $key => $value ) {
			if ( $debug ) {
				$this->logger->log( "\$key = [$key]", 1 );
				$this->logger->log( "\$value = [$value]", 1 );
			}
		
				// Add the field name
				
			if ( !array_key_exists( $key, $this->tableStructure )) {
				$this->errorMessage	= "Field [$key] does not exist in this table";
				return $retVal;
			}
			if ( strlen( $setStr ) > 0 ) {
				$setStr	.= ", \n";
			}
			$setStr	.= "\t" . $key . " = ";
			
				// Add the value
				
			$valType	= $this->tableStructure[ $key ][ "Type" ];
			$valueStr	= "";
			if ( $debug ) {
				$this->logger->log( "\$valType = [$valType]", 1 );
			}

			if (( $valType == "datetime" ) 
					|| ( $valType == "timestamp" )
					|| ( strpos( $valType, "varchar" ) > -1 )
					|| ( strpos( $valType, "text" ) > -1 )) {
				$valueStr	= "'" . $value . "'";
			} else {
				if ( $value != "" ) {
					$valueStr	= $value;
				} else {
					$valueStr	= "null";			// Note: this will return an error if NOT NULL, but should
													// have been caught above when checking required params
				}
				
			}
			$setStr	.= $valueStr;
			
		}
		
		$queryStr	= "UPDATE " . $this->tableName . " SET \n" .
						$setStr . " \n" .
						"WHERE ID = " . $this->id;
		if ( $debug ) {
			$this->logger->log( "\$queryStr = [$queryStr]", 1 );
		}
						
			// Update the record
			
		$gsiDB	= new Database();
		try {

			$gsiDB->openDB();
			$gsiDB->execQueryStr( $queryStr );
			$gsiDB->closeDB();
			$retVal	= true;

		} catch ( Exception $ex ) {
			if ( $debug ) {
				$this->logger->log( "Exception caught in DBTable::updateRecord: [" . $ex->getMessage() . "]", 1 );
			}
			$this->errorMessage	= "Exception caught in DBTable::updateRecord: [" . $ex->getMessage() . "]";
			$gsiDB->closeDB();
			throw $ex;
		}

		unset( $gsiDB );
		
			// Load the record
			
		$this->loadRecordById( $this->id );
		if ( $debug ) {
			$this->trace();
		}

		if ( $debug ) {
			$this->logger->log( "\$retVal = [$retVal]", 1 );
			$this->logger->log( "Done DBTable::updateRecord", 1 );
		}
		return $retVal;
	}



	//
	// trace -- traces out the current properties of this object to the log
	//
	public function trace() {

		$this->logger->log( "In DBTable::trace" );
		$this->logger->log( "\$id	= [$this->id]", 1 );
		$this->logger->log( "\$dateAdded	= [$this->dateAdded]", 1 );
		$this->logger->log( "\$dateChanged	= [$this->dateChanged]", 1 );
		
		foreach ( $this->tableFields as $key => $value ) {
			$this->logger->log( "\$this->tableFields[ \"$key\" ] = [$value]", 1 );
		}

	}


	//
	// toJSONStr -- Returns a JSON string with all the values for this object
	//
	public function toJSONStr() {
		$debug	= $this->debug;
		
		$retVal	= "";
		
		$output	= array (
						"ID"	=> $this->id,
						"DateAdded"	=> $this->dateAdded, 
						"DateChanged"	=> $this->dateChanged
						);
						
		$output	= array_merge( $output, $this->tableFields );

		$retVal	= json_encode( $output );
		
		return $retVal;
	}



}
?>