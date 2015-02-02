<?php
namespace GO\Core\Cache;

use GO\Core\App;
use GO\Core\Cache\CacheInterface;
use GO\Core\Fs\File;

/**
 * Cache implementation that uses serialized objects in files on disk.
 * The cache is persistent accross requests.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Disk implements CacheInterface{
	
	private $_ttls;
	private $_ttlFile;
	private $_ttlsDirty=false;
	private $_folder;
	
	private $_time;
	
	public function __construct(){
		
		$this->_folder = App::config()->getTempFolder()->createFolder('diskcache');
		
		$this->_folder->create();		
		
		$this->_ttlFile = $this->_folder->createFile('ttls.txt');
		
		$this->_load();
		
		$this->_time=time();
	}
	
	private function _load(){
		if(!isset($this->_ttls)){
			
			if($this->_ttlFile->exists()){
				$data = $this->_ttlFile->getContents();
				$this->_ttls = unserialize($data);
			}else
			{
				$this->_ttls = array();
			}
		}
	}
	/**
	 * Store any value in the cache
	 * @param string $key
	 * @param mixed $value Will be serialized
	 * @param int $ttl Seconds to live
	 */
	public function set($key, $value, $ttl=0){
		
		//don't set false values because unserialize returns false on failure.
		if($key===false)
			return true;

		$key = File::stripInvalidChars($key,'-');
						
		if($ttl){
			$this->_ttls[$key]=$this->_time+$ttl;
			$this->_ttlsDirty=true;
		}
		
		$file = $this->_folder->createFile($key);
		
		$success = $file->putContents(serialize($value));

		return $success;
	}
	
	/**
	 * Get a value from the cache
	 * 
	 * @param string $key
	 * @return boolean 
	 */
	public function get($key){
		
		$key = File::stripInvalidChars($key, '-');
		
		if(!empty($this->_ttls[$key]) && $this->_ttls[$key]<$this->_time){
			unlink($this->_folder.$key);
			return false;
		}elseif(!file_exists($this->_folder.$key))
		{
			return false;
		}else
		{
			$data = file_get_contents($this->_folder.$key);
			$unserialized = unserialize($data);
			
			if($unserialized===false){
				trigger_error("Could not unserialize key data from file ".$this->_folder.$key);
				return false;
			}else
			{			
				return $unserialized;
			}
		}
	}
	
	/**
	 * Delete a value from the cache
	 * 
	 * @param string $key 
	 */
	public function delete($key){
		$key = File::stripInvalidChars($key, '-');
		
		unset($this->_ttls[$key]);
		$this->_ttlsDirty=true;
		@unlink($this->_folder.$key);
	}
	/**
	 * Flush all values 
	 */
	public function flush(){
		$this->_ttls=array();
		$this->_ttlsDirty=true;
		
		$this->_folder->delete();
		$this->_folder->create(0777);
	}
	
	public function __destruct(){
		if($this->_ttlsDirty)
			$this->_ttlFile->putContents (serialize($this->_ttls));
	}
	
	public function supported() {
		return true;
	}
}