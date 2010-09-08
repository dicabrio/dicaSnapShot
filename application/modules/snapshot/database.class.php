<?php

class Database extends DataRecord {

	private static $donotshow = array('mysql' => 'mysql', 'information_schema' => 'information_schema');

	private $location = '.';

	private $snapshots;

	/**
	 * @param string $name
	 */
	public function __construct($name='') {
		$this->setAttr('Database', $name);
	}

	protected function defineColumns() {
		parent::addColumn('Database', DataTypes::VARCHAR, 255);
	}

	/**
	 *
	 * @param string $mysqlLocation
	 * @param string $mysqlUser
	 * @param string $mysqlPass
	 * @param string $mysqlHost
	 * @param string $whereToSave
	 */
	public function createSnapshot($mysqlLocation, $mysqlUser, $mysqlPass, $mysqlHost, $whereToSave) {
		$name = $this->getAttr('Database');
		$snapshot = $whereToSave.$name.'_'.time().'_a0_.sql';
		$sToExecute	= $mysqlLocation.'mysqldump -u '.$mysqlUser.' --password='.$mysqlPass.' -h '.$mysqlHost.' '.$name.' > '.$snapshot;
		$output = shell_exec($sToExecute);

		return new Snapshot($name, $whereToSave, $snapshot);
	}

	public function restoreSnapshot($mysqlLocation, $mysqlUser, $mysqlPass, $mysqlHost, $snapshotName) {
		$snapshot = $this->getSnapshot($snapshotName);

		if ($snapshot == null) {
			throw new SnapshotException('There is no snapshot "'.$snapshot.'" found for '.$this->getAttr('Database').'');
		}

		$sToExecute	= $mysqlLocation.'mysql -u '.$mysqlUser.' --password='.$mysqlPass.' -h '.$mysqlHost.' --database='.$this->getAttr('Database').' < '.$snapshot->getSnapshotFullPathFile();
		$output = shell_exec($sToExecute);

	}

	public function setLocation($location) {
		$this->location = $location;
	}

	/**
	 *
	 * @return string
	 */
	public function getName() {
		return $this->getAttr('Database');
	}

	/**
	 * @return array
	 */
	public function getSnapshots() {

		if ($this->snapshots === null) {
			$this->snapshots = Snapshot::getForDatabase($this->getAttr('Database'), $this->location);
		}

		return $this->snapshots;

	}

	/**
	 * @param string $name
	 * @return Snapshot
	 */
	private function getSnapshot($name) {

		$this->getSnapshots();

		foreach ($this->snapshots as $snapshot) {
			if ($snapshot->getSnapshotFile() == $name) {
				return $snapshot;
			}
		}

		return null;
	}

	/**
	 * @todo this should have parameters for dbuser, dbpass, dbhost so it can pass it through to the databases
	 * this way it will not have to ask for it in the restore and create method
	 *
	 * 
	 * factory method. Getting all databases
	 * @return array
	 */
	public static function getAllDatabases($location) {

		$foundDatabases = parent::findBySql(__CLASS__, "SHOW DATABASES");
		
		$databases = array();
		foreach ($foundDatabases as $database) {
			if (!in_array($database->getName(), self::$donotshow)) {
				$database->setLocation($location);
				$databases[$database->getName()] = $database;
			}
		}

		return $databases;
	}

}

class Snapshot {

	private $dbname;

	private $location;

	private $snapshot;

	private $timeOfCreation;

	private $label;

	public function __construct($dbname, $location, $snapshot) {

		if (empty($dbname)) {
			throw new SnapshotException('No databasename given to create a snapshot');
		}

		if (empty($location)) {
			throw new SnapshotException('No location given to set the dump');
		}
		
		$this->dbname = $dbname;
		$this->location = $location;

		$this->processSnapshotFilename($snapshot);

		$this->snapshot = $snapshot;

	}

	private function processSnapshotFilename($snapshot) {
		$stingToValidate = str_replace($this->location.$this->dbname.'_', '', $snapshot);
		$pattern = '/^(\d+)_a0_([a-zA-Z0-9-_]*)\.sql$/';
		if (!preg_match($pattern, $stingToValidate, $matches)) {
			throw new SnapshotException('The given snapshot is not valid. Perhaps it is of another database: '. $snapshot);
		}

		$this->timeOfCreation = $matches[1];
		$this->label = $matches[2];

	}

	public function getLabel() {
		return $this->label;
	}

	public function getSnapshotFile() {
		return str_replace($this->location, '', $this->snapshot);
	}

	public function getSnapshotFullPathFile() {
		return $this->snapshot;
	}

	public function getTimeOfCreation() {
		return $this->timeOfCreation;
	}

	public function delete() {
		$fileMan = new FileManager($this->snapshot);
		$fileMan->delete();
	}

	public static function getForDatabase($dbName, $location) {
		$snapshots = array();

		$snapshotFiles = glob($location.$dbName.'_*.sql');
		foreach ($snapshotFiles as $snapshot) {
			try {
				$ss = new Snapshot($dbName, $location, $snapshot);
				$snapshots[] = $ss;
			} catch (SnapshotException $e) {
				// not valid.. let it be
			}
		}

		return $snapshots;
	}
}

class SnapshotException extends Exception {}