<?php
namespace GO\Modules\Bands;

use GO\Core\AbstractModule;
use GO\Modules\Bands\Controller\BandController;
use GO\Modules\Bands\Controller\HelloController;

class BandsModule extends AbstractModule {
	public function routes() {
		BandController::routes()
				->get('bands', 'store')
				->get('bands/0','new')
				->get('bands/:bandId','read')
				->put('bands/:bandId', 'update')
				->post('bands', 'create')
				->delete('bands/:bandId','delete');
		
		HelloController::routes()
				->get('bands/hello', 'name');
	}
	
	
}
