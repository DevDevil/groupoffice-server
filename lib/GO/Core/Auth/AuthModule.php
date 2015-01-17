<?php

namespace GO\Core\Auth;

use GO\Core\AbstractModule;
use GO\Core\Auth\Controller\AuthController;
use GO\Core\Auth\Controller\PermissionsController;
use GO\Core\Auth\Controller\RoleController;
use GO\Core\Auth\Controller\RoleUsersController;
use GO\Core\Auth\Controller\UserController;
use GO\Core\Auth\Controller\UserRolesController;

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