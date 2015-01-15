<?php
namespace GO\Modules\Bands;

use GO\Core\AbstractModule;
use GO\Modules\Bands\Controller\BandController;
use GO\Modules\Bands\Controller\HelloController;

class BandsModule extends AbstractModule {
	public function routes() {
		return [
			'bands' => [
				
				'controller' => BandController::className(),
				'routeParams' => ['bandId'],
				
				'children' => [
					'hello' => [
						'controller' => HelloController::className()
					]
				]
			]
		];
	}
}
