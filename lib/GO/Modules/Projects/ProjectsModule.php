<?php

namespace GO\Modules\Projects;

use GO\Core\AbstractModule;
use GO\Modules\Projects\Controller\ProposalController;

class ProjectsModule extends AbstractModule {

	public function routes() {
		ProposalController::routes()
				->get('proposals', 'store')
				->get('proposals/0','new')
				->get('proposals/:proposalId','read')
				->put('proposals/:proposalId', 'update')
				->post('proposals', 'create')
				->delete('proposals/:proposalId','delete');
	}
}
