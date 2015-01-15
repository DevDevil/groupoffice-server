<?php
namespace GO\Core;

use GO\Core\App;

/**
 * Observable class. Listeners can be added to objects that extend this class.
 * 
 * @todo Persistant listeners not working yet
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Observable{
	
	private static $_listeners;
	
	
	/**
	 * 
	 * @return Fs\Folder;
	 */
	private static function getListenersTempFolder(){
		$cacheFolder = App::config()->getTempFolder();
		$folder = $cacheFolder->createFolder('listeners');
		
		if(App::debugger()->enabled){
			$folder->delete();			
		}
			
		return $folder;
	}
	
	/**
	 * Will check if the event listeners have been cached and will 
	 * cache them when necessary.
	 * 
	 * At the moment this function is called in index.php In the future this
	 * should be called at the new entry point of the application.
	 */
	public static function cacheListeners(){	
		
		self::getListenersTempFolder();
		
		
		//TODO add listeners from all APPS
		
		
	}
	/**
	 * Add a listener function to this object
	 * 
	 * @param string $eventName
	 * @param string $function Function to call
	 * @param array $params If supplied these will override the default event parameters
	 * @param boolean $persistant If set to true this listener will be saved forever. Otherwise it will fire just during a single request.
	 */
	public function addListener($eventName,$function,$params=null, $persistant=true){
		
		$listener = array('params'=>$params, 'function'=>$function);
		
		if($persistant){
			$line = '$listeners["'.$eventName.'"][]='.var_export($listener, true).';'."\n";		

			$folder=$this->getListenersTempFolder();
			if(!$folder->exists()){
				$folder->create();
			}
			
			$file = $folder->createFile(get_class($this).'.php');			

			if(!$file->exists()){
				$file->putContents("<?php\n");
			}

			$file->putContents($line, FILE_APPEND);	
		}  else {
			$className = get_class($this);
			$this->_initListeners($className);
			
			if(!isset(self::$_listeners[$className][$eventName])){
				self::$_listeners[$className][$eventName]=array();
			}
			
			self::$_listeners[$className][$eventName][]=$listener;			
		}
	}	
	
	private function _initListeners($className){		
		if(!isset(self::$_listeners[$className])){
			
			//listeners array will be loaded from a file. Because addListener is only called once when there is no cache.
			$listeners=array();
			
			$cacheFile = $this->getListenersTempFolder()->createFile($className.'.php');		
			if($cacheFile->exists()){
				require($cacheFile->getPath());
			}
			
			self::$_listeners[$className]=$listeners;			
		}
	}
	
	/**
	 * Fire an event so that listener functions will be called.
	 * 
	 * @param String $eventName Name fo the event
	 * @param Array $params Paramters for the listener function
	 * 
	 * @return boolean If one listerner returned false it will stop execution of 
	 *  other listeners and will return false.
	 */
	public function fireEvent($eventName, $params=array()){
		
		$className = get_class($this);	
//		do{
		$this->_initListeners($className);		
		
		if(isset(self::$_listeners[$className][$eventName])){
			foreach(self::$_listeners[$className][$eventName] as $listener)
			{
//				App::debug('Firing listener for class '.$className.' event '.$eventName.': '.$listener[0].'::'.$listener[1]);
				
				if(isset($listener['params'])){
					$params=$listener['params'];
				}

				$return = call_user_func_array($listener['function'], $params);
				if($return===false){
					App::debug("Event '$eventName' cancelled by ".$listener[0].'::'.$listener[1]);
					return false;
				}
			}
		}
//		}
//		while($className = get_parent_class($className));
		return true;
	}
}