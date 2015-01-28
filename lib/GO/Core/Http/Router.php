<?php

namespace GO\Core\Http;

use Exception;
use GO\Core\App;
use GO\Core\Auth\Controller\AuthController;
use GO\Core\Auth\Controller\PermissionsController;
use GO\Core\Auth\Controller\RoleController;
use GO\Core\Auth\Controller\RoleUsersController;
use GO\Core\Auth\Controller\UserController;
use GO\Core\Auth\Controller\UserRolesController;
use GO\Core\Install\Controller\CheckController;
use GO\Core\Install\Controller\InstallController;
use GO\Core\Install\Controller\UpgradeController;
use GO\Core\Install\Model\System;
use GO\Core\Modules\Controller\ModuleController;
use GO\Core\Modules\Model\Module;

/**
 * The router routes requests to their controller actions
 *
 * Each module can add routes See {@see \GO\Core\AbstractModule} for information
 * about creating routes
 * 
 * {@see \GO\Core\Controller\AbstractRESTController} and {@see \GO\Core\Controller\AbstractCrudController}
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
	
	
	private function coreRoutes(){
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
				],
				
				'system' => [
					'children' => [
						'check' => [						
							'controller' => CheckController::className()
						],
						'upgrade' => [						
							'controller' => UpgradeController::className()
						],
						'install' => [						
							'controller' => InstallController::className()
						]
				]				
			],
			'modules' => [
				'controller' => ModuleController::className()
			]
		];
	}

	/**
	 * Get routing tables from all modules
	 * 
	 * @todo cache this
	 * 
	 * @return array[]
	 */
	public function getRoutes() {

		
		$routes = $this->coreRoutes();
		
		if(System::isDatabaseInstalled()){
			$modules = Module::find();		
		
			foreach($modules as $module){

				$routes = array_merge($routes, $module->manager()->routes());
			}
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
				App::request()->redirect($this->buildUrl('system/check'));
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
