<?php

namespace Intermesh\Modules\Email\Controller;

use Intermesh\Core\Controller\AbstractRESTController;
use Intermesh\Modules\Email\Model\Folder;

/**
 * The controller for accounts. Admin role is required.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class TestController extends AbstractRESTController {

	public function httpGet() {
		$folder = Folder::find(['name' => 'INBOX'])->single();


		$dbUids = $folder->allUidsFromDb();
		$imapUids = $folder->imapMailbox()->search();

//		echo count($imapUids).' - '.count($dbUids);;
//		exit();
		
		$nextUids = array_diff($imapUids, $dbUids);


		var_dump($nextUids);

		$message = $folder->imapMailbox()->getMessage(array_shift($nextUids));

		echo $message->subject . ' - ' . $message->from->email.'-'.$message->uid;
		
		return $this->renderJson();
	}

}
