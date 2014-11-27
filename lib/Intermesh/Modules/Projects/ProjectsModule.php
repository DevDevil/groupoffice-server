<?php

namespace Intermesh\Modules\Projects;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\Projects\Controller\ProjectController;
use Intermesh\Modules\Projects\Controller\TaskController;

class ProjectsModule extends AbstractModule {

	public static function getRoutes() {
		return [
			'projects' => [
				'routeParams' => ['projectId'],
				'controller' => ProjectController::className(),
				'children' => [
					'tasks' => [
						'routeParams' => ['taskId'],
						'controller' => TaskController::className(),
					]
				]
			]
		];
	}
}
