<?php

namespace Intermesh\Modules\Auth;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\Auth\Controller\AuthController;
use Intermesh\Modules\Auth\Controller\PermissionsController;
use Intermesh\Modules\Auth\Controller\RoleController;
use Intermesh\Modules\Auth\Controller\RoleUsersController;
use Intermesh\Modules\Auth\Controller\UserController;
use Intermesh\Modules\Auth\Controller\UserRolesController;

class AuthModule extends AbstractModule{
	public function routes(){
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