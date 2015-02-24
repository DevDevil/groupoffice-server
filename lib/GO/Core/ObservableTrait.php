<?php
namespace GO\Core;

trait ObservableTrait {
	
	private static $_listeners = [];
	
	
	/**
	 * Add an event listener
	 * 
	 * @param int $event Defined in constants prefixed by EVENT_
	 * @param callable $fn 
	 */
	public static function on($event, $fn){
		
		if(!isset(static::$_listeners[$event])){
			static::$_listeners[$event] = [];
		}
		
		static::$_listeners[$event][] = $fn;
	}
	
	/**
	 * Fire an event
	 * 
	 * 
	 * @param int $event Defined in constants prefixed by EVENT_
	 * @param mixed Extra paramters will be passed on to the listener
	 * @return boolean
	 */
	public static function fireEvent($event){
		if(!isset(static::$_listeners[$event])){
			return true;
		}
		
		$args = func_get_args();
		
		//shift $event
		array_shift($args);
		
		foreach(static::$_listeners[$event] as $listener) {
			$return = call_user_func_array($listener, $args);
			
			if($return === false){
				return false;
			}
		}
		
		return true;
	}	
}