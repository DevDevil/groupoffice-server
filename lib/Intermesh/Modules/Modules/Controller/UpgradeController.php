<?php

namespace Intermesh\Modules\Modules\Controller;

use Intermesh\Core\Controller\AbstractRESTController;
use Intermesh\Core\Db\Utils;
use Intermesh\Core\Fs\File;
use Intermesh\Modules\Contacts\ContactsModule;
use Intermesh\Modules\Modules\Model\Module;
use Intermesh\Modules\Modules\ModulesModule;

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
		
		$module = new Module();
		$module->name = ContactsModule::className();
		$module->save();

		Module::runModuleUpdates();
	
		return $this->renderJson($this->response);
	}
	
	private function initDatabase(){
		
		if(Utils::tableExists('modulesModule')){
			//if modulesModule exists the database must be installed
			return true;
		}
		
		$modulesManager = new ModulesModule();
		
		$initSqlFile = new File($modulesManager->path().'/Install/Init.sql');
		
		Utils::runSQLFile($initSqlFile);
		
		
		
		
	}

	

	

}
