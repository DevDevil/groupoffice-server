<?php

namespace GO\Modules\Auth;

use GO\Core\AbstractModule;
use GO\Modules\Auth\Controller\AuthController;
use GO\Modules\Auth\Controller\PermissionsController;
use GO\Modules\Auth\Controller\RoleController;
use GO\Modules\Auth\Controller\RoleUsersController;
use GO\Modules\Auth\Controller\UserController;
use GO\Modules\Auth\Controller\UserRolesController;

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