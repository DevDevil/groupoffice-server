<?php
namespace GO\Modules\DevTools;

use GO\Core\AbstractModule;

class DevToolsModule extends AbstractModule{
	public function routes() {
		return [
			'devtools' => [
				'children' =>[
					'model'=>[
						'controller' => Controller\ModelController::className(),
						'routeParams'=>['modelName']
					],
					'routes' => [
						'controller' => Controller\RoutesController::className()
					]
				]
			]
		];
	}
}