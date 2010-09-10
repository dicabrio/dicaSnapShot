<?php
/**
 * @todo when no config variables like mysql_location and
 * snapshot_dir are set or found in the database redirect them to
 * a settings controller so those variables can be saved in the database
 * 
 */
class SnapshotController implements Controller {

	/**
	 * @var array
	 */
	private $arguments;

	/**
	 * @var View
	 */
	private $view;

	/**
	 *
	 * @var array
	 */
	private $databases;

	/**
	 * @var string
	 */
	private $snapshotsLocation;

	/**
	 * @var string
	 */
	private $mysqlLocation;

	/**
	 *
	 * @var Session
	 */
	private $session;

	/**
	 *
	 * @var Request
	 */
	private $request;

	/**
	 * @param string $method
	 */
	public function __construct($method) {

		$this->session = Session::getInstance();
		$this->request = Request::getInstance();

		$mysqlLocation = Settings::getByName('mysql_location');
		$snapshotDir = Settings::getByName('snapshot_dir');

		$this->snapshotsLocation = $snapshotDir->getValue();
		$this->mysqlLocation = $mysqlLocation->getValue();

		if (empty($this->snapshotsLocation) || empty($this->mysqlLocation)) {
			$this->session->set('notice', 'You need to specify the snapshot path or the mysql location');
			Util::gotoPage(Conf::get('general.url.www').'/settings/');
		}

		$this->databases = Database::getAllDatabases($this->snapshotsLocation,
					$this->mysqlLocation,
					Conf::get('database.dbhost'),
					Conf::get('database.dbuser'),
					Conf::get('database.dbpass'));

		$this->view = new View('snapshot/listalldatabases.php');
		$this->view->assign('databases', $this->databases);

	}

	public function _index() {

		$this->view->error = $this->session->get('error');
		$this->session->set('error', null);

		return $this->view->getContents();

	}

	public function _default() {

	}

	public function setArguments($arguments) {
		$this->arguments = $arguments;
	}

	/**
	 * @return Database
	 */
	private function getDatabase() {
		$databaseName = Util::getUrlSegment(2);
		if (!isset($this->databases[$databaseName])) {
			$this->session->set('error', 'Cannot create a snapshot of this database. It doesn\'t exist');
			Util::gotoPage(Conf::get('general.url.www').'/snapshot/#'.$databaseName);
		}

		return $this->databases[$databaseName];
	}

	public function create() {
		
		try {

			$database = $this->getDatabase();
			$database->createSnapshot();

		} catch (Exception $e) {
			$this->session->set('error', $e->getMessage());
		}

		Util::gotoPage(Conf::get('general.url.www').'/snapshot/#'.$database->getName());

	}

	public function restore() {

		try {
			
			$database = $this->getDatabase();
			$database->restoreSnapshot(Util::getUrlSegment(3));

		} catch (Exception $e) {
			$this->session->set('error', $e->getMessage());
		}

		Util::gotoPage(Conf::get('general.url.www').'/snapshot/#'.$database->getName());
		
	}

	public function delete() {

		try {

			$database = $this->getDatabase();
			$database->deleteSnapshot(Util::getUrlSegment(3));

		} catch (Exception $e) {
			$this->session->set('error', $e->getMessage());
		}

		Util::gotoPage(Conf::get('general.url.www').'/snapshot/#'.$database->getName());

	}

	public function rename() {

		if (Request::method() != Request::POST) {
			Util::gotoPage(Conf::get('general.url.www').'/snapshot/');
		}

		try {

			$oldSnapshotName =  Util::getUrlSegment(3);
			$newTitle = $this->request->post('renameto');

			$database = $this->getDatabase();
			$database->renameSnapshot($oldSnapshotName, $newTitle);

		} catch (Exception $e) {
			$this->session->set('error', $e->getMessage());
		}

		Util::gotoPage(Conf::get('general.url.www').'/snapshot/#'.$database->getName());
	}
}