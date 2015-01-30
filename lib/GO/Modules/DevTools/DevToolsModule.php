<?php
namespace GO\Modules\DevTools;

use GO\Core\AbstractModule;

class DevToolsModule extends AbstractModule{
	public function routes() {
		
		Controller\TestController::routes()
			->get('devtools', 'store')
			->get('devtools/0','new')
			->get('devtools/:fieldSetId','read')
			->put('devtools/:fieldSetId', 'update')
			->post('devtools', 'create')
			->delete('devtools/:fieldSetId','delete');
		
		
		return [
			'devtools' => [
				'children' =>[
					'test' => [
						'controller' => Controller\TestController::className(),
					],
					
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