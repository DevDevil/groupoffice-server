<?php

namespace Intermesh\Core;

use Exception;
use Intermesh\Core\Fs\File;
use Intermesh\Core\Fs\Folder;
use Intermesh\Modules\Modules\Model\Module;
use Intermesh\Modules\Modules\ModulesModule;
use ReflectionClass;

abstract class AbstractModule extends AbstractObject {

	public static function getRoutes() {
		return [];
	}
	
	/**
	 * Controls if the module is installed by default
	 * 
	 * @return boolean
	 */
	public function autoInstall(){
		return true;
	}
	
	/**
	 * Installs the module
	 * 
	 * @return Module
	 */
	public function install(){
		$module = new Module();
		$module->name = $this->className();
		return $module->save();
	}
	
	/**
	 * Get the filesystem path of this module
	 *  
	 * @return string
	 */
	public function path(){
		$r = new ReflectionClass($this);
		
		return dirname($r->getFileName());
	}
	
	
	/**
	 * Get update files.
	 * Can be SQL or PHP scripts.
	 * 
	 * @return File[] The array has the filename date stamp as key so it can be sorted
	 */
	public function databaseUpdates(){
		
		$folder = new Folder($this->path());		
		$dbFolder = $folder->createFolder('Install/Database');		
		if(!$dbFolder->exists()){
			return [];
		}else
		{
			
			$files = $dbFolder->getChildren(true, false);
			
			usort($files, function($file1, $file2){
				return $file1->getName() > $file2->getName();
			});
			
			
			$module = $this->model();
			
			if($module){				
				$files = array_slice($files, $module->version);				
			}
			
			
			$regex = '/[0-9]{8}-[0-9]{4}\.(sql|php)/';
			foreach($files as $file){				
				if(!preg_match($regex, $file->getName())){
					throw new Exception("The upgrade file '".$file->getName()."' is not in the right filename format. It should be YYYYMMDD-HHMM.sql or YYYYMMDD-HHMM.php");
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
	 * ['Intermesh\Modules\Contacts\ContactsModule']
	 * 
	 * 
	 * @return string[]
	 */
	public function depends(){
		return [];
	}
	
	
	/**
	 * Get the module model
	 * 
	 * @return Module
	 */
	public static function model(){
		return Module::find(['name' => static::className()])->single();
	}

	
	/**
	 * Get's all modules that depend on eachother including this module class
	 * 
	 * @return string[]
	 */
	public function getRecursiveDependencies(){
		$depends = $this->depends();
		
		
		$all = $depends;
		
		foreach($depends as $depend){
			$manager = new $depend;
			
			$all = array_merge($all, $manager->getRecursiveDependencies());			
		}
		
		if(!in_array($this->className(), $all)){
			$all[] = $this->className();
		}
		
		return array_unique($all);
	}
	
	
	


}
