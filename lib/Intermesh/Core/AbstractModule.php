<?php

use Intermesh\Core\AbstractObject;

namespace Intermesh\Core;

abstract class AbstractModule extends AbstractObject {

	public static function getRoutes() {
		return [];
	}
	
	/**
	 * 
	 * @param string $moduleName
	 * @return static|boolean
	 */
	public static function findByName($moduleName){		
		$moduleName = ucfirst($moduleName);
		$className = "\\Intermesh\\Modules\\" . $moduleName . "\\" . $moduleName . "Module";
		
		if (!class_exists($className)) {
			return false;
		} else {
			return new $className;
		}
	}
	
	public function path(){
		$r = new \ReflectionClass($this);
		
		return dirname($r->getFileName());
	}
	
	
	/**
	 * Get update files.
	 * Can be SQL or PHP scripts.
	 * 
	 * @return Fs\File[] The array has the filename date stamp as key so it can be sorted
	 */
	public function databaseUpdates(){
		
		$folder = new Fs\Folder($this->path());		
		$dbFolder = $folder->createFolder('Install/Database');		
		if(!$dbFolder->exists()){
			return [];
		}else
		{
			
			$files = $dbFolder->getChildren(true, false);
			
			//echo $this->moduleName();
			
			$module = \Intermesh\Modules\Modules\Model\Module::find(['name' => $this->className()])->single();
			
			if($module){
				$files = array_slice($files, $module->version);
			}
			
			
			$regex = '/[0-9]{8}-[0-9]{4}\.(sql|php)/';
			foreach($files as $file){				
				if(!preg_match($regex, $file->getName())){
					throw new \Exception("The upgrade file '".$file->getName()."' is not in the right filename format. It should be YYYYMMDD-HHMM.sql or YYYYMMDD-HHMM.php");
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
