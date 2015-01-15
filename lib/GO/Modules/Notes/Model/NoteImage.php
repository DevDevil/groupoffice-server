<?php
namespace GO\Modules\Notes\Model;

use GO\Core\App;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\RelationFactory;
use GO\Core\Fs\File;
use GO\Core\Fs\Folder;

/**
 * @property int $id
 * @property int $noteId
 * @property string $path
 * @property int $sortOrder
 */

class NoteImage extends AbstractRecord{
	
	public $imageUrl;
	
	protected static function defineValidationRules() {
		return array(				

		);
	}
	
	public static function defineRelations(RelationFactory $r){
		return array(
			$r->belongsTo('note',Note::className(), 'noteId')	
		);
	}
	
	/**
	 * Get the folder to store images in.
	 *
	 * @return Folder
	 */
	public static function getImagesFolder(){
		return App::config()->getDataFolder()->createFolder('noteImages')->create();
	}
	
		/**
	 * Get the image file
	 *
	 * @return File
	 */
	public function getImageFile(){
		return new File(self::getImagesFolder().'/'.$this->path);
	}

	/**
	 * Set a image
	 *
	 * @param File $file
	 */
	public function setImageTempPath($temporaryImagePath, $save=false) {

		$imagesFolder = self::getImagesFolder();

		$file = new File(App::session()->getTempFolder().'/'.$temporaryImagePath);

		$file->move($imagesFolder->createFile($file->getName()));
		$this->path = $file->getRelativePath(self::getImagesFolder());
		if($save){
			$this->save();
		}

	}

	public function save() {

		if($this->isModified('path') && $this->path==""){
			//remove photo file
			$imageFile = $this->getImageFile();
		}

		if(!parent::save()){
			return false;
		}

		if(isset($imageFile) && $imageFile->exists()){
			$imageFile->delete();
		}

		return $this;

	}
	
}