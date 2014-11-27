<?php
namespace Intermesh\Core\Cache;

/**
 * Key value cache implementation interface. The cache is persistent accross 
 * requests.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
interface CacheInterface{
	/**
	 * Store any value in the cache
	 * @param string $key
	 * @param mixed $value Will be serialized
	 * @param int $ttl Seconds to live
	 */
	public function set($key, $value, $ttl=0);
	
	/**
	 * Get a value from the cache
	 * 
	 * @param string $key
	 * @return boolean 
	 */
	public function get($key);
	
	/**
	 * Delete a value from the cache
	 * 
	 * @param string $key 
	 */
	public function delete($key);
	
	/**
	 * Flush all values 
	 */
	public function flush();
	
	/**
	 * Returns true if this system supports this cache driver
	 * 
	 * @return boolean
	 */
	public function supported();
}