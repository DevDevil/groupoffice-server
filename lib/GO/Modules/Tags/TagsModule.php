<?php

namespace GO\Modules\Tags;

use GO\Core\AbstractModule;
use GO\Modules\Tags\Controller\TagController;

class TagsModule extends AbstractModule {

	public function routes() {
		return [
			'tags' => [
				'routeParams' => ['tagId'],
				'controller' => TagController::className()					
			],
		];
	}
}