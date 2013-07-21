<?php
//
// Config.php -- Copyright 2013 One Plus Love, Inc.  All rights reserved.
//
// Configuration object for PHP code.  Note that this object should be used as a singleton.  This 
// class is getting called too much.
//
// Constructor -- sets env and db connection values
//

	// Load needed objects

class Config {


		//
		// Properties
		//

	public $debug			= false;
	public $env				= "";
	public $compName		= "" ;
	
		// Database

	public $dbUser			= "";
	public $dbPass			= "";
	public $dbServer		= "";
	public $dbName			= "";

		// Logging

	public $LOGDIR		= "/../logs/";
	public $LOGPREFIX	= "OPL_";
	public $LOGSUFFIX	= "_php_logfile.log";
	public $LOGSTAMP	= "\n****\n\nOPL Logging Started on {date} at {time} on server {machine}\n\n****\n\n";


		//
		// Methods
		//


	//
	// Constructor -- sets env and db connection values
	//
	function __construct() {
		if ( $this->debug ) {
			echo "Debugging turned on in Config.php<br>\n";
		}

			// Determine environment

		if ( isset( $_SERVER[ "SERVER_NAME" ])) {
			if ( $this->debug ) {
				echo "\$_SERVER[ \"SERVER_NAME\" ] = [" . $_SERVER[ "SERVER_NAME" ] . "]<br>\n";
			}
			$domain			= explode( ".", $_SERVER[ "SERVER_NAME" ]);
			if ( is_numeric( $domain[ 0 ] )) {
				$this->env		= $_SERVER[ "SERVER_NAME" ];
			} else {
				$this->env		= $domain[ 0 ];
			}
			$this->compName	= getenv( "COMPUTERNAME" );
		} else {
			throw new Exception( "Cannot determine server type" );
		}

		if ( $this->debug ) {
			echo "Config->env = [$this->env]<br>\n";
			echo "Config->compName = [$this->compName]<br>\n";
		}

			// Database connection parameters

		switch ( $this->env ) {

				// Localhost

			case "localhost":
			case "127.0.0.1":
				if ( $this->debug ) {
					echo "Using localhost configuration<br>\n";
				}
				$this->dbUser	= "root";
				$this->dbPass	= "f2Zb0x";
				$this->dbServer	= "127.0.0.1";
				$this->dbName	= "opl";

				break;

			default:
				if ( $this->debug ) {
					echo "Using GoDaddy testing configuration<br>\n";
				}
				$this->dbUser   = "oplapidb";
				$this->dbPass   = "pAnh3ad!opl";
				$this->dbServer = "50.63.244.200";
				$this->dbName   = "oplapidb";

				break;
		}

		if ( $this->debug ) {
			echo "Config->dbUser = [$this->dbUser]<br>\n";
			echo "Config->dbPass = [$this->dbPass]<br>\n";
			echo "Config->dbServer = [$this->dbServer]<br>\n";
			echo "Config->dbName = [$this->dbName]<br>\n";
		}
	}

}
?>
