<?php

namespace GO\Modules\DevTools;

use GO\Core\AbstractModule;
use GO\Modules\DevTools\Controller\ModelController;
use GO\Modules\DevTools\Controller\RoutesController;

class DevToolsModule extends AbstractModule {

	public function routes() {

		ModelController::routes()
						->get('devtools/models', 'list')
						->get('devtools/models/:modelName/props', 'props');

		RoutesController::routes()
						->get('devtools/routes', 'markdown');
	}

}
