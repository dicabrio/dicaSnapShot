<?php
/**
 * 
 */
class Database extends DataRecord {

	private static $donotshow = array('mysql' => 'mysql', 'information_schema' => 'information_schema');

	private $location = '.';

	private $snapshots;

	private $mysqllocation;

	private $dbuser;

	private $dbhost;

	private $dbpass;

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
	 * @param string $whereToSave
	 */
	public function createSnapshot($whereToSave=null) {

		if ($whereToSave == null) {
			$whereToSave = $this->location;
		}

		$name = $this->getAttr('Database');
		$snapshot = $whereToSave.$name.'_'.time().'_a0_.sql';
		$sToExecute	= $this->mysqllocation.'mysqldump -u '.$this->dbuser.' --password='.$this->dbpass.' -h '.$this->dbhost.' '.$name.' > '.$snapshot;
		$output = shell_exec($sToExecute);

		return new Snapshot($name, $whereToSave, $snapshot);
	}

	/**
	 *
	 * @param string $snapshotName
	 */
	public function restoreSnapshot($snapshotName) {
		$snapshot = $this->getSnapshot($snapshotName);
		$sToExecute	= $this->mysqllocation.'mysql -u '.$this->dbuser.' --password='.$this->dbpass.' -h '.$this->dbhost.' --database='.$this->getAttr('Database').' < '.$snapshot->getSnapshotFullPathFile();
		$output = shell_exec($sToExecute);

	}

	public function renameSnapshot($oldSnapshotName, $newSnapshotName) {
		$snapshot = $this->getSnapshot($oldSnapshotName);
		$snapshot->addLabel($newSnapshotName);
	}

	/**
	 *
	 * @param string $snapshotName
	 */
	public function deleteSnapshot($snapshotName) {
		$snapshot = $this->getSnapshot($snapshotName);
		$snapshot->delete();
	}

	public function setSnapshotLocation($location) {
		$this->location = $location;
	}
	
	public function setMySQLPath($path) {
		$this->mysqllocation = $path;
	}

	public function setDbCredentials($dbhost, $dbuser, $dbpass) {
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
	}

	/**
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

		$snapshot = null;
		foreach ($this->snapshots as $snapshot) {
			if ($snapshot->getSnapshotFile() == $name) {
				return $snapshot;
			}
		}

		throw new SnapshotException('There is no snapshot "'.$snapshot.'" found for '.$this->getAttr('Database').'');
	}

	/**
	 * @todo this should have parameters for dbuser, dbpass, dbhost so it can pass it through to the databases
	 * this way it will not have to ask for it in the restore and create method
	 *
	 * 
	 * factory method. Getting all databases
	 * @return array
	 */
	public static function getAllDatabases($location, $mysqlPath, $dbhost, $dbuser, $dbpass) {

		$foundDatabases = parent::findBySql(__CLASS__, "SHOW DATABASES");
		
		$databases = array();
		foreach ($foundDatabases as $database) {
			if (!in_array($database->getName(), self::$donotshow)) {
				$database->setSnapshotLocation($location);
				$database->setMySQLPath($mysqlPath);
				$database->setDbCredentials($dbhost, $dbuser, $dbpass);
				$databases[$database->getName()] = $database;
			}
		}

		return $databases;
	}

}

/**
 *
 */
class Snapshot {

	/**
	 *
	 * @var string
	 */
	private $dbname;

	/**
	 *
	 * @var location
	 */
	private $location;

	/**
	 *
	 * @var string
	 */
	private $snapshot;

	/**
	 *
	 * @var string
	 */
	private $timeOfCreation;

	/**
	 *
	 * @var string
	 */
	private $label;

	/**
	 *
	 * @param string $dbname
	 * @param string $location
	 * @param string $snapshot
	 */
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

	/**
	 *
	 * @param string $snapshot
	 */
	private function processSnapshotFilename($snapshot) {
		$stingToValidate = str_replace($this->location.$this->dbname.'_', '', $snapshot);
		$pattern = '/^(\d+)_a0_([a-zA-Z0-9-_]*)\.sql$/';
		if (!preg_match($pattern, $stingToValidate, $matches)) {
			throw new SnapshotException('The given snapshot is not valid. Perhaps it is of another database: '. $snapshot);
		}

		$this->timeOfCreation = $matches[1];
		$this->label = $matches[2];

	}

	/**
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 *
	 * @return string
	 */
	public function getSnapshotFile() {
		return str_replace($this->location, '', $this->snapshot);
	}

	/**
	 *
	 * @return string
	 */
	public function getSnapshotFullPathFile() {
		return $this->snapshot;
	}

	/**
	 *
	 * @return string
	 */
	public function getTimeOfCreation() {
		return $this->timeOfCreation;
	}

	/**
	 * @return void
	 */
	public function delete() {
		$fileMan = new FileManager($this->snapshot);
		$fileMan->delete();
	}

	/**
	 *
	 * @param string $title
	 */
	public function addLabel($title) {
		$title = str_replace(array(' ', '_'), '', trim($title));
		$fileMan = new FileManager($this->snapshot);
		$fileMan->moveTo($this->location, $this->dbname.'_'.$this->timeOfCreation.'_a0_'.$title.'.sql');
	}

	/**
	 *
	 * @param string $dbName
	 * @param string $location
	 * @return array
	 */
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

		return array_reverse($snapshots);
	}
}

class SnapshotException extends Exception {}