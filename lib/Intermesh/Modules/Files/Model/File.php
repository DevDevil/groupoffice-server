<?php

namespace Intermesh\Modules\Files\Model;

use Exception;
use Intermesh\Core\App;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Core\Fs\File as FsFile;
use Intermesh\Core\Db\SoftDeleteTrait;
/**
 * The file model
 * 
 * @property int $id
 * @property int $ownerUserId
 * @property \Intermesh\Modules\Auth\Model\User $owner
 * @property string $modifiedAt
 * @property string $createdAt
 * @property int $contactId
 * @property string $text
 * @property boolean $readOnly Used by dropbox now. Don't import files and folders to read only folders.
 * @property boolean $isFolder 
 * 
 * @property Folder $parent
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class File extends AbstractRecord {

	private $_newFile;
	
	use SoftDeleteTrait;

	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->belongsTo('parent', File::className(), 'parentId'),
			$r->hasMany('children', File::className(), 'parentId')->setQuery(Query::newInstance()->orderBy(['name' => 'ASC'])),
		];
	}
	
	
	public function getPath(){
		$path = $this->name;
		
		$parent = $this;
		while($parent = $parent->parent){
			$path = $parent->name.'/'.$path;			
		}
		
		return $path;
	}
	
	/**
	 * Find a file by path
	 * 
	 * @param string $path
	 * @return \Intermesh\Modules\Files\Model\File|boolean
	 */
	public static function findByPath($path){
		
		$path = trim($path, '/');

		$parts = explode('/', $path);
		
		$file = false;
		
		$parentId = null;
		while ($name = array_shift($parts)) {
			$file = File::find(['parentId' => $parentId, 'name' => $name])->single();
			
			if(!$file)
			{
				return false;				
			}
			
			$parentId = $file->id;			
		}
		
		return $file;
	}
	
	public function createFolder($path, $attributes = []){
		
		$path = trim($path, '/');

		$parts = explode('/', $path);
		
		$parentId = $this->id;
		while ($name = array_shift($parts)) {
			$file = File::find(['parentId' => $parentId, 'name' => $name])->single();
			
			if(!$file)
			{
				$file = new File();				
				
				$file->modelId = $this->modelId;
				$file->modelName = $this->modelName;
				
				$file->setAttributes($attributes);
				
				$file->parentId = $parentId;
				$file->isFolder = true;
				$file->name = $name;
				
				
				if(!$file->save()){
					return false;
				}
			}
			
			$parentId = $file->id;
		}	
		
		return $file;
		
	}
	
	public function createFile($name){
		$file = File::find(['parentId' => $this->id, 'name' => $name])->single();
			
		if(!$file)
		{
			$file = new File();
			$file->parentId = $this->id;
			$file->isFolder = false;
			$file->modelId = $this->modelId;
			$file->modelName = $this->modelName;
			$file->name = $name;
			if(!$file->save()){
				return false;
			}
			
		}
		return $file;
	}
	
	/**
	 * Set the model for this file or folder. This can be used for checking 
	 * file access
	 * 
	 * @param AbstractRecord $model
	 */
	public function setModel(AbstractRecord $model){
		$this->modelName = $model->className();
		$this->modelId = $model->id;
	}
	
	/**
	 * Get the model this file belongs too
	 * 
	 * @return AbstractRecord
	 */
	public function getModel(){
		return call_user_func([$this->modelName, 'findByPk'], $this->modelId);
	}

	/**
	 * Set file on the filesystem as the data of this file model
	 * 
	 * @param FsFile $file
	 */
	public function setFile(FsFile $file) {
		$this->_newFile = $file;

		$this->size = $file->getSize();
		$this->contentType = $file->getContentType();
	}
	
	/**
	 * Set a temporary file
	 *
	 * @param string $tempFilePath
	 */
	public function setTempPath($tempFilePath) {		
		$file = new FsFile(App::session()->getTempFolder().'/'.$tempFilePath);
		
		$this->setFile($file);
	}

	public function save() {

		$success = parent::save();

		if ($success && isset($this->_newFile)) {
			$destinationFile = $this->getFilesystemFile();

			//make sure folder exists
			$destinationFile->getFolder()->create();

			if (!$this->_newFile->move($destinationFile)) {
				throw new Exception("Failed to set file data!");
			}

			unset($this->_newFile);
		}

		return $success;
	}

	public function deletePermanently() {

		if (parent::deletePermanently()) {
			$this->getFilesystemFile()->delete();

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Output the file to the browser for download
	 */
	public function output() {
		
		header('Content-Type: ' . $this->contentType);
		header('Content-Disposition: inline; filename="' . $this->name . '"');
		header('Content-Length: ' . $this->size);
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", $this->modifiedAt) . ' GMT');

		$this->getFilesystemFile()->output();
	}

	/**
	 * 
	 * @return File
	 * @throws Exception
	 */
	public function getFilesystemFile() {

		if (!$this->id) {
			throw new Exception("Save file first!");
		}

		return App::config()->getDataFolder()->createFile('files/' . $this->parentId . '/' . $this->id);
	}

}
