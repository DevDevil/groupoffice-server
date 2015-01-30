<?php
namespace GO\Modules\Email\Controller;

use GO\Core\Controller\AbstractController;
use GO\Modules\Email\Model\Attachment;

class AttachmentController extends AbstractController {



	/**
	 * GET a list of accounts or fetch a single account
	 *
	 * 
	 * @param int $accountId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($attachmentId) {
		
		//cache for a month
		$this->cacheHeaders(null, "attachment-".$attachmentId, new \DateTime('@'.(time()+86400*30)));
		
		
		$attachment = Attachment::findByPk($attachmentId);
		$attachment->output();

	}
}