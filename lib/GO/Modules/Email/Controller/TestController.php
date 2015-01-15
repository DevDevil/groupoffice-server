<?php

namespace GO\Modules\Email\Controller;

use GO\Core\Controller\AbstractRESTController;
use GO\Modules\Email\Model\Folder;

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
		
//		$uid = array_shift($nextUids);
		
//		$dbMessage = \GO\Modules\Email\Model\Message::find(['imapUid' => $uid])->single();

//		var_dump($dbMessage ? $dbMessage->toArray() : false);

//		var_dump($nextUids);
		
		foreach($nextUids as $missingUid){
			$key = array_search($missingUid, $imapUids);
			
			echo $key."\n";
		}

//		$imapMessage = $folder->imapMailbox()->getMessage($uid);
//		
//		$dbMessage = new \GO\Modules\Email\Model\Message();
//		$dbMessage->folder = $folder;
//		$dbMessage->account = $folder->account;
//		$dbMessage->setFromImapMessage($imapMessage);
//		if(!$dbMessage->save()){
//			var_dump($dbMessage->getValidationErrors());
//		}

//		echo $imapMessage->subject . ' - ' . $imapMessage->from->email.'-'.$imapMessage->uid;
		
		return $this->renderJson();
	}

}
