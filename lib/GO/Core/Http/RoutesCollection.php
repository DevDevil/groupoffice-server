<?php

namespace GO\Core\Http;

/**
 * Holds route definitions for a Controller.
 * 
 * @see Router
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class RoutesCollection {
	
	private $_controllerName;
	
	private $_routes = [];
	
	public function __construct($controllerName) {
		$this->_controllerName = $controllerName;
	}
	
	private function _addRoute($method, $route, $action) {
		$this->_routes[] = [$method, $route, $action];
		
		return $this;
	}
	
	/**
	 * Add a route that handles a GET request
	 * 
	 * Example:
	 * 
	 * <code>
	 * AuthController::routes()
	 *			->get('auth/users', 'store');
	 * </code>
	 * 
	 * @param string $route eg. auth/users
	 * @param string $action The controller action without the "action" prefix. eg. "store" leads to method actionStore. The actions are case-insensitive.
	 * @return self
	 */
	public function get($route, $action){
		return $this->_addRoute('GET', $route, $action);
	}
	
	/**
	 * Add a route that handles a POST request
	 * 
	 * Example:
	 * 
	 * <code>
	 * AuthController::routes()
	 *			->get('auth/users', 'create');
	 * </code>
	 * 
	 * @param string $route eg. auth/users
	 * @param string $action The controller action without the "action" prefix. eg. "store" leads to method actionStore. The actions are case-insensitive.
	 * @return self
	 */
	public function post($route, $action){
		return $this->_addRoute('POST', $route, $action);
	}
	
	/**
	 * Add a route that handles a POST request
	 * 
	 * Example:
	 * 
	 * <code>
	 * AuthController::routes()
	 *			->get('auth/users/:userId', 'update');
	 * </code>
	 * 
	 * @param string $route eg. auth/users
	 * @param string $action The controller action without the "action" prefix. eg. "store" leads to method actionStore. The actions are case-insensitive.
	 * @return self
	 */
	public function put($route, $action){
		return $this->_addRoute('PUT', $route, $action);
	}
	
	/**
	 * Add a route that handles a POST request
	 * 
	 * Example:
	 * 
	 * <code>
	 * AuthController::routes()
	 *			->delete('auth/users/:userId', 'delete');
	 * </code>
	 * 
	 * @param string $route eg. auth/users
	 * @param string $action The controller action without the "action" prefix. eg. "store" leads to method actionStore. The actions are case-insensitive.
	 * @return self
	 */
	public function delete($route, $action){
		return $this->_addRoute('DELETE', $route, $action);
	}
	
	/**
	 * Get the route definitions
	 * 
	 * @return array
	 */
	public function getRoutes(){
		return $this->_routes;
	}
	
	/**
	 * Get the controller name the routes are for.
	 * 
	 * @return string
	 */
	public function getController(){
		return $this->_controllerName;
	}
}