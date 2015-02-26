<?php
namespace GO\Core\Util;

use GO\Core\App;
use GO\Core\Fs\Folder;

class ClassFinder{
	
	private $_className;
	
	private $_results;
	
	public function __construct($className) {
		$this->_className = $className;
	}
	
	public function find(){
		
		//array(2) { 
		// ["GO\"]=> array(1) { 
		//    [0]=> string(99) "/var/www/pocket-office/intermesh-php-example/vendor/intermesh/intermesh-php-framework/lib/GO" } 
		// ["IPE\"]=> array(1) { 
		//    [0]=> string(52) "/var/www/pocket-office/intermesh-php-example/lib/IPE" } 
		//  } 
		$prefixes = App::config()->classLoader->getPrefixesPsr4();
		
		$this->_results = [];
		foreach($prefixes as $namespace => $paths){
			$folder = new Folder($paths[0]);
			$this->getModelsFromFolder($folder, $namespace);
		}
		
		return $this->_results;
	}
	
	private function getModelsFromFolder(Folder $folder, $namespace) {
		
		
		foreach($folder->getChildren() as $child) {
			if($child->isFile()){
				$className = $namespace.$child->getNameWithoutExtension();
				
				if(is_subclass_of($className, $this->_className)) {
					$this->_results[] = $className;
				}
			}else
			{
				$this->getModelsFromFolder($child, $namespace.$child->getName().'\\');
			}
		}
	}
}