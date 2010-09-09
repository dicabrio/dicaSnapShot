<?php

class SettingsController implements Controller {

	public function __construct($method) {

	}

	public function _index() {

		$req = Request::getInstance();

		$mysqlLocation = Settings::getByName('mysql_location');
		$snapshotDir = Settings::getByName('snapshot_dir');
		
		if ($req->post('update') != null) {
			
			$mysqlLocation->setValue($req->post($mysqlLocation->getName()));
			$mysqlLocation->save();
			$snapshotDir->setValue($req->post($snapshotDir->getName()));
			$snapshotDir->save();
			
		}

		$view = new View('general/settings.php');
		$view->settings = array($mysqlLocation, $snapshotDir);
		return $view->getContents();
	}

	public function _default() {

	}

	public function setArguments($arguments) {
		$this->arguments = $arguments;
	}
}