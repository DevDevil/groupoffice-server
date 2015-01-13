<?php

namespace Intermesh\Modules\Tennis;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\Tennis\Controller\SpeelsterkteController;

class TennisModule extends AbstractModule {

	public function routes() {
		return [
			'tennis' => [
				'controller' => SpeelsterkteController::className()				
			],
		];
	}
}