<?php
namespace Intermesh\Core;

use Intermesh\Core\App;

/**
 * Base object class of all objects.
 * 
 * It implements setters and getters and properties can be set with the config object.
 * 
 * @see \Intermesh\Core\Config
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class AbstractObject { 
	
	public function __construct() {		
		$this->_applyConfig();	
	}
	
	/**
	 * Create a new instance of this object
	 * 
	 * @return \static 
	 */
	public static function newInstance(){
		return new static;
	}
	/**
	 * Applies config options to this object
	 */
	private function _applyConfig(){
		$className = get_class($this);

		
		if(isset(App::config()->classConfig[$className])){			
			foreach(App::config()->classConfig[$className] as $key=>$value){
				$this->$key=$value;
			}
		}		
	}
	
	/**
	 * Returns the name of this class.
	 * @return string the name of this class.
	 */
	public static function className() {
		return get_called_class();
	}
	
	/**
	 * Get the name of the module this object belongs too. This is the 2rd
	 * part of the namespace;
	 * 
	 * @return string
	 */
	public static function moduleName(){
		$parts = explode("\\", self::className());
		
		return $parts[2];		
	}
	
	/**
	 * Magic getter that calls get<NAME> functions in objects
	 
	 * @param string $name property name
	 * @return mixed property value
	 * @throws Exception If the property setter does not exist
	 */
	public function __get($name)
	{			
		$getter = 'get'.$name;

		if(method_exists($this,$getter)){
			return $this->$getter();
		}else
		{
			throw new \Exception("Can't get not existing property '$name' in '".$this->className()."'");			
		}
	}		
	
	/**
	 * Magic function that checks the get<NAME> functions
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) {
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			// property is not null
			return $this->$getter() !== null;
		} else {
			return false;
		}
	}
	

	/**
	 * Magic setter that calls set<NAME> functions in objects
	 * 
	 * @param string $name property name
	 * @param mixed $value property value
	 * @throws Exception If the property getter does not exist
	 */
	public function __set($name,$value)
	{
		$setter = 'set'.$name;
			
		if(method_exists($this,$setter)){
			$this->$setter($value);
		}else
		{				
			
			$getter = 'get' . $name;
			if(method_exists($this, $getter)){
				$errorMsg = "Can't set read only property '$name' in '".$this->className()."'";
			}else {
				$errorMsg = "Can't set not existing property '$name' in '".$this->className()."'";
			}	

			throw new \Exception($errorMsg);			
		}
	}	
}