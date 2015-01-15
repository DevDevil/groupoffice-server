<?php

namespace Intermesh\Modules\DevTools\Controller;

use Intermesh\Core\Controller\AbstractRESTController;

class ModelController extends AbstractRESTController {

	public function httpGet() {
		
	}
	/**
	 * Get all routes
	 * 
	 * 
	 * @param array $routes
	 * @param string $prefix
	 * @return string[]
	 */
	public function getRoutesAsString($routes=null, $prefix = ''){
		
		$routeStr = [];
		
		if(!isset($routes)){
			$routes = $this->getRoutes();
		}
		
		foreach($routes as $route => $config){
			
				
			$str = $prefix.'/'.$route;
			if(isset($config['controller'])){
				$routeStr[] .= $str;
			}

			if(isset($config['routeParams'])){
				foreach($config['routeParams'] as $p){
					$str .= '/{'.$p.'}';

					if(isset($config['controller'])){
						$routeStr[] .= $str;
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