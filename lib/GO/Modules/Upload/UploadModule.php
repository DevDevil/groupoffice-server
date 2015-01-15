<?php

namespace GO\Modules\Upload;

use GO\Core\AbstractModule;
use GO\Modules\Upload\Controller\FlowController;
use GO\Modules\Upload\Controller\ThumbController;

class UploadModule extends AbstractModule {

	public function routes() {
		return [
			'upload' => [
				'controller' => FlowController::className(),
				'children' => [
					'thumb' => [
						'routeParams' => ['tempFile'],
						'controller' => ThumbController::className()
					]
				],
			],
		];
	}
}