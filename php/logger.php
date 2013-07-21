<?php
// 
// Logger.php -- Copyright 2013 One Plus Love, Inc.
// 	
// This class implements a simple logger.  It will append messages to a log file with a name 
// set in the Config.php object, based upon today's date.  If the file does not exist, it will be created
// first.
// 
// This is a singleton class, use the singleton method to get a pointer to it.
// 
// Constructor
// createLogFile -- creates the filename for the current log if necessary.  Provides log 
// log -- write a message to the log with timestamp
//

require_once( "config.php" );

class Logger {

		// Constants 
		
		// Members
		
	public $debug		= false;
	
	private static $logDir		= "";
	private static $logPrefix	= "";
	private static $logSuffix	= "";
	private static $logStamp	= "";
	private static $logDate		= "";
    
	public static $curLogFile	= "";

    private static $instance;		// Hold an instance of the class
	

	//
	// Constructor
	// 
    // A private constructor; prevents direct creation of object
    //
    private function __construct() {
    	if ( $this->debug ) {
    	    echo "Logger has been constructed\n";
    	}
    	
    	$config				= new Config();
    	self::$logDir		= $config->LOGDIR;
    	self::$logPrefix	= $config->LOGPREFIX;
    	self::$logSuffix	= $config->LOGSUFFIX;
    	self::$logStamp		= $config->LOGSTAMP;
    	
    	if ( $this->debug ) {
    	    echo "self::\$logDir = [" . self::$logDir . "]\n";
    	    echo "self::\$logPrefix = [" . self::$logPrefix . "]\n";
    	    echo "self::\$logSuffix = [" . self::$logSuffix . "]\n";
    	    echo "self::\$logStamp = [" . self::$logStamp . "]\n";
    	}
    }


	// 
    // The singleton method
    //
    public static function singleton() {
        if ( !isset( self::$instance )) {
            $className = __CLASS__;
            self::$instance = new $className;
        }

        return self::$instance;
    }
    

	//
	// createLogFile -- creates the filename for the current log if necessary.  Provides log 
	// rotation by days.  Stamps the file if it is just being created
	//
	public function createLogFile() {
		if ( $this->debug ) {
			echo "In createLogFile\n";
		}
		
			// Check if we must create the file 
			
		$mustCreate	= false;
		if ( self::$curLogFile != "" ) {
			if ( $this->debug ) {
				echo "self::\$curLogFile already set\n";
			}

				// Get the current date

			$curDateStr	= date( "Y_m_d" );
			if ( $this->debug ) {
				echo "\$curDateStr = [$curDateStr]\n";
			}
						
				// Check if the file already exists, and if the date is correct
				
			if ( file_exists( self::$curLogFile )) {
				if ( $this->debug ) {
					echo "File exists\n";
				}
				$fileDate	= substr( self::$curLogFile, 
										strpos( self::$curLogFile, self::$logPrefix ) + strlen( self::$logPrefix ),
										10 );
				if ( $this->debug ) {
					echo "\$fileDate = [$fileDate]\n";
				}
				
				if ( strcmp( $fileDate, $curDateStr ) != 0 ) {
					$mustCreate	= true;
				}
			} else {
				if ( $this->debug ) {
					echo "File not found\n";
				}
				
					// Check if the name we have can be used

				if ( strcmp( $curDateStr, self::$logDate ) != 0 ) {
					$mustCreate	= true;									// No, name is not correct date
				}
			}
		} else {
			$mustCreate	= true;
		}
		if ( $this->debug ) {
			echo "\$mustCreate = [" . ( $mustCreate ? "true" : "false" ) . "]\n";
		}
		
			// Check if we must create it		

		if ( $mustCreate ) {
			self::$logDate		= date( "Y_m_d" );						// Always save the date we create
			self::$curLogFile	= $_SERVER[ "DOCUMENT_ROOT" ] . 
									self::$logDir . 
									self::$curLogFile .
									self::$logPrefix . date( "Y_m_d" ) . self::$logSuffix;
		} else {
			if ( $this->debug ) {
				echo "curLogFile already set\n";
			}
		}
		if ( $this->debug ) {
			echo "self::\$curLogFile = [" . self::$curLogFile . "]\n";
		}
		
			// Check again if the file already exists, we may have created a new name
	
		if ( !file_exists( self::$curLogFile )) {
			if ( $this->debug ) {
				echo "This is a new log file\n";
			}
			
			$logStamp	= str_replace( "{date}", date( "m/d/Y" ), self::$logStamp );
			$logStamp	= str_replace( "{time}", date( "H:i:s" ), $logStamp );
			$logStamp	= str_replace( "{machine}", getenv( "COMPUTERNAME" ), $logStamp );
			
			error_log( $logStamp, 3, self::$curLogFile );			// Stamp the file with a header
		} else {
			if ( $this->debug ) {
				echo "Using existing file\n";
			}
		}
	}
	
	
	// 
	// log -- write a message to the log with timestamp
	//
	public function log( $message, $numTabs = 0 ) {
		if ( $this->debug ) {
			echo "In Logger::log\n";
		}

			// Stamp the message and call write
			
		$logmsg	= date( "H:i:s" ) . " " . $_SERVER[ "REMOTE_ADDR" ] . "    ";
		for ( $curTab = 0; $curTab < $numTabs; $curTab++ ) {
			$logmsg	.= "    ";
		}
		$logmsg	.= $message . "\n";
		$this->createLogFile();
		error_log( $logmsg, 3, self::$curLogFile );

	}		
}

?>