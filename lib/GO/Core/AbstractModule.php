<?php

namespace GO\Core;

use Exception;
use GO\Core\Fs\File;
use GO\Core\Fs\Folder;
use GO\Core\Modules\Model\Module;
use GO\Core\Modules\Model\ModuleConfig;
use ReflectionClass;

/**
 * Module class
 * 
 * This class manages the module. It controls the installation, upgrades and
 * routes are defined for it.
 * 
 * Eg:
 * 
 * <code>
 * <?php
 * namespace GO\Modules\Bands;
 * 
 * use GO\Core\AbstractModule;
 * use GO\Modules\Bands\Controller\BandController;
 * use GO\Modules\Bands\Controller\HelloController;
 * 
 * class BandsModule extends AbstractModule {
 * 	
 * 	const PERMISSION_CREATE = 1;
 * 	
 * 	public function routes() {
 * 		BandController::routes()
 * 				->get('bands', 'store')
 * 				->get('bands/0','new')
 * 				->get('bands/:bandId','read')
 * 				->put('bands/:bandId', 'update')
 * 				->post('bands', 'create')
 * 				->delete('bands/:bandId','delete');
 * 		
 * 		HelloController::routes()
 * 				->get('bands/hello', 'name');
 * 	}
 * }
 * </code>
 * 
 * 
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class AbstractModule extends AbstractObject {

	/**
	 * Returns the routing table for the module
	 * 
	 * See the {@see Http\Router} documentation for more info .
	 * 
	 * @return array[]
	 */
	public function routes() {
	}

	/**
	 * Controls if the module is installed by default
	 * 
	 * @return boolean
	 */
	public function autoInstall() {
		return true;
	}

	/**
	 * Installs the module
	 * 
	 * @return Module
	 */
	public function install() {
		$module = new Module();
		$module->name = $this->className();
		return $module->save();
	}

	/**
	 * Get the filesystem path of this module
	 *  
	 * @return string
	 */
	public function path() {
		$r = new ReflectionClass($this);

		return dirname($r->getFileName());
	}

	/**
	 * Get update files.
	 * Can be SQL or PHP scripts.
	 * 
	 * @return File[] The array has the filename date stamp as key so it can be sorted
	 */
	public function databaseUpdates() {

		$folder = new Folder($this->path());
		$dbFolder = $folder->createFolder('Install/Database');
		if (!$dbFolder->exists()) {
			return [];
		} else {

			$files = $dbFolder->getChildren(true, false);

			usort($files, function($file1, $file2) {
				return $file1->getName() > $file2->getName();
			});


			$module = $this->model();

			if ($module) {
				$files = array_slice($files, $module->version);
			}


			$regex = '/[0-9]{8}-[0-9]{4}\.(sql|php)/';
			foreach ($files as $file) {
				if (!preg_match($regex, $file->getName())) {
					throw new Exception("The upgrade file '" . $file->getName() . "' is not in the right filename format. It should be YYYYMMDD-HHMM.sql or YYYYMMDD-HHMM.php");
				}
			}

			return $files;
		}
	}

	/**
	 * Return module dependencies
	 * 
	 * Return an array of full classnames. eg.
	 * 
	 * ['GO\Modules\Contacts\ContactsModule']
	 * 
	 * 
	 * @return string[]
	 */
	public function depends() {
		return [];
	}

	/**
	 * Get the module model
	 * 
	 * @return Module
	 */
	public static function model() {
		return Module::find(['name' => static::className()])->single();
	}

	/**
	 * Get's all modules that depend on eachother including this module class
	 * 
	 * @return string[]
	 */
	public function getRecursiveDependencies() {
		$depends = $this->depends();


		$all = $depends;

		foreach ($depends as $depend) {
			$manager = new $depend;

			$all = array_merge($all, $manager->getRecursiveDependencies());
		}

		if (!in_array($this->className(), $all)) {
			$all[] = $this->className();
		}

		return array_unique($all);
	}
	
	/**
	 * Get the configuration object for this module.
	 * 
	 * @return ModuleConfig
	 */
	public static function config(){
		return new ModuleConfig(self::className());
	}
}
