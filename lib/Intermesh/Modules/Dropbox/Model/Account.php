<?php

namespace Intermesh\Modules\Dropbox\Model;

use Dropbox as dbx;
use Exception;
use Intermesh\Core\App;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\Column;
use Intermesh\Core\Fs\File as File2;
use Intermesh\Modules\Files\Model\File;

/**
 * The Account model
 *
 * @property int $ownerUserid
 * @property string $accessToken
 * @property string $requestToken
 * @property string $deltaCursor
 * @property int $dropboxUserId
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Account extends AbstractRecord {

	public static function primaryKeyColumn() {
		return 'ownerUserId';
	}

	private function getPocketOfficeSnapShot(File  $folder = null, $sort = true) {

		$snapshot = array();
		$stmt = isset($folder) ? $folder->children : File::find(['parentId' => null]);
		
		
		foreach ($stmt as $file) {
			$snapshot['/'.strtolower($file->path)] = array('modifiedAt' => $file->modifiedAt, 'path' => '/'.$file->path, 'file' => $file->isFolder ? false : $file->getFileSystemFile()->getPath());
			
			if($file->isFolder){
				$snapshot = array_merge($snapshot, self::getPocketOfficeSnapShot($file, false));				
			}
		}

		
		if ($sort){
			ksort($snapshot);
		}

		return $snapshot;
	}

	private function getDropboxSnapshot($path = "/", $sort=true) {

		$snapshot = array();

		$dbxClient = $this->getClient();
		$folderMetaData = $dbxClient->getMetadataWithChildren($path);


		if (isset($folderMetaData['contents'])) {
			foreach ($folderMetaData['contents'] as $item) {

				$snapshot[strtolower($item['path'])] = array('modifiedAt' => strtotime($item['modified']), 'path' => $item['path']);

				if ($item['is_dir']) {
					$snapshot = array_merge($snapshot, self::getDropboxSnapshot($item['path'], false));
				}
			}
		}

		if ($sort){
			ksort($snapshot);
		}

		return $snapshot;
	}

	
	/**
	 * 
	 * @return \Intermesh\Modules\Dropbox\Model\dbx\Client
	 */
	public function getClient(){
		return new dbx\Client($this->accessToken, App::config()->productName);
	}



	public function sync($reset = false) {

		
		//ini_set('max_execution_time')
		
		self::log("Dropbox sync");


		if ($reset) {
			$this->deltaCursor = null;
		}

		$dbxClient = $this->getClient();

		self::log("Getting Dropbox Changes");

		$hasMore = true;
		while ($hasMore) {

			$delta = $dbxClient->getDelta($this->deltaCursor);
			var_dump($delta);
//			exit();
			if (!isset($delta)) {
				throw new Exception("Could not get delta from Dropbox!");
			}
			$hasMore = $delta['has_more'];

			foreach ($delta['entries'] as $entry) {
				flush();

				//check if it's in the Group-Office sync root
//				if(DropboxModule::dbxPathIsInGo($entry[0])){
				//$entry[1]['path'] = with case. Otherwise we just have a string to lowered path for deleting
				$dbxPath = isset($entry[1]['path']) ? $entry[1]['path'] : $entry[0];
				$poPath = $dbxPath;

				if (!isset($entry[1])) {
					//should be deleted


					$file = File::findByPath($poPath);
					
					if ($file) {
						self::log("Deleting file on Group-Office " . $poPath);

						$file->delete();
					}else{
						self::log("Could not find path for delete file on Group-Office " . $poPath);						
					}
					
				} else if ($entry[1]['is_dir']) {
					self::log("Create folder on Group-Office " . $entry[1]['path'] . " -> " . $poPath);
					
					$parent = File::findByPath(dirname($poPath));					
					
					if(!$parent){
						self::log("Skipped folder creation because parent doesn't exist");
					}else if($parent->readOnly){
						self::log("Skipped folder creation because parent is marked as read only");
					}else
					{
						$parent->createFolder(File2::utf8Basename($poPath));
					}
					
					
				} else {

					self::log("Download from Dropbox " . $entry[1]['path'] . " -> " . $poPath);
					
					$poPathParts = explode('/', trim($poPath, '/'));
					
					$filename = array_pop($poPathParts);
					
					$rootFolder = File::findByPath(array_shift($poPathParts));					
					$folder = $rootFolder->createFolder(implode('/', $poPathParts));					
					
					if($folder->readOnly){
						self::log("Skipped download because folder is marked as read only");
					}else
					{
					
						$file = $folder->createFile($filename);

						$tmpFile = File2::tempFile();
						$f = fopen($tmpFile->getPath(), "w+b");
						$fileMetadata = $dbxClient->getFile($entry[0], $f);
						fclose($f);

						$file->modifiedAt = date(Column::DATETIME_API_FORMAT, strtotime($fileMetadata['modified']));
						$file->setFile($tmpFile);
						$file->save();
					}
				}
			}
		}
//		}

		$this->deltaCursor = $delta['cursor'];
		$this->save();



		self::log("Applying Group-Office changes to Dropbox");

		
		$poSnapshot = $this->getPocketOfficeSnapShot();

//		var_dump($goSnapshot);

		$dbxSnapshot = $this->getDropboxSnapshot();
//		var_dump($dbxSnapshot);
		
//		exit();
		foreach ($poSnapshot as $path => $props) {

			$dbxPath = $props['path'];
			$dbxPathToLower = strtolower($dbxPath);
			if (!isset($dbxSnapshot[$dbxPathToLower]) || $dbxSnapshot[$dbxPathToLower]['modifiedAt'] < $props['modifiedAt']) {
				if ($props['file']) {

					self::log("Upload to Dropbox " . $path . " -> " . $dbxPath);

					$inStream = fopen($props['file'], 'r');
					$meta = $dbxClient->uploadFile($dbxPath, dbx\WriteMode::force(), $inStream);

					if (!isset($meta)){
						throw new Exception("Failed to create file '" . $dbxPath . "' on Dropbox");
					}
				}elseif (!isset($dbxSnapshot[$dbxPathToLower])) {
					self::log("Create folder on Dropbox " . $path . " -> " . $dbxPath);

					$folderMetaData = $dbxClient->createFolder($dbxPath);

					if (!isset($folderMetaData)){
						throw new Exception("Failed to create folder '" . $dbxPath . "' on Dropbox");
					}
				}
			}
		}

		//reverse sort for deleting so that deeper files are deleted first.
		krsort($dbxSnapshot);
		
//		var_dump($dbxSnapshot);
//		var_dump($poSnapshot);
		
		foreach ($dbxSnapshot as $path => $props) {			
			if (!isset($poSnapshot[$path])) {
				self::log("Deleting on dropbox " . $path);

				if (!$dbxClient->delete($path)) {
					throw new Exception("Failed to delete '" . $path . "'");
				}
			}
		}

		//get delta again so we won't process our own changes next sync
		$delta = $dbxClient->getDelta($this->deltaCursor);
		$this->deltaCursor = $delta['cursor'];
		$this->save();

		self::log("Done!");
	}

	public static function log($text) {

		echo $text."\n";

//		if (!empty(\GO::config()->dropbox_log)) {
//			$user = isset(\GO::session()->values['username']) ? \GO::session()->values['username'] : 'notloggedin';
//
//			$text = "[$user] " . str_replace("\n", "\n[$user] ", $text);
//
//			file_put_contents(\GO::config()->file_storage_path . 'log/dropbox.log', $text . "\n", FILE_APPEND);
//		}
	}

}
