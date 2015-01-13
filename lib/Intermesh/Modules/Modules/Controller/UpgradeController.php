<?php

namespace Intermesh\Modules\Modules\Controller;

use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractRESTController;
use Intermesh\Core\Db\Utils;
use Intermesh\Core\Fs\File;
use Intermesh\Modules\Modules\Model\Module;
use Intermesh\Modules\Modules\ModulesModule;
use Intermesh\Modules\Modules\ModuleUtils;

/**
 * Perform system check
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class UpgradeController extends AbstractRESTController {
	
	private $response = [];
	
	/**
	 * Authenticate the current user
	 * 
	 * Override this for special use cases.
	 * 
	 * @return boolean
	 */
	protected function authenticate(){
		return true;
	}

	/**
	 * Run system tests
	 */
	public function httpGet() {
		
		$this->initDatabase();
		
		$modules = ModuleUtils::getModules();
		
		foreach($modules as $module){
			if($module->autoInstall()){
				$module->install();
			}
		}
						
		
		Module::runModuleUpdates();
	
		return $this->renderJson($this->response);
	}
	
	private function setUtf8Collation(){
		//Set utf8 as collation default
		$sql = "ALTER DATABASE `".App::dbConnection()->database."` CHARACTER SET utf8 COLLATE utf8_unicode_ci;";		
		App::dbConnection()->query($sql);
		

		
	}
	
	private function initDatabase(){
		
		if(Utils::tableExists('modulesModule')){
			//if modulesModule exists the database must be installed
			return true;
		}
		
		$this->setUtf8Collation();
		
		$modulesManager = new ModulesModule();
		
		$initSqlFile = new File($modulesManager->path().'/Install/Init.sql');
		
		Utils::runSQLFile($initSqlFile);
		
		
		
		
	}

	

	

}
