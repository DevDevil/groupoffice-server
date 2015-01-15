<?php

namespace GO\Modules\Modules;

use GO\Core\AbstractModule;
use GO\Modules\Modules\Controller\CheckController;
use GO\Modules\Modules\Controller\UpgradeController;
use GO\Modules\Modules\Controller\ModuleController;

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
