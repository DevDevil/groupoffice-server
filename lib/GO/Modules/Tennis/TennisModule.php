<?php

namespace GO\Modules\Tennis;

use GO\Core\AbstractModule;
use GO\Modules\Tennis\Controller\SpeelsterkteController;

class TennisModule extends AbstractModule {

	public function routes() {
		return [
			'tennis' => [
				'controller' => SpeelsterkteController::className()				
			],
		];
	}
}