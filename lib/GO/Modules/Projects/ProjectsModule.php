<?php

namespace GO\Modules\Projects;

use GO\Core\AbstractModule;
use GO\Modules\Projects\Controller\ProjectController;
use GO\Modules\Projects\Controller\TaskController;

class ProjectsModule extends AbstractModule {

	public function routes() {
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
