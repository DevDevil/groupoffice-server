<?php
namespace Intermesh\Modules\Announcements\Model;

use Intermesh\Core\App;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Core\Fs\Folder;
use Intermesh\Modules\Auth\Model\User;
use Intermesh\Core\Fs\File;
use Intermesh\Core\Db\SoftDeleteTrait;
/**
 * The Anouncement model
 *
 * @property int $id
 * @property int $ownerUserId
 * @property User $owner
 * @property string $createdAt
 * @property string $modifiedAt
 * @property string $title
 * @property string $text
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Announcement extends AbstractRecord{
	
	use SoftDeleteTrait;
	
	public static function defineRelations(RelationFactory $r){
		return [
			$r->belongsTo('owner', User::className(), 'ownerUserId')
			];
	}
	
	
	/**
	 * Get the folder to store photo's in.
	 *
	 * @return Folder
	 */
	public static function getImagesFolder(){
		return App::config()->getDataFolder()->createFolder('announcementImages')->create();
	}
	
	/**
	 * Get the photo file
	 *
	 * @return File
	 */
	public function getImageFile(){
		if(empty($this->imagePath)){
			
			return false;
		}else
		{
			return new File(self::getImagesFolder().'/'.$this->imagePath);
		}
	}
	
	public function getThumbUrl(){
		
		//Added modified at so browser will reload when dynamically changed with js
		return empty($this->imagePath) ? false : App::router()->buildUrl("announcements/".$this->id."/thumb", ['modifiedAt' => $this->modifiedAt]); 
		
	}

	/**
	 * Set a photo
	 *
	 * @param File $file
	 */
	public function setImageTempPath($temporaryImagePath, $save=false) {

		$photosFolder = self::getImagesFolder();
		
		$file = new File(App::session()->getTempFolder().'/'.$temporaryImagePath);
		
		$destinationFile = $photosFolder->createFile($this->id.'.'.$file->getExtension());
		$destinationFile->delete();

		$file->move($destinationFile);
		$this->imagePath = $file->getRelativePath($photosFolder);
		if($save){
			$this->save();
		}

	}
}