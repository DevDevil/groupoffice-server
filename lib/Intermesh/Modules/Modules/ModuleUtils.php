<?php
namespace Intermesh\Modules\Modules;

use Intermesh\Core\App;
use Intermesh\Core\Fs\Folder;


class ModuleUtils{
	/**
	 * 
	 * @return Folder[] index is namespace prefix.
	 */
	public static function getModuleFolders(){
		
		$modulesFolders = array();
		
		//array(2) { 
		// ["Intermesh\"]=> array(1) { 
		//    [0]=> string(99) "/var/www/pocket-office/intermesh-php-example/vendor/intermesh/intermesh-php-framework/lib/Intermesh" } 
		// ["IPE\"]=> array(1) { 
		//    [0]=> string(52) "/var/www/pocket-office/intermesh-php-example/lib/IPE" } 
		//  } 
		$prefixes = App::config()->classLoader->getPrefixesPsr4();
		
		foreach($prefixes as $prefix => $paths){
				
			$modulesFolder = new Folder($paths[0].'/Modules');	
			foreach($modulesFolder->getChildren() as $folder){
				if($folder->isFolder()){
					$modulesFolders[$prefix.'Modules\\'.$folder->getName().'\\']=$folder;
				}
			}		
		}
		
		return $modulesFolders;
	}
	
	
	/**
	 * Get model classes of all modules.
	 * 
	 * @return string[]
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