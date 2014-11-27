<?php

namespace Intermesh\Core\Http;

use Exception;
use Intermesh\Core\App;

/**
 * The router routes requests to their controller actions
 *
 * Each module can define it's own routes. The first part of the route must always
 * match the module name. So the route "auth/users/1" will look in the contacts module.
 * 
 * This will find the class "Intermesh\Modules\Auth\AuthModule".
 * The routes for that module are defined  in that file:
 * 
 * <code>
 * class AuthModule extends AbstractModule{
	public static function getRoutes(){
		return [
					'auth' => [
						'controller' => AuthController::className(),
						'children' => [
							'users' => [
								'routeParams' => ['userId'],
								'controller' => UserController::className(),
								'children' => [
									'roles' =>[
										'controller' => UserRolesController::className()
									]
								]
							],
							'roles' => [
								'routeParams' => ['roleId'],
								'controller' => RoleController::className(),
								'children' => [
									'users' =>[
										'controller' => RoleUsersController::className()
									],

								]
							],
							'permissions' => [
								'routerParams' => ['modelId', 'modelName'],
								'controller' => PermissionsController::className()
							]
						]
					]

			];
		}

	}
 * </code>
 * 
 * So in this case our example will find the "users" and the "routeParam" "userId" will be set to "1".
 * 
 * The UserController will handle this request. 
 * 
 * {@see \Intermesh\Core\Controller\AbstractRESTController} and {@see \Intermesh\Core\Controller\AbstractCrudController}
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Router {

	private function getModuleRoutes($module) {

		$className = "\\Intermesh\\Modules\\" . $module . "\\" . $module . "Module";


		if (!class_exists($className)) {
			throw new Exception("Module (" . $className . ") has no routes!");
		} else {
			return $className::getRoutes();
		}
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

			$this->route = isset(App::request()->get['r']) ? App::request()->get['r'] : false;

			$this->_routeParts = explode('/', $this->route);

			$module = ucfirst($this->_routeParts[0]);

			$routes = $this->getModuleRoutes($module);

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
									'controller' => "Intermesh\Contacts\Controller\Contact",
									'actions' => ['thumb', 'original','upload'],
									'args' => ['modelName' => Contact::className(), 'primaryKeyName' => 'contactId'],
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
		$url .= $_SERVER['PHP_SELF'];

		return $url;
	}

	/**
	 * Generate a controller URL.
	 * 
	 * eg. buildUrl('auth/users', ['sortDirection' => 'DESC']); will build:
	 * 
	 * "/index.php?r=auth/users&sortDirection=DESC"
	 *
	 * @param string $route To controller. eg. addressbook/contact/submit
	 * @param array $params eg. ['id' => 1, 'someVar' => 'someValue']
	 * @return string
	 */
	public function buildUrl($route = '', $params = []) {

		$url = $this->getBaseUrl();

		if (!empty($route)) {
			$url .= '?r='.$route;
		}

		if (count($params)) {
			$queryParams = empty($route) ? '?' : '&';

			foreach ($params as $key => $value) {
				
				$queryParams .= $key . '=' . urlencode($value) . '&';
			}

			$url .= rtrim($queryParams, '&');
		}

		return $url;
	}

}
