<?php

namespace GO\Modules\Upload;

use GO\Core\AbstractModule;
use GO\Modules\Upload\Controller\FlowController;
use GO\Modules\Upload\Controller\ThumbController;

class UploadModule extends AbstractModule {

	public function routes() {
		
		FlowController::routes()
						->get('upload', 'upload')
						->post('upload', 'upload');
		
		ThumbController::routes()
						->get('upload/thumb/:tempFile', 'download');
		
	}
}