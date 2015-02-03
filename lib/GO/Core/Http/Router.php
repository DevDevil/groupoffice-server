<?php

namespace GO\Core\Http;

use GO\Core\App;
use GO\Core\Auth\Controller\AuthController;
use GO\Core\Auth\Controller\RoleController;
use GO\Core\Auth\Controller\UserController;
use GO\Core\Exception\HttpException;
use GO\Core\Install\Controller\SystemController;
use GO\Core\Install\Model\System;
use GO\Core\Modules\Controller\ModuleController;
use GO\Core\Modules\Model\Module;

/**
 * The router routes requests to their controller actions
 *
 * Each module can add routes See {@see \GO\Core\AbstractModule} for information
 * about creating routes
 * 
 * {@see \GO\Core\Controller\AbstractController}
 * 
 * Available routes:
 * 
 * | Method | Route | Controller     |
 * |--------|-------|----------------|
 * |GET | auth | {@link GO\Core\Auth\Controller\AuthController::actionisLoggedIn}|
 * |POST | auth | {@link GO\Core\Auth\Controller\AuthController::actionlogin}|
 * |DELETE | auth | {@link GO\Core\Auth\Controller\AuthController::actionlogout}|
 * |GET | auth/users | {@link GO\Core\Auth\Controller\UserController::actionstore}|
 * |GET | auth/users/0 | {@link GO\Core\Auth\Controller\UserController::actionnew}|
 * |GET | auth/users/:userId | {@link GO\Core\Auth\Controller\UserController::actionread}|
 * |PUT | auth/users/:userId | {@link GO\Core\Auth\Controller\UserController::actionupdate}|
 * |POST | auth/users | {@link GO\Core\Auth\Controller\UserController::actioncreate}|
 * |DELETE | auth/users | {@link GO\Core\Auth\Controller\UserController::actiondelete}|
 * |GET | auth/roles | {@link GO\Core\Auth\Controller\RoleController::actionstore}|
 * |GET | auth/roles/0 | {@link GO\Core\Auth\Controller\RoleController::actionnew}|
 * |GET | auth/roles/:roleId | {@link GO\Core\Auth\Controller\RoleController::actionread}|
 * |PUT | auth/roles/:roleId | {@link GO\Core\Auth\Controller\RoleController::actionupdate}|
 * |POST | auth/roles | {@link GO\Core\Auth\Controller\RoleController::actioncreate}|
 * |DELETE | auth/roles | {@link GO\Core\Auth\Controller\RoleController::actiondelete}|
 * |GET | system/install | {@link GO\Core\Install\Controller\SystemController::actioninstall}|
 * |GET | system/upgrade | {@link GO\Core\Install\Controller\SystemController::actionupgrade}|
 * |GET | system/check | {@link GO\Core\Install\Controller\SystemController::actioncheck}|
 * |GET | modules | {@link GO\Core\Modules\Controller\ModuleController::actionstore}|
 * |GET | announcements | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionstore}|
 * |GET | announcements/0 | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionnew}|
 * |GET | announcements/:announcementId | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionread}|
 * |PUT | announcements/:announcementId | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionupdate}|
 * |POST | announcements | {@link GO\Modules\Announcements\Controller\AnnouncementController::actioncreate}|
 * |DELETE | announcements/:announcementId | {@link GO\Modules\Announcements\Controller\AnnouncementController::actiondelete}|
 * |GET | announcements/:announcementId/thumb | {@link GO\Modules\Announcements\Controller\ThumbController::actiondownload}|
 * |GET | bands | {@link GO\Modules\Bands\Controller\BandController::actionstore}|
 * |GET | bands/0 | {@link GO\Modules\Bands\Controller\BandController::actionnew}|
 * |GET | bands/:bandId | {@link GO\Modules\Bands\Controller\BandController::actionread}|
 * |PUT | bands/:bandId | {@link GO\Modules\Bands\Controller\BandController::actionupdate}|
 * |POST | bands | {@link GO\Modules\Bands\Controller\BandController::actioncreate}|
 * |DELETE | bands/:bandId | {@link GO\Modules\Bands\Controller\BandController::actiondelete}|
 * |GET | hello | {@link GO\Modules\Bands\Controller\HelloController::actionname}|
 * |GET | contacts | {@link GO\Modules\Contacts\Controller\ContactController::actionstore}|
 * |GET | contacts/0 | {@link GO\Modules\Contacts\Controller\ContactController::actionnew}|
 * |GET | contacts/:contactId | {@link GO\Modules\Contacts\Controller\ContactController::actionread}|
 * |PUT | contacts/:contactId | {@link GO\Modules\Contacts\Controller\ContactController::actionupdate}|
 * |POST | contacts | {@link GO\Modules\Contacts\Controller\ContactController::actioncreate}|
 * |DELETE | contacts/:contactId | {@link GO\Modules\Contacts\Controller\ContactController::actiondelete}|
 * |GET | customfields/models | {@link GO\Modules\CustomFields\Controller\ModelController::actionget}|
 * |GET | customfields/fieldsets | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionstore}|
 * |GET | customfields/fieldsets/0 | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionnew}|
 * |GET | customfields/fieldsets/:fieldSetId | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionread}|
 * |PUT | customfields/fieldsets/:fieldSetId | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionupdate}|
 * |POST | customfields/fieldsets | {@link GO\Modules\CustomFields\Controller\FieldSetController::actioncreate}|
 * |DELETE | customfields/fieldsets/:fieldSetId | {@link GO\Modules\CustomFields\Controller\FieldSetController::actiondelete}|
 * |GET | customfields/:fieldSetId/fields | {@link GO\Modules\CustomFields\Controller\FieldController::actionstore}|
 * |GET | customfields/:fieldSetId/fields/0 | {@link GO\Modules\CustomFields\Controller\FieldController::actionnew}|
 * |GET | customfields/:fieldSetId/fields/:fieldId | {@link GO\Modules\CustomFields\Controller\FieldController::actionread}|
 * |PUT | customfields/:fieldSetId/fields/:fieldId | {@link GO\Modules\CustomFields\Controller\FieldController::actionupdate}|
 * |POST | customfields/:fieldSetId/fields | {@link GO\Modules\CustomFields\Controller\FieldController::actioncreate}|
 * |DELETE | customfields/:fieldSetId/fields/:fieldId | {@link GO\Modules\CustomFields\Controller\FieldController::actiondelete}|
 * |GET | devtools/models | {@link GO\Modules\DevTools\Controller\ModelController::actionlist}|
 * |GET | devtools/models/:modelName/props | {@link GO\Modules\DevTools\Controller\ModelController::actionprops}|
 * |GET | devtools/routes | {@link GO\Modules\DevTools\Controller\RoutesController::actionmarkdown}|
 * |GET | email/accounts | {@link GO\Modules\Email\Controller\AccountController::actionstore}|
 * |GET | email/accounts/0 | {@link GO\Modules\Email\Controller\AccountController::actionnew}|
 * |GET | email/accounts/:accountId | {@link GO\Modules\Email\Controller\AccountController::actionread}|
 * |PUT | email/accounts/:accountId | {@link GO\Modules\Email\Controller\AccountController::actionupdate}|
 * |POST | email/accounts | {@link GO\Modules\Email\Controller\AccountController::actioncreate}|
 * |DELETE | email/accounts/:accountId | {@link GO\Modules\Email\Controller\AccountController::actiondelete}|
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class Router {
	
	
	/**
	 *
	 * @var RoutesCollection[] 
	 */
	private $_routeCollections = [];
	
	/**
	 * Adds route collestions. 
	 * 
	 * Don't call this function yourself. Use {@see \GO\Core\Controller\AbstractController::routes()}.
	 * 
	 * @param \GO\Core\Http\RoutesCollection $routes
	 */
	public function addRoutes(RoutesCollection $routes) {
		$this->_routeCollections[] = $routes;
	}
	
	
