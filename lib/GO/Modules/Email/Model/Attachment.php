<?php

namespace GO\Modules\Email\Model;

use Exception;
use GO\Core\App;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\RelationFactory;
use GO\Core\Fs\File;


/**
 * The Attachment model
 *
 * @property int $id
 * @property int $messageId
 * @property string $filename
 * @property string $contentType
 * @property string $contentId
 * @property boolean $inline
 * @property string $imapPartNumber
 * @property boolean $foundInBody
 * 
 * @property Message $message
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Attachment extends AbstractRecord {
	
	private $_newFile;
	
	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->belongsTo('message', Message::className(), 'messageId'),
		];
	}
	
	/**
	 * Set file on the filesystem as the data of this file model
	 * 
	 * @param File $file
	 */
	public function setFile(File $file) {
		$this->_newFile = $file;

		$this->size = $file->getSize();		
	}

	public function save() {

		$success = parent::save();

		if ($success && isset($this->_newFile)) {
			$destinationFile = $this->_getFilesystemFile();

			//make sure folder exists
			$destinationFile->getFolder()->create();

			if (!$this->_newFile->move($destinationFile)) {
				throw new Exception("Failed to set file data!");
			}

			unset($this->_newFile);
		}

		return $success;
	}
	
	/**
	 * Output the file to the browser for download
	 */
	public function output() {
		
		if($this->size == null){
			//attachment hasn't been downloaded from IMAP yet.
			
			$imapMessage = $this->message->getImapMessage(true);
			
			if(!$imapMessage){
				throw new \Exception("Could not get IMAP message");
			}
			
			$attachments = $imapMessage->getStructure()->findParts(['partNumber' => $this->imapPartNumber]);
			$attachment = array_shift($attachments);
			if(!$attachment){
//				var_dump($imapMessage->getStructure()->toArray());
				throw new \Exception("Could not find attachment part with number: ".$this->imapPartNumber);
			}
			
			$file = File::tempFile();
			$attachment->output($file->open('w'));				

			$this->setFile($file);
			$this->save();
		}
		
		header('Content-Type: ' . $this->_getFilesystemFile()->getContentType());
		header('Content-Disposition: inline; filename="' . $this->filename . '"');
		header('Content-Length: ' . $this->size);
		
		$this->_getFilesystemFile()->output();
	}
	
	
	
	/**
	 * 
	 * @return File
	 * @throws Exception
	 */
	private function _getFilesystemFile() {

		if (!$this->id) {
			throw new Exception("Save file first!");
		}

		return App::config()->getDataFolder()->createFile('email/' . $this->messageId . '/' . $this->id);
	}
	
	public function getUrl(){
		return App::router()->buildUrl('email/accounts/'.$this->message->accountId.'/folders/'.$this->message->folderId.'/threads/'.$this->message->threadId.'/attachments/'.$this->id);
	}
	
//	public function toArray(array $returnAttributes = ['*', 'url']) {
//		return parent::toArray($returnAttributes);
//	}
}