<?php

/**
*
 *	Development server control panel.
 *	Version 0.2, written by Dennis Futselaar <dennis.futselaar@webgamic.nl>
*
*	This class is responsible for creating, deleting and listing snapshots

	This class also lists databases
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once('Database.class.php');



class Snapshot {

	private $oDatabase		= null;
	private $aDatabases		= array();

	function __construct() {
		$this->oDatabase	= new Database();
		foreach ($this->oDatabase->getDatabases() as $sDatabase) {
			// Filter out databases.
			if ($sDatabase != 'mysql' && $sDatabase != 'information_schema' && $sDatabase != 'dev-server') {
				$this->aDatabases[$sDatabase]	= $sDatabase;
			}
		}
	}

	function updateSnapshot($sID) {

		$sID		= basename($sID);
		//$sFilename	= '/usr/projects/snapshots/'.$sID.'.sql';
		$sFilename	= SNAP_PATH.$sID.'.sql';
		if (!file_exists($sFilename)) {
			return false;
		}

		//extract adus revision
		$aDatabase	= $this->getDatabaseInfo($sID);

		// Get the extra sql
		$sAdusURL = str_replace("{revision}", $aDatabase['adus'], 'http://dbatool.webgamic.nl/?mod=adus&sv=check&prot=ajaxpost&rev={revision}');
		$sAdusData = @file_get_contents($sAdusURL);
		if($sAdusData === FALSE) {
			$aQueries = array();
		}
		else {
			// Extract the queries, if any
			@(list ( $iNewRevision, $sInfo ) = split("\|", $sAdusData, 2));
			if($sInfo === NULL) {
				$sQueries = "";
			}
			else {
				$sQueries = str_replace("<BOUNDARY>","\n",$sInfo);
			}
		}

		//no new revision
		if ($iNewRevision==='noqueries') {
			return false;
		}

		file_put_contents( $sFilename, $sQueries, FILE_APPEND );
		$aDatabase['adus'] = $iNewRevision;

		rename( $sFilename, $this->buildFileName( $aDatabase ));


	}

	function rename ( $sID, $sNewName ) {

		$sID		= basename($sID);
		//$sFilename	= '/usr/projects/snapshotsnapshots/'.$sID.'.sql';
		$sFilename	= SNAP_PATH.$sID.'.sql';
		if (!file_exists($sFilename)) {

			return false;
		}
		$aDatabase	= $this->getDatabaseInfo($sID);
		$aDatabase['title'] = $sNewName;

		rename( $sFilename, $this->buildFileName( $aDatabase ) );

	}

	private function buildFileName( $aDatabase ) {

		return SNAP_PATH.$aDatabase['name'].'_'.$aDatabase['time'].'_a'.$aDatabase['adus'].'_'.$aDatabase['title'].'.sql';

	}

	function createSnapshot($sDatabaseName) {

		//fetch adus working revision from file
		$sAdusWorkingRev = ADUSREV_FILE;

		//set default revision
		$iRev = 0;
		if ( file_exists( $sAdusWorkingRev ) ) {
			$sRevCode = file_get_contents( $sAdusWorkingRev );
			if ( strpos( $sRevCode, "=" ) !== false ) {
				$aRevCodeParts = explode("=", $sRevCode );
				if ( count( $aRevCodeParts )===2 ) {
					$iRev = (int) $aRevCodeParts[ 1 ];
				}
			}
		}

		//create database name
		$sDatabaseName = $this->sanitizeDatabaseName( $sDatabaseName );

		//add adus revision to name
		$sAdusSuffix = "_a" . $iRev . "_";

		if (!$sDatabaseName) {
			return false;
		}

		// Now create a dump with mysqldump
		$sID	= $sDatabaseName.'_'.time() . $sAdusSuffix;

		// Execute the dump command
		$output = shell_exec(MYSQL_LOC.'mysqldump -u '.MYSQL_USER.' --password='.MYSQL_PASS.' -h '.MYSQL_HOST.' '.$sDatabaseName. ' > '.SNAP_PATH.$sID.'.sql');

		if (strpos($output, 'ERROR')) {
			return false;
		}
		// We are done here
		return $sID;

	}

	function restoreSnapshot($sID) {

		$sID		= basename($sID);
		$sFilename	= SNAP_PATH.$sID.'.sql';


		if (!file_exists($sFilename)) {
			return false;
		}

		$aDatabase	= $this->getDatabaseInfo($sID);

		$iAdusRevision = $aDatabase['adus'];

//		file_put_contents( ADUSREV_FILE , "rev=".$iAdusRevision );



		// Execute mysql with the file as the input
		$sToExecute	= MYSQL_LOC.'mysql -u '.MYSQL_USER.' --password='.MYSQL_PASS.' -h '.MYSQL_HOST.' --database='.$aDatabase['name'].' < '.$sFilename	;
		//echo $sToExecute; exit;
		$output = shell_exec($sToExecute);
		if (strpos($output, 'ERROR')) {
			throw new Exception($output, 0);
		}

		return true;

	}



	function deleteSnapshot($sID) {
		$sID		= basename($sID);
		$sFilename	= $this->getURL($sID);

		if (!$sFilename) {
			return false;
		}

		return unlink($sFilename);

	}

	private function sanitizeDatabaseName($sDatabaseName) {

		// Sanitize database name
		if (!preg_match('/([A-Za-z_0-9\-]+)/', $sDatabaseName, $aMatches)) {
			return false;
		}

		//
		$sDatabaseName	= $aMatches[1];

		if (!isset($this->aDatabases[$sDatabaseName])) {
			return false;
		}

		return $sDatabaseName;

	}



	private function getDatabaseInfo($sID) {
		// Get database.
		if (!preg_match('/([A-Za-z_0-9\-]+?)\_([0-9]+)_?a?([0-9]+)?_?(.*)?/', $sID, $aMatches)) {
			return false;
		}


		return array(
			'name' => $aMatches[1], 'time' => $aMatches[2],
			'adus' => (isset($aMatches[3])) ? $aMatches[3] : 0,
			'title' => (isset($aMatches[4])) ? $aMatches[4] : '');

	}

	/**
	*
	*	This method gets all the snapshots and returns it in an array
	*
	*/

	function getSnapshots($sDatabaseName=null) {
		$aAllFiles	= scandir(SNAP_PATH);
		$aReturn	= array();
		foreach ($aAllFiles as $sFile) {
			if (substr($sFile, -4) == '.sql') {
				if ($sDatabaseName !== null) {
					if (false !== strpos($sFile, $sDatabaseName)) {
						$sID			= substr($sFile, 0, -4);
						$aReturn[$sID]	= $this->getDatabaseInfo($sID);
					}
				} else {
					$sID			= substr($sFile, 0, -4);
					$aReturn[$sID]	= $this->getDatabaseInfo($sID);
				}
//				$sID			= substr($sFile, 0, -4);
//				$aReturn[$sID]	= $this->getDatabaseInfo($sID);
			}
		}

		return $aReturn;
	}

	/**
	*
	*	This method gets all the databases that can be snapshotted
	*
	*/

	function getDatabases() {
		return $this->aDatabases;
	}

	function getURL($sID) {
		$sFilename	= 'file://'.SNAP_PATH.$sID.'.sql';

		if (!file_exists($sFilename)) {
			return false;
		}

		return $sFilename;
	}

	function copySnapshotToServer($sID) {
		if (!($sFilename = $this->getURL($sID))) {
			return false;
		}

		// Connect to remote server
		$rFTP	= @ftp_connect(FTP_HOST, 21, 5);
		if (!$rFTP) {
			return false;
		}

		if ($bResult = @ftp_login($rFTP, FTP_USER, FTP_PASS)) {

			$sRFilename	= FTP_DIR.$sID.'.sql';

			// Upload file
			$bResult = @ftp_put($rFTP, $sRFilename, $sFilename, FTP_BINARY);

		}

		@ftp_close($rFTP);

		return $bResult;

	}

	function getRemoteSnapshots() {
		// Connect to remote server
		$rFTP	= @ftp_connect(FTP_HOST, 21, 5);
		if (!$rFTP) {
			return array();
		}

		if (!@ftp_login($rFTP, FTP_USER, FTP_PASS)) {
			@ftp_close($rFTP);
			return array();
		}

		$aList	= ftp_nlist($rFTP, FTP_DIR);

		@ftp_close($rFTP);

		$aReturn	= array();
		//
		foreach ($aList as $sEntry) {
			if (substr($sEntry, -4) == '.sql') {
				$sID = basename(substr($sEntry, 0, -4));
				if (($aEntryInfo = $this->getDatabaseInfo($sID)) !== false) {
					$aReturn[$sID] = $aEntryInfo;
				}
			}
		}

		return $aReturn;
	}

	function copySnapshotToLocal($sID) {
		// Connect to remote server
		$rFTP	= @ftp_connect(FTP_HOST, 21, 5);
		if (!$rFTP) {
			return array();
		}

		if ($bResult = @ftp_login($rFTP, FTP_USER, FTP_PASS)) {

			$sRFilename	= FTP_DIR.$sID.'.sql';
			$sFilename	= SNAP_PATH.$sID.'.sql';

			// Upload file
			$bResult = @ftp_get($rFTP, $sFilename, $sRFilename, FTP_BINARY);
		}

		@ftp_close($rFTP);

		return $bResult;

	}

	function delServerSnapshot($sID) {
		// Connect to remote server
		$rFTP	= @ftp_connect(FTP_HOST, 21, 5);
		if (!$rFTP) {
			return array();
		}

		if ($bResult = @ftp_login($rFTP, FTP_USER, FTP_PASS)) {
			$sRFilename	= FTP_DIR.$sID.'.sql';
			// Delete file
			$bResult = @ftp_delete($rFTP, $sRFilename);
		}

		@ftp_close($rFTP);

		return $bResult;
	}
}