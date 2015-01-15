<?php

namespace Intermesh\Core\Http;

use Exception;
use Intermesh\Core\App;

/**
 * The router routes requests to their controller actions
 *
 * Each module can add routes See {@see \Intermesh\Core\AbstractModule} for information
 * about creating routes
 * 
 * {@see \Intermesh\Core\Controller\AbstractRESTController} and {@see \Intermesh\Core\Controller\AbstractCrudController}
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Router {

	/**
	 * Get routing tables from all modules
	 * 
	 * @todo cache this
	 * 
	 * @return array[]
	 */
	public function getRoutes() {

		$modules = \Intermesh\Modules\Modules\Model\Module::find();
		
		$routes = [];
		
		foreach($modules as $module){
			
			$routes = array_merge($routes, $module->manager()->routes());
		}
		
		return $routes;
	}	
	
	/**
	 * The current route. 
	 * 
	 * eg. /contacts/1
	 * 
	 * @var string 
	 */
	public $route;

	/**
	 * Finds the controller that matches the route and runs it.
	 */
	public function run() {
		
//		try {
		
			if(!isset($_SERVER['PATH_INFO'])){
				App::request()->redirect($this->buildUrl('core/check'));
			}
		
			$this->route = ltrim($_SERVER['PATH_INFO'],'/');

			$this->_routeParts = explode('/', $this->route);			

			$routes = $this->getRoutes();

			$this->walkRoute($routes);
//		} catch (\Exception $e) {
//			
//		}
	}

	/**
	 * Get the params passed in a route.
	 * 
	 * eg. /contacts/1 where 1 is a "contactId" would return ["contactId" => 1];
	 * 
	 * @var array
	 */
	public $routeParams = [];
	
	/**
	 * The configuration of the current route
	 * 
	 * eg.
	 * 
	 * 'contacts' => [
						'routeParams' => ['contactId'], 
						'controller' => "Intermesh\Core\Controller\RESTController",						
						'children' => [
								'thumb' => [
									'routeParams' => ['contactId'],
									'controller' => "Intermesh\Contacts\Controller\Contact"									
								]
							]
						]
	 * 
	 * @var array
	 */
	
	public $routeConfig;
	
	private $_routeParts;

	

	private function walkRoute($routes) {

		$routePart = array_shift($this->_routeParts);

		foreach ($routes as $path => $config) {
			if ($routePart === $path) {
				$this->getParams($config);

				if (!empty($this->_routeParts)) {
					if (!isset($config['children'])) {
						$config['children'] = [];
					}
					return $this->walkRoute($config['children']);
				} else {

					if (!isset($config['controller'])) {
						throw new Exception("No controller defined for this route!");
					}

					if (!isset($config['constructorArgs'])) {
						$config['constructorArgs'] = [];
					}
					
					$this->routeConfig = $config;

					$controller = new $config['controller']($this);
					return $controller->run();
				}
			}
		}

		throw new Exception("Route $routePart not found! " . var_export($routes, true));
	}

	private function getParams($options) {
		if (!empty($options['routeParams'])) {
			foreach ($options['routeParams'] as $paramName) {
				$this->routeParams[$paramName] = array_shift($this->_routeParts);
			}
		}
	}

	private function getBaseUrl() {

		$https = App::request()->isHttps();
		$url = $https ? 'https://'.$_SERVER['HTTP_HOST'] : 'http://'.$_SERVER['HTTP_HOST'];
		if ((!$https && $_SERVER["SERVER_PORT"] != 80) || ($https && $_SERVER["SERVER_PORT"] != 443)){
			$url .= ":".$_SERVER["SERVER_PORT"];
		}
		$url .= $_SERVER['SCRIPT_NAME'].'/';

		return $url;
	}

	/**
	 * Generate a controller URL.
	 * 
	 * eg. buildUrl('auth/users', ['sortDirection' => 'DESC']); will build:
	 * 
	 * "/(path/to/index.php|aliastoindexphp)/auth/users&sortDirection=DESC"
	 *
	 * @param string $route To controller. eg. addressbook/contact/submit
	 * @param array $params eg. ['id' => 1, 'someVar' => 'someValue']
	 * @return string
	 */
	public function buildUrl($route = '', $params = []) {

		$url = $this->getBaseUrl();

		if (!empty($route)) {
			$url .= $route;
		}

		if (!empty($params)) {
			
			$sep = '?';

			foreach ($params as $key => $value) {				
				$url .= $sep.$key . '=' . urlencode($value);				
				$sep ='&';
			}
		}

		return $url;
	}

}
