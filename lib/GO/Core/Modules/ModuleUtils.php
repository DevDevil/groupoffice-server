<?php
namespace GO\Core\Modules;

use GO\Core\AbstractModule;
use GO\Core\App;
use GO\Core\Fs\Folder;


class ModuleUtils{
	/**
	 * 
	 * @return Folder[] index is namespace prefix.
	 */
	public static function getModuleFolders(){
		
		$modulesFolders = array();
		
		//array(2) { 
		// ["GO\"]=> array(1) { 
		//    [0]=> string(99) "/var/www/pocket-office/intermesh-php-example/vendor/intermesh/intermesh-php-framework/lib/GO" } 
		// ["IPE\"]=> array(1) { 
		//    [0]=> string(52) "/var/www/pocket-office/intermesh-php-example/lib/IPE" } 
		//  } 
		$prefixes = App::config()->classLoader->getPrefixesPsr4();
		
		foreach($prefixes as $prefix => $paths){
				
			$modulesFolder = new Folder($paths[0].'/Modules');	
			if($modulesFolder->exists()) {
				foreach($modulesFolder->getChildren() as $folder){
					if($folder->isFolder()){
						$modulesFolders[$prefix.'Modules\\'.$folder->getName().'\\']=$folder;
					}
				}		
			}
		}
		
		return $modulesFolders;
	}
	
	/**
	 * Get's all module class files
	 * 
	 * @return AbstractModule[];
	 */
	public static function getModules(){
		$folders = self::getModuleFolders();
		
		$managers = [];
		foreach($folders as $prefix => $folder){
			$moduleManagerClass = $prefix.$folder->getName().'Module';
			
			if(class_exists($moduleManagerClass)){
				$managers[] = new $moduleManagerClass;
				
			}
		}
		
		return $managers;
	}
	
	
	/**
	 * Get model classes of all modules.
	 * 
	 * @return string[] eg. ["GO\Core\Auth\Model\User", "etc.."]
	 */
	public static function getModelNames(){
		
		$modelNames = [];
		$folders = ModuleUtils::getModuleFolders();
		
		foreach($folders as $prefix => $folder){
			$modelFolder = $folder->createFolder('Model');
			if($modelFolder->exists()){
				$files = $modelFolder->getChildren();
				
				foreach($files as $file){
					$className = $prefix.'Model\\'.$file->getNameWithoutExtension();
					
					if(class_exists($className)){
						$modelNames[] = $className;
					}
				}						
			}
		}
		
		return $modelNames;
	}
}