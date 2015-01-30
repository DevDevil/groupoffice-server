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
  * | Route | Controller     |
 * |-------|----------------|
 * |/modules | {@link GO\Core\Modules\Controller\ModuleController}|
 * |/modules/check | {@link GO\Core\Modules\Controller\CheckController}|
 * |/modules/upgrade | {@link GO\Core\Modules\Controller\UpgradeController}|
 * |/auth | {@link GO\Core\Auth\Controller\AuthController}|
 * |/auth/users | {@link GO\Core\Auth\Controller\UserController}|
 * |/auth/users/[userId] | {@link GO\Core\Auth\Controller\UserController}|
 * |/auth/users/[userId]/roles | {@link GO\Core\Auth\Controller\UserRolesController}|
 * |/auth/roles | {@link GO\Core\Auth\Controller\RoleController}|
 * |/auth/roles/[roleId] | {@link GO\Core\Auth\Controller\RoleController}|
 * |/auth/roles/[roleId]/users | {@link GO\Core\Auth\Controller\RoleUsersController}|
 * |/auth/permissions | {@link GO\Core\Auth\Controller\PermissionsController}|
 * |/notes | {@link GO\Modules\Notes\Controller\NoteController}|
 * |/notes/[noteId] | {@link GO\Modules\Notes\Controller\NoteController}|
 * |/notes/[noteId]/noteImages | {@link GO\Modules\Notes\Controller\ThumbController}|
 * |/notes/[noteId]/noteImages/[noteImageId] | {@link GO\Modules\Notes\Controller\ThumbController}|
 * |/notes/[noteId]/noteImages/[noteImageId]/thumb | {@link GO\Modules\Notes\Controller\ThumbController}|
 * |/tennis | {@link GO\Modules\Tennis\Controller\SpeelsterkteController}|
 * |/tags | {@link GO\Modules\Tags\Controller\TagController}|
 * |/tags/[tagId] | {@link GO\Modules\Tags\Controller\TagController}|
 * |/customfields/models | {@link GO\Modules\CustomFields\Controller\ModelController}|
 * |/customfields/fieldsets | {@link GO\Modules\CustomFields\Controller\FieldSetController}|
 * |/customfields/fieldsets/[modelName] | {@link GO\Modules\CustomFields\Controller\FieldSetController}|
 * |/customfields/fieldsets/[modelName]/[fieldSetId] | {@link GO\Modules\CustomFields\Controller\FieldSetController}|
 * |/customfields/fieldsets/[modelName]/[fieldSetId]/fields | {@link GO\Modules\CustomFields\Controller\FieldController}|
 * |/customfields/fieldsets/[modelName]/[fieldSetId]/fields/[fieldId] | {@link GO\Modules\CustomFields\Controller\FieldController}|
 * |/upload | {@link GO\Modules\Upload\Controller\FlowController}|
 * |/upload/thumb | {@link GO\Modules\Upload\Controller\ThumbController}|
 * |/upload/thumb/[tempFile] | {@link GO\Modules\Upload\Controller\ThumbController}|
 * |/email/test | {@link GO\Modules\Email\Controller\TestController}|
 * |/email/sync | {@link GO\Modules\Email\Controller\SyncController}|
 * |/email/sync/[accountId] | {@link GO\Modules\Email\Controller\SyncController}|
 * |/email/accounts | {@link GO\Modules\Email\Controller\AccountController}|
 * |/email/accounts/[accountId] | {@link GO\Modules\Email\Controller\AccountController}|
 * |/email/accounts/[accountId]/mailboxes/[mailboxName]/messages | {@link GO\Modules\Email\Controller\ImapMessageController}|
 * |/email/accounts/[accountId]/mailboxes/[mailboxName]/messages/[uid] | {@link GO\Modules\Email\Controller\ImapMessageController}|
 * |/email/accounts/[accountId]/mailboxes/[mailboxName]/messages/[uid]/attachments | {@link GO\Modules\Email\Controller\AttachmentController}|
 * |/email/accounts/[accountId]/mailboxes/[mailboxName]/messages/[uid]/attachments/[partNumber] | {@link GO\Modules\Email\Controller\AttachmentController}|
 * |/email/accounts/[accountId]/folders | {@link GO\Modules\Email\Controller\FolderController}|
 * |/email/accounts/[accountId]/folders/[folderId] | {@link GO\Modules\Email\Controller\FolderController}|
 * |/email/accounts/[accountId]/folders/[folderId]/threads | {@link GO\Modules\Email\Controller\ThreadController}|
 * |/email/accounts/[accountId]/folders/[folderId]/threads/[threadId] | {@link GO\Modules\Email\Controller\ThreadController}|
 * |/email/accounts/[accountId]/folders/[folderId]/threads/[threadId]/attachments | {@link GO\Modules\Email\Controller\AttachmentController}|
 * |/email/accounts/[accountId]/folders/[folderId]/threads/[threadId]/attachments/[attachmentId] | {@link GO\Modules\Email\Controller\AttachmentController}|
 * |/email/accounts/[accountId]/folders/[folderId]/messages | {@link GO\Modules\Email\Controller\MessageController}|
 * |/email/accounts/[accountId]/folders/[folderId]/messages/[messageId] | {@link GO\Modules\Email\Controller\MessageController}|
 * |/email/accounts/[accountId]/folders/[folderId]/messages/[messageId]/attachments | {@link GO\Modules\Email\Controller\AttachmentController}|
 * |/email/accounts/[accountId]/folders/[folderId]/messages/[messageId]/attachments/[attachmentId] | {@link GO\Modules\Email\Controller\AttachmentController}|
 * |/announcements | {@link GO\Modules\Announcements\Controller\AnnouncementController}|
 * |/announcements/[announcementId] | {@link GO\Modules\Announcements\Controller\AnnouncementController}|
 * |/announcements/[announcementId]/thumb | {@link GO\Modules\Announcements\Controller\ThumbController}|
 * |/projects | {@link GO\Modules\Projects\Controller\ProjectController}|
 * |/projects/[projectId] | {@link GO\Modules\Projects\Controller\ProjectController}|
 * |/projects/[projectId]/tasks | {@link GO\Modules\Projects\Controller\TaskController}|
 * |/projects/[projectId]/tasks/[taskId] | {@link GO\Modules\Projects\Controller\TaskController}|
 * |/contacts | {@link GO\Modules\Contacts\Controller\ContactController}|
 * |/contacts/[contactId] | {@link GO\Modules\Contacts\Controller\ContactController}|
 * |/contacts/[contactId]/thumb | {@link GO\Modules\Contacts\Controller\ThumbController}|
 * |/contacts/[contactId]/files | {@link GO\Modules\Contacts\Controller\FilesController}|
 * |/contacts/[contactId]/files/[fileId] | {@link GO\Modules\Contacts\Controller\FilesController}|
 * |/contacts/[contactId]/timeline | {@link GO\Modules\Contacts\Controller\TimelineController}|
 * |/contacts/[contactId]/timeline/[itemId] | {@link GO\Modules\Contacts\Controller\TimelineController}|
 * |/devtools/model | {@link GO\Modules\DevTools\Controller\ModelController}|
 * |/devtools/model/[modelName] | {@link GO\Modules\DevTools\Controller\ModelController}|
 * |/devtools/routes | {@link GO\Modules\DevTools\Controller\RoutesController}|
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
	
	private function _addRouteParts($routeParts, $method, $action, $controller){
		if(!isset($this->_optimizedRoutes[$method])){
			$this->_optimizedRoutes[$method] = [];
		}
		
		$cur['children'] = &$this->_optimizedRoutes[$method];
		
		foreach($routeParts as $part) {
			
			if(substr($part, 0, 1) != ':'){			
				if(!isset($cur['children'][$part])) {
					$cur['children'][$part] = [
						'routeParams' => [],
						'actionWithParams' => null,
						'action' => null,
						'controller' => $controller,
						'children' => []
					];
				}

				$cur = &$cur['children'][$part];
			}else
			{
				//route parameter
				
				$cur['routeParams'][] = substr($part, 1);
			}
		}
		
		if(!empty($cur['routeParams'])) {
			$cur['actionWithParams'] = $action;
		}else
		{
			$cur['action'] = $action;
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
						'controller' => "GO\Core\Controller\RESTController",						
						'children' => [
								'thumb' => [
									'routeParams' => ['contactId'],
									'controller' => "GO\Contacts\Controller\Contact"									
								]
							]
						]
	 * 
	 * @var array
	 */
	
	public $routeConfig;
	
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

			$action = empty($this->routeParams) ? $config['action'] : $config['actionWithParams'];

			if (empty($action)) {
				//throw new Exception("No action defined for this route!");
				throw new HttpException(404, "No action defined for route");
			}
			
			
			//Is this needed?
			$this->routeConfig = $config;

			$controller = new $config['controller']($this);
			return $controller->run($action);
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
				$this->_routeParts[] = $part;
				
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
