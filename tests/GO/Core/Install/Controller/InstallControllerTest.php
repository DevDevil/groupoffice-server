<?php

namespace GO\Core\Install\Controller;

use GO\Core\App;
use PHPUnit_Framework_TestCase;

class InstallControllerTest extends PHPUnit_Framework_TestCase {
	
	
/**
 * @runInSeparateProcess
 */
	public function testInstallDatabase(){
	
		
		$configFile = dirname(__FILE__).'/../../../../../config.php';
		//Initialize the framework with confuration
		\GO\Core\App::init(require($configFile));

		\GO\Core\App::config()->classLoader = $GLOBALS['classLoader'];

		
		$testdb = "GO_TEST_".date("YmdGis")."_".uniqid();
		
		$dbExists = \GO\Core\Db\Utils::databaseExists($testdb);
		
		$this->assertEquals(false, $dbExists);
						
		
		App::dbConnection()->query("CREATE DATABASE ".$testdb);
		
		//Set new database
		App::dbConnection()->database = $testdb;
		App::dbConnection()->setPDO();
		
		$controller = new InstallController(App::router());
		
		$response = $controller->httpGet();
	
		$this->assertEquals(true, empty($response['errors']));
		$this->assertEquals(true, $response['success']);
		
		
		App::dbConnection()->query("DROP DATABASE ".$testdb);
	}
}