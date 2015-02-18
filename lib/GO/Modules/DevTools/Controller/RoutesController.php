<?php

namespace GO\Modules\DevTools\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;

class RoutesController extends AbstractController {

	public function actionMarkdown() {
		//{@link http://intermesh.nl description}
		
		$this->setContentType('text/plain');
		
		echo " * | Method | Route | Controller     |\n";
		echo " * |--------|-------|----------------|\n";
 	
		foreach(App::router()->getRouteCollections() as $routeCollection){
			$controller = $routeCollection->getController();
			
			$routes = $routeCollection->getRoutes();
			
			foreach($routes as $route){
				$method = $route[0];
				$path = $route[1];
				$action = "action".$route[2];				
				
				echo ' * |'.$method.' | '.$path.' | {@link '.$controller."::".$this->findMethodCaseInsensitive($controller, $action)."}|\n";
			}
		}
	}
	
	private function findMethodCaseInsensitive($class, $method) {
		$reflection = new \ReflectionClass($class);		
		$method = $reflection->getMethod($method);		
		return $method->getName();
	}
	/**
	 * Get all routes
	 * 
	 * 
	 * @param array $routes
	 * @param string $prefix
	 * @return string[]
	 */
	private function getRoutesAsString($routes=null, $prefix = ''){
		
		$routeStr = [];
		
		if(!isset($routes)){
			$routes = App::router()->getRoutes();
		}
		
		foreach($routes as $route => $config){
			
				
			$str = $prefix.'/'.$route;
			if(isset($config['controller'])){
				$routeStr[$str] = $config;
			}

			if(isset($config['routeParams'])){
				foreach($config['routeParams'] as $p){
					$str .= '/['.$p.']';

					if(isset($config['controller'])){
						$routeStr[$str] = $config;
					}
				}
			}

			
			if(isset($config['children'])){
				$routeStr = array_merge($routeStr, $this->getRoutesAsString($config['children'], $str));
			}
		}
		
		return $routeStr;
		
	}
}