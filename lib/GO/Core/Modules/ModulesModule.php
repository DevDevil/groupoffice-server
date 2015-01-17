<?php

namespace GO\Core\Modules;

use GO\Core\AbstractModule;
use GO\Core\Modules\Controller\CheckController;
use GO\Core\Modules\Controller\UpgradeController;
use GO\Core\Modules\Controller\ModuleController;

class ModulesModule extends AbstractModule {

	public  function routes() {
		return [
			'modules' => [
				'controller' => ModuleController::className(),
				'children' => [
					'check' => [						
						'controller' => CheckController::className()
					],
					'upgrade' => [						
						'controller' => UpgradeController::className()
					]
				],
			]
		];
	}
}
