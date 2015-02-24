<?php
namespace GO\Core\Event;

trait ObservableTrait {
	
	protected static $_listeners = [];
	
	
	/**
	 * Add an event listener
	 * 
	 * @param int $event Defined in constants prefixed by EVENT_
	 * @param callable $fn 
	 * @return int $index Can be used for removing the listener.
	 */
	public static function on($event, $fn){
		
		if(!isset(static::$_listeners[get_called_class()][$event])){
			static::$_listeners[get_called_class()][$event] = [];
		}
		static::$_listeners[get_called_class()][$event][] = $fn;
		
		return count(static::$_listeners[get_called_class()][$event])-1;
	}
	
	/**
	 * Remove a listener
	 * 
	 * @param int $event
	 * @param int $index
	 */
	public static function off($event, $index) {
		unset(static::$_listeners[get_called_class()][$event][$index]);
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
		
		if(!isset(static::$_listeners[get_called_class()][$event])){
			return true;
		}
		
		$args = func_get_args();
		
		//shift $event
		array_shift($args);
		
		foreach(static::$_listeners[get_called_class()][$event] as $listener) {
			$return = call_user_func_array($listener, $args);
			
			if($return === false){
				return false;
			}
		}
		
		return true;
	}	
}