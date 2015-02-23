<?php

namespace GO\Core\Http;

use GO\Core\App;
use GO\Core\Auth\Browser\Controller\AuthController;
use GO\Core\Auth\Controller\RoleController;
use GO\Core\Auth\Controller\UserController;
use GO\Core\Auth\Oauth2\Controller\Oauth2Controller;
use GO\Core\Exception\HttpException;
use GO\Core\Install\Controller\SystemController;
use GO\Core\Install\Model\System;
use GO\Core\Modules\Controller\ModuleController;
use GO\Core\Modules\Model\Module;

/**
 * The router routes requests to their controller actions
 *
 * Some core routes are created in this class in the private function "_collectRoutes".
 * Each module can add routes too in their module file. {@see \GO\Core\AbstractModule}.
 * 
 * <code>
 * <?php
 * namespace GO\Modules\Bands;
 * 
 * use GO\Core\AbstractModule;
 * use GO\Modules\Bands\Controller\BandController;
 * use GO\Modules\Bands\Controller\HelloController;
 * 
 * class BandsModule extends AbstractModule {
 * 	
 * 	const PERMISSION_CREATE = 1;
 * 	
 * 	public function routes() {
 * 		BandController::routes()
 * 				->get('bands', 'store')
 * 				->get('bands/0','new')
 * 				->get('bands/:bandId','read')
 * 				->put('bands/:bandId', 'update')
 * 				->post('bands', 'create')
 * 				->delete('bands/:bandId','delete');
 * 		
 * 		HelloController::routes()
 * 				->get('bands/hello', 'name');
 * 	}
 * }
 * </code>
 * 
 * {@see \GO\Core\Controller\AbstractController}
 * 
 * Available routes:
 * 
  * | Method | Route | Controller     |
 * |--------|-------|----------------|
 * |GET | auth | {@link GO\Core\Auth\Controller\AuthController::actionIsLoggedIn}|
 * |POST | auth | {@link GO\Core\Auth\Controller\AuthController::actionLogin}|
 * |DELETE | auth | {@link GO\Core\Auth\Controller\AuthController::actionLogout}|
 * |POST | auth/oauth2/token | {@link GO\Core\Auth\Oauth2\Controller\Oauth2Controller::actionToken}|
 * |GET | auth/users | {@link GO\Core\Auth\Controller\UserController::actionStore}|
 * |GET | auth/users/0 | {@link GO\Core\Auth\Controller\UserController::actionNew}|
 * |GET | auth/users/:userId | {@link GO\Core\Auth\Controller\UserController::actionRead}|
 * |PUT | auth/users/:userId | {@link GO\Core\Auth\Controller\UserController::actionUpdate}|
 * |POST | auth/users | {@link GO\Core\Auth\Controller\UserController::actionCreate}|
 * |DELETE | auth/users | {@link GO\Core\Auth\Controller\UserController::actionDelete}|
 * |GET | auth/roles | {@link GO\Core\Auth\Controller\RoleController::actionStore}|
 * |GET | auth/roles/0 | {@link GO\Core\Auth\Controller\RoleController::actionNew}|
 * |GET | auth/roles/:roleId | {@link GO\Core\Auth\Controller\RoleController::actionRead}|
 * |PUT | auth/roles/:roleId | {@link GO\Core\Auth\Controller\RoleController::actionUpdate}|
 * |POST | auth/roles | {@link GO\Core\Auth\Controller\RoleController::actionCreate}|
 * |DELETE | auth/roles | {@link GO\Core\Auth\Controller\RoleController::actionDelete}|
 * |GET | system/install | {@link GO\Core\Install\Controller\SystemController::actionInstall}|
 * |GET | system/upgrade | {@link GO\Core\Install\Controller\SystemController::actionUpgrade}|
 * |GET | system/check | {@link GO\Core\Install\Controller\SystemController::actionCheck}|
 * |GET | modules | {@link GO\Core\Modules\Controller\ModuleController::actionStore}|
 * |POST | modules | {@link GO\Core\Modules\Controller\ModuleController::actionCreate}|
 * |GET | customfields/models | {@link GO\Modules\CustomFields\Controller\ModelController::actionGet}|
 * |GET | customfields/fieldsets/:modelName | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionStore}|
 * |GET | customfields/fieldsets/:modelName/0 | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionNew}|
 * |GET | customfields/fieldsets/:modelName/:fieldSetId | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionRead}|
 * |PUT | customfields/fieldsets/:modelName/:fieldSetId | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionUpdate}|
 * |POST | customfields/fieldsets/:modelName | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionCreate}|
 * |DELETE | customfields/fieldsets/:modelName/:fieldSetId | {@link GO\Modules\CustomFields\Controller\FieldSetController::actionDelete}|
 * |GET | customfields/fieldsets/:modelName/:fieldSetId/fields | {@link GO\Modules\CustomFields\Controller\FieldController::actionStore}|
 * |GET | customfields/fieldsets/:modelName/:fieldSetId/fields/0 | {@link GO\Modules\CustomFields\Controller\FieldController::actionNew}|
 * |GET | customfields/fieldsets/:modelName/:fieldSetId/fields/:fieldId | {@link GO\Modules\CustomFields\Controller\FieldController::actionRead}|
 * |PUT | customfields/fieldsets/:modelName/:fieldSetId/fields/:fieldId | {@link GO\Modules\CustomFields\Controller\FieldController::actionUpdate}|
 * |POST | customfields/fieldsets/:modelName/:fieldSetId/fields | {@link GO\Modules\CustomFields\Controller\FieldController::actionCreate}|
 * |DELETE | customfields/fieldsets/:modelName/:fieldSetId/fields/:fieldId | {@link GO\Modules\CustomFields\Controller\FieldController::actionDelete}|
 * |GET | devtools/models | {@link GO\Modules\DevTools\Controller\ModelController::actionList}|
 * |GET | devtools/models/:modelName/props | {@link GO\Modules\DevTools\Controller\ModelController::actionProps}|
 * |GET | devtools/routes | {@link GO\Modules\DevTools\Controller\RoutesController::actionMarkdown}|
 * |GET | upload | {@link GO\Modules\Upload\Controller\FlowController::actionUpload}|
 * |POST | upload | {@link GO\Modules\Upload\Controller\FlowController::actionUpload}|
 * |GET | upload/thumb/:tempFile | {@link GO\Modules\Upload\Controller\ThumbController::actionDownload}|
 * |GET | email/accounts | {@link GO\Modules\Email\Controller\AccountController::actionStore}|
 * |GET | email/accounts/0 | {@link GO\Modules\Email\Controller\AccountController::actionNew}|
 * |GET | email/accounts/:accountId | {@link GO\Modules\Email\Controller\AccountController::actionRead}|
 * |GET | email/accounts/:accountId/sync | {@link GO\Modules\Email\Controller\AccountController::actionSync}|
 * |PUT | email/accounts/:accountId | {@link GO\Modules\Email\Controller\AccountController::actionUpdate}|
 * |POST | email/accounts | {@link GO\Modules\Email\Controller\AccountController::actionCreate}|
 * |DELETE | email/accounts/:accountId | {@link GO\Modules\Email\Controller\AccountController::actionDelete}|
 * |GET | email/accounts/:accountId/folders | {@link GO\Modules\Email\Controller\FolderController::actionStore}|
 * |GET | email/accounts/:accountId/folders/0 | {@link GO\Modules\Email\Controller\FolderController::actionNew}|
 * |GET | email/accounts/:accountId/folders/:folderId | {@link GO\Modules\Email\Controller\FolderController::actionRead}|
 * |PUT | email/accounts/:accountId/folders/:folderId | {@link GO\Modules\Email\Controller\FolderController::actionUpdate}|
 * |POST | email/accounts/:accountId/folders | {@link GO\Modules\Email\Controller\FolderController::actionCreate}|
 * |DELETE | email/accounts/:accountId/folders/:folderId | {@link GO\Modules\Email\Controller\FolderController::actionDelete}|
 * |GET | email/accounts/:accountId/folders/:folderId/threads | {@link GO\Modules\Email\Controller\ThreadController::actionStore}|
 * |GET | email/accounts/:accountId/folders/:folderId/threads/:threadId/attachment | {@link GO\Modules\Email\Controller\AttachmentController::actionRead}|
 * |GET | announcements | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionStore}|
 * |GET | announcements/0 | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionNew}|
 * |GET | announcements/:announcementId | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionRead}|
 * |PUT | announcements/:announcementId | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionUpdate}|
 * |POST | announcements | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionCreate}|
 * |DELETE | announcements/:announcementId | {@link GO\Modules\Announcements\Controller\AnnouncementController::actionDelete}|
 * |GET | announcements/:announcementId/thumb | {@link GO\Modules\Announcements\Controller\ThumbController::actionDownload}|
 * |GET | contacts | {@link GO\Modules\Contacts\Controller\ContactController::actionStore}|
 * |GET | contacts/0 | {@link GO\Modules\Contacts\Controller\ContactController::actionNew}|
 * |GET | contacts/:contactId | {@link GO\Modules\Contacts\Controller\ContactController::actionRead}|
 * |PUT | contacts/:contactId | {@link GO\Modules\Contacts\Controller\ContactController::actionUpdate}|
 * |POST | contacts | {@link GO\Modules\Contacts\Controller\ContactController::actionCreate}|
 * |DELETE | contacts/:contactId | {@link GO\Modules\Contacts\Controller\ContactController::actionDelete}|
 * |GET | contacts/0/thumb | {@link GO\Modules\Contacts\Controller\ThumbController::actionDownload}|
 * |GET | contacts/:contactId/thumb | {@link GO\Modules\Contacts\Controller\ThumbController::actionDownload}|
 * |GET | contacts/:contactId/files | {@link GO\Modules\Contacts\Controller\FilesController::actionStore}|
 * |GET | contacts/:contactId/files/:fileId | {@link GO\Modules\Contacts\Controller\FilesController::actionRead}|
 * |PUT | contacts/:contactId/files/:fileId | {@link GO\Modules\Contacts\Controller\FilesController::actionUpdate}|
 * |POST | contacts/:contactId/files | {@link GO\Modules\Contacts\Controller\FilesController::actionCreate}|
 * |DELETE | contacts/:contactId/files/:fileId | {@link GO\Modules\Contacts\Controller\FilesController::actionDelete}|
 * |GET | bands | {@link GO\Modules\Bands\Controller\BandController::actionStore}|
 * |GET | bands/0 | {@link GO\Modules\Bands\Controller\BandController::actionNew}|
 * |GET | bands/:bandId | {@link GO\Modules\Bands\Controller\BandController::actionRead}|
 * |PUT | bands/:bandId | {@link GO\Modules\Bands\Controller\BandController::actionUpdate}|
 * |POST | bands | {@link GO\Modules\Bands\Controller\BandController::actionCreate}|
 * |DELETE | bands/:bandId | {@link GO\Modules\Bands\Controller\BandController::actionDelete}|
 * |GET | bands/hello | {@link GO\Modules\Bands\Controller\HelloController::actionName}|
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
	 * @param RoutesCollection $routes
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
		
		
		Oauth2Controller::routes()
				->post('auth/oauth2/token', 'token');
		
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
				->get('modules', 'store')
				->post('modules', 'create');

		
		
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
