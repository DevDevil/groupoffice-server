<?php

namespace GO\Core\Http;

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
	
	public function get($route, $action){
		return $this->_addRoute('GET', $route, $action);
	}
	
	public function post($route, $action){
		return $this->_addRoute('POST', $route, $action);
	}
	
	public function put($route, $action){
		return $this->_addRoute('PUT', $route, $action);
	}
	
	public function delete($route, $action){
		return $this->_addRoute('DELETE', $route, $action);
	}
	
	public function getRoutes(){
		return $this->_routes;
	}
	
	public function getController(){
		return $this->_controllerName;
	}
}