<?php
namespace GO\Modules\Bands;

use GO\Core\AbstractModule;
use GO\Modules\Bands\Controller\BandController;
use GO\Modules\Bands\Controller\HelloController;

class BandsModule extends AbstractModule {
	public function routes() {
		return [
//			'GET /bands' => [BandController::className(), 'actionStore'],
//			'POST /bands' => [BandController::className(), 'actionCreate'],
//			'GET /bands/' => [BandController::className(), 'actionNew'],
//			'GET /bands/:bandId' => [BandController::className(), 'actionRead'],
//			'PUT /bands/:bandId' => [BandController::className(), 'actionUpdate'],
//			'DELETE /bands/:bandId' => [BandController::className(), 'actionDelete'],
//			
//			
//			BandController::className() => [
//				'GET /bands' => 'store',
//				'POST /bands' => 'actionCreate',
//				'GET /bands/' => 'actionNew',
//				'GET /bands/:bandId' => 'actionRead',
//				'PUT /bands/:bandId' =>  'actionUpdate',
//				'DELETE /bands/:bandId' => 'actionDelete',
//			],
//			
//			
//			BandController::className() => [
//				'GET /bands/:bandId/album' => 'store',
//				'POST /bands/:bandId/album' => 'actionCreate',
//				'GET /bands/:bandId/album/' => 'actionNew',
//				'GET /bands/:bandId/album/:albumId' => 'actionRead',
//				'PUT /bands/:bandId/album/:albumId' =>  'actionUpdate',
//				'DELETE /bands/:bandId/album/:albumId' => 'actionDelete',
//			],
			
			'bands' => [
				
				'controller' => BandController::className(),
				'routeParams' => ['bandId']		
				
			]
		];
	}
}
