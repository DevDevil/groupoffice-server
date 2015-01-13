<?php

namespace Intermesh\Modules\Modules;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\Modules\Controller\CheckController;
use Intermesh\Modules\Modules\Controller\UpgradeController;
use Intermesh\Modules\Modules\Controller\ModuleController;

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
