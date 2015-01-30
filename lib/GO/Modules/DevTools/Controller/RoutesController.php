<?php

namespace GO\Modules\DevTools\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;

class RoutesController extends AbstractController {

	public function httpGet() {
		//{@link http://intermesh.nl description}
		
		$routes = $this->getRoutesAsString();
		
		echo "<pre>";
		
		echo " * | Route | Controller     |\n";
		echo " * |-------|----------------|\n";
 	
		foreach($routes as $routeStr => $config){			
			echo ' * |'.$routeStr.' | {@link '.$config['controller']."}|\n";
		}
		
		echo "</pre>";
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