<?php

namespace GO\Core\Install\Model;

use GO\Core\AbstractModel;
use GO\Core\App;
use PDOException;

/**
 * System check result model
 * 
 * It contains a boolean and feedback.
 */
class SystemCheckResult extends AbstractModel {

	public $success;
	public $msg;

	public function __construct($success, $msg = "OK") {

		parent::__construct();

		$this->success = $success;
		$this->msg = $msg;
	}

}
/**
 * System check model to test if the system meets the system requirements
 */
class SystemCheck extends AbstractModel {

	private $_checks = [];

	public function __construct() {
		parent::__construct();

		$this->_registerCheck(
						"PHP version", function() {
			$version = phpversion();
			if (version_compare($version, "5.4", ">=")) {
				return new SystemCheckResult(true, "OK (" . $version . ")");
			} else {

				return new SystemCheckResult(false, "Your PHP version ($version) is older then the required version 5.4");
			}
		});

		$this->_registerCheck(
						"Database connection", function() {
			try {
				$conn = App::dbConnection()->getPDO();
				return new SystemCheckResult(true, "Connection established");
			} catch (PDOException $e) {
				return new SystemCheckResult(false, "Couldn't connect to database. Please check the config. PDO Exception: " . $e->getMessage());
			}
		});

		$this->_registerCheck(
						"Temp folder", function() {
			$file = App::config()->getTempFolder()->createFile("test.txt");

			if ($file->touch()) {

				$file->delete();

				return new SystemCheckResult(true, 'Is writable');
			} else {
				return new SystemCheckResult(false, "'" . App::config()->getTempFolder() . "' is not writable!");
			}
		});

		$this->_registerCheck(
						"Data folder", function() {

			$folder = App::config()->getDataFolder();

			if (!$folder->exists()) {
				return new SystemCheckResult(false, '"' . $folder . '" doesn\'t exist');
			}

			$file = $folder->createFile("test.txt");

			if ($file->touch()) {

				$file->delete();

				return new SystemCheckResult(true, "Is writable");
			} else {
				return new SystemCheckResult(false, "'" . $folder . "' is not writable!");
			}
		});
	}

	private function _registerCheck($name, $function) {
		$this->_checks[$name] = $function;
	}
	
	/**
	 * Run all system checks
	 * 
	 * It will return an array with a success boolean and array of system check results.
	 * @return array
	 */
	public function run(){
		
		$response = [
			'success' => true, 
			'databaseInstalled' => System::isDatabaseInstalled(),
			'installUrl' => App::router()->buildUrl('/system/install'),
			'upgradeUrl' => App::router()->buildUrl('/system/upgrade'),
			'checks' => []
			];
		
		foreach($this->_checks as $name => $function){
			
			$result = $function();
			
			/* @var $result SystemCheckResult */			
			if(!$result->success){
				$response['success'] = false;
			}
			
			$response['checks'][$name] = $result->toArray();
		}
		
		return $response;		
	}
}