<?php

/**
*
*	Database class
*
*/

class Database {

	private $aDatabases	= array();

	function __construct() {
	
		$rAllDatabases		= mysql_query("SHOW DATABASES");
		if (mysql_num_rows($rAllDatabases)) {
		
			while ($aInfo = mysql_fetch_assoc($rAllDatabases)) {
			
				$sDatabase	= $aInfo['Database'];
				$this->aDatabases[$sDatabase]	= $sDatabase;
			
			}
		
		}
	
	}

	function getDatabases() {
		return $this->aDatabases;
	}

}