//	private function coreRoutes(){		
		
	
		
		
//		return [
//				'auth' => [
//					'controller' => AuthController::className(),
//					'children' => [
//						'users' => [
//							'routeParams' => ['userId'],
//							'controller' => UserController::className(),
//							'children' => [
//								'roles' =>[
//									'controller' => UserRolesController::className()
//								]
//							]
//						],
//						'roles' => [
//							'routeParams' => ['roleId'],
//							'controller' => RoleController::className(),
//							'children' => [
//								'users' =>[
//									'controller' => RoleUsersController::className()
//								],
//								
//							]
//						],
//						'permissions' => [
//							'routerParams' => ['modelId', 'modelName'],
//							'controller' => PermissionsController::className()
//						]
//					]
//				],
//				
//				'system' => [
//					'children' => [
//						'check' => [						
//							'controller' => CheckController::className()
//						],
//						'upgrade' => [						
//							'controller' => UpgradeController::className()
//						],
//						'install' => [						
//							'controller' => InstallController::className()
//						]
//				]				
//			],
//			'modules' => [
//				'controller' => ModuleController::className()
//			]
//		];
//	}

	/**
	 * Defines core routes and collects routes from all modules
	 * 
	 * @return array[]
	 */
	private function _collectRoutes() {
		
		AuthController::routes()
				->get('auth', 'isLoggedIn')
				->post('auth', 'login')
				->delete('auth', 'logout');
		
		UserController::routes()				
				->get('auth/users', 'store')
				->get('auth/users/0','new')
				->get('auth/users/:userId','read')
				->put('auth/users/:userId', 'update')
				->post('auth/users', 'create')
				->delete('auth/users','delete');
		
		RoleController::routes()				
				->get('auth/roles', 'store')
				->get('auth/roles/0','new')
				->get('auth/roles/:roleId','read')
				->put('auth/roles/:roleId', 'update')
				->post('auth/roles', 'create')
				->delete('auth/roles','delete');
		
		SystemController::routes()
				->get('system/install', 'install')
				->get('system/upgrade', 'upgrade')
				->get('system/check', 'check');
		
		ModuleController::routes()
				->get('modules', 'store');

		
		
		if(System::isDatabaseInstalled()){
			$modules = Module::find();		
		
			foreach($modules as $module){
				$module->manager()->routes();
			}
		}
	}
	
	
	private $_optimizedRoutes = [];
	
	
	/**
	 * Converts route definitions to an optimized array for the router
	 * 
	 * <code>
	 *[
	 * 	'GET' => [
	 *			'auth' => [
	 *				'controller' => AuthController::className(),
	 *				'action' => 'login',
	 *				'actionWithParams' => null,
	 *				'routeParams' => [],
	 *				'children' => [
	 *					'users' => [
	 *						'controller' => UserController::className(),				
	 *						'routeParams' => ['userId'],
	 *						'actionWithParams' => 'read',
	 *						'action' => 'store',
	 *						'childen' => [
	 *							'0'=> [
	 *								'controller' => UserController::className(),				
	 *								'routeParams' => [],
	 *								'actionWithParams' => null,
	 *								'action' => 'new',
	 *							]
	 *						]
	 *					]
	 *				]
	 *			]
	 *		]
	 *		
	 *	];
	 * </code>
	 * 
	 * @return array
	 */
	private function _convertRoutes(){		
		foreach($this->_routeCollections as $routeCollection){
			$controller = $routeCollection->getController();
			
			$routes = $routeCollection->getRoutes();
			
			foreach($routes as $route){
				$method = $route[0];
				$action = $route[2];				
				$routeParts = explode('/', $route[1]);
				
				$this->_addRouteParts($routeParts, $method, $action, $controller);
			}
		}
		
		return $this->_optimizedRoutes;
	}
	
	/**
	 * Get all the route collections
	 * 
	 * Each controller has a route collection
	 * 
	 * @return RoutesCollection[]
	 */
	public function getRouteCollections(){
		return $this->_routeCollections;
	}
	
	private function _addRouteParts($routeParts, $method, $action, $controller){
		if(!isset($this->_optimizedRoutes[$method])){
			$this->_optimizedRoutes[$method] = [];
		}
		
		$cur['children'] = &$this->_optimizedRoutes[$method];
		
		//eg /bands/:bandId/albums/:albumId
		//At the bands we store bandId as routeParams and at albums we store albumId.
		//But the total of route params is 2 for this route.		
		$totalRouteParams = 0; 
		
		foreach($routeParts as $part) {
			
			if(substr($part, 0, 1) != ':'){			
				if(!isset($cur['children'][$part])) {
					$cur['children'][$part] = [
						'routeParams' => [],
						'actions' => [], //indexed with no. of params
						'controller' => $controller,
						'children' => []
					];
				}

				$cur = &$cur['children'][$part];
			}else
			{
				
				//route parameter				
				
				$routeParam = substr($part, 1);
				
				if(!in_array($routeParam, $cur['routeParams'])){
					$cur['routeParams'][] = $routeParam;
				}
				
				$totalRouteParams++;
			}
		}		
		$cur['actions'][$totalRouteParams] = $action;		
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
		
		try {
		
			if(!isset($_SERVER['PATH_INFO'])){
				App::request()->redirect($this->buildUrl('system/check'));
			}
		
			$this->route = ltrim($_SERVER['PATH_INFO'],	'/');

			$this->_routeParts = explode('/', $this->route);			

			$this->_collectRoutes();
			$this->_convertRoutes();
			
			
			if(!isset($this->_optimizedRoutes[$_SERVER['REQUEST_METHOD']])){
				throw new HttpException(404);
			}

			$this->_walkRoute($this->_optimizedRoutes[$_SERVER['REQUEST_METHOD']]);
		} catch (HttpException $e) {
			header("HTTP/1.1 " . $e->getCode());
			header("Status: ".$e->getCode());
			
			echo $e->getMessage();
		}
	}

	/**
	 * Get the params passed in a route.
	 * 
	 * eg. /contacts/1 where 1 is a "contactId" would return ["contactId" => 1];
	 * 
	 * @var array
	 */
	public $routeParams = [];
	
	private $_routeParts;

	private function _walkRoute($routes) {

		$routePart = array_shift($this->_routeParts);
		
		if(!isset($routes[$routePart])){
			throw new HttpException(404, "Route $routePart not found! " . var_export($routes, true));
		}
		$config = $routes[$routePart];

		$this->_getRouteParams($config);
		
		if (!empty($this->_routeParts)) {
			return $this->_walkRoute($config['children']);
		} else {

			$paramCount = count($this->routeParams);
			
			if (empty($config['actions'][$paramCount])) {
				//throw new Exception("No action defined for this route!");
				throw new HttpException(404, "No action defined for route ".$this->route." params: ".var_export($this->routeParams, true));
			}

			$controller = new $config['controller']($this);
			return $controller->run($config['actions'][$paramCount]);
		}
		
	}

	
	private function _getRouteParams($options) {
		foreach ($options['routeParams'] as $paramName) {
			
			if(empty($this->_routeParts)){
				break;
			}
			
			$part = array_shift($this->_routeParts);			
			if(isset($options['children'][$part])){
			
				//put back the part
				array_unshift($this->_routeParts, $part);
				
				// If there's an exact child match.
				// Like 0 here matches both rules. In this case the rule with the exact match wins.
				//
				// Rules example:
				// ->get('auth/users/0','new')
				// ->get('auth/users/:userId','read')
				
				break;
			}
			
			//add the param
			$this->routeParams[$paramName] = $part;
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
