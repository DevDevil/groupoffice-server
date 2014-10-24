<?php
namespace Intermesh\Modules\Contacts\Model;

use Intermesh\Core\App;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Core\Db\SoftDeleteTrait;
use Intermesh\Core\Fs\File;
use Intermesh\Core\Fs\Folder;
use Intermesh\Modules\Auth\Model\RecordPermissionTrait;
use Intermesh\Modules\Auth\Model\Role;
use Intermesh\Modules\Auth\Model\User;
use Intermesh\Modules\Files\Model\RecordFolderTrait;
use Intermesh\Modules\Tags\Model\Tag;
use Intermesh\Modules\Timeline\Model\Item;

/**
 * The contact model
 *
 * @property int $id
 * @property int $addressbookId
 * @property string $prefixes
 * @property string $firstName
 * @property string $middleName
 * @property string $lastName
 * @property string $suffixes
 * @property string $gender
 * @property string $birthDay
 * @property string $photoFilePath
 *
 * @property User $owner
 * @property ContactEmailAddress[] $emailAddresses
 * @property ContactPhone[] $phoneNumbers
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class Contact extends AbstractRecord{
	
	use RecordPermissionTrait;
	
	use SoftDeleteTrait;
	
	use RecordFolderTrait;

	public static function defineRelations(RelationFactory $r){
		return array(
			$r->belongsTo('owner', User::className(), 'ownerUserId'),
			$r->hasMany('roles', ContactRole::className(), 'contactId'),
			$r->hasMany('emailAddresses', ContactEmailAddress::className(), 'contactId')->autoCreate(),
			$r->hasMany('phoneNumbers', ContactPhone::className(), 'contactId')->autoCreate(),
			$r->manyMany('tags', Tag::className(), ContactTag::className(), 'contactId'),
			$r->hasMany('tagLink', ContactTag::className(), 'contactId'),
			$r->hasMany('addresses', ContactAddress::className(), 'contactId'),
			$r->hasMany('dates', ContactDate::className(), 'contactId'),
			$r->hasMany('employees', Contact::className(), 'companyContactId'),
			$r->belongsTo('company', Contact::className(), 'companyContactId'),
			
			$r->belongsTo('user', User::className(), 'userId'),
			
			$r->hasMany('timeline', Item::className(), 'contactId'),
			
			$r->hasOne('customfields', ContactCustomFields::className(), 'id')->autoCreate()
		);
	}

	/**
	 * Get the folder to store photo's in.
	 *
	 * @return Folder
	 */
	public static function getPhotosFolder(){
		return App::config()->getDataFolder()->createFolder('contactsPhotos')->create();
	}

//	/**
//	 * Get the name to display. It combines first, last and middle name.
//	 *
//	 * @return string
//	 */
//	public function getDisplayName(){
//		$name = $this->firstName;
//		if($this->middleName!=''){
//			$name .= ' '.$this->middleName;
//		}
//
//		if($this->lastName!=''){
//			$name .= ' '.$this->lastName;
//		}
//
//		return $name;
//	}

	/**
	 * Get the photo file
	 *
	 * @return File
	 */
	public function getPhotoFile(){
		if(empty($this->photoFilePath)){
			
			$gender= $this->gender =='M' ? 'male' : 'female';
			
			return new File(App::config()->getLibPath().'/Modules/Contacts/Resources/'.$gender.'.svg');
		}else
		{
			return new File(self::getPhotosFolder().'/'.$this->photoFilePath);
		}
	}

	/**
	 * Set a photo
	 *
	 * @param File $file
	 */
	public function setPhotoTempPath($temporaryImagePath, $save=false) {

		$photosFolder = self::getPhotosFolder();
		
		$file = new File(App::session()->getTempFolder().'/'.$temporaryImagePath);
		
		$destinationFile = $photosFolder->createFile($this->id.'.'.$file->getExtension());
		$destinationFile->delete();

		$file->move($destinationFile);
		$this->photoFilePath = $file->getRelativePath(self::getPhotosFolder());
		if($save){
			$this->save();
		}

	}
	
	public function validate() {
		
		//always fill name field on contact too
		if(!isset($this->name) && !$this->isCompany){
			$this->name = $this->firstName;
			
			if(!empty($this->middleName)){
					$this->name .= ' '.$this->middleName;
			}
			
			$this->name .= ' '.$this->lastName;
		}
		
		return parent::validate();
	}

	public function save() {

		$wasNew = $this->getIsNew();		
		
		if($this->isModified('photoFilePath') && $this->photoFilePath==""){
			//remove photo file
			$photoFile = $this->getPhotoFile();
		}

		if(!parent::save()){
			return false;
		}

		if(isset($photoFile) && $photoFile->exists()){
			$photoFile->delete();
		}
		
		
		if($wasNew){
			
			//Share this address book with the owner by adding it's role
			$model = new ContactRole();
			$model->contactId=$this->id;
			$model->roleId=$this->owner->role->id;
			$model->editAccess=1;
			$model->readAccess=1;
			$model->deleteAccess=1;
			$model->save();
			
			if($this->userId > 0){
				$contactRole = new ContactRole();
				$contactRole->contactId = $this->id;
				$contactRole->roleId = $this->userId;
				$contactRole->editAccess = true;
				$contactRole->save();
			}
			
			$autoRoles = Role::findAutoRoles();
			
			foreach($autoRoles as $role){
				$model = new ContactRole();
				$model->contactId=$this->id;
				$model->roleId=$role->id;
				$model->editAccess=1;
				$model->readAccess=1;
				$model->deleteAccess=1;
				$model->save();
			}
		}

		return $this;

	}
	
	
}