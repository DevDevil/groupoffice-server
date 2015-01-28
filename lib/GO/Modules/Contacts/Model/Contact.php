<?php
namespace GO\Modules\Contacts\Model;

use GO\Core\App;
use GO\Core\Db\AbstractRecord;

use GO\Core\Db\SoftDeleteTrait;
use GO\Core\Fs\File;
use GO\Core\Fs\Folder;
use GO\Core\REST\RESTModelInterface;
use GO\Core\Auth\Model\RecordPermissionTrait;
use GO\Core\Auth\Model\Role;
use GO\Core\Auth\Model\User;
use GO\Modules\Files\Model\RecordFolderTrait;
use GO\Modules\Tags\Model\Tag;
use GO\Modules\Timeline\Model\Item;

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
 * @property string $_photoFilePath
 *
 * @property User $owner
 * @property ContactEmailAddress[] $emailAddresses
 * @property ContactPhone[] $phoneNumbers
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Contact extends AbstractRecord {
	
	use RecordPermissionTrait;
	
	use SoftDeleteTrait;
	
	use RecordFolderTrait;
	
	public function __construct() {
		parent::__construct();
		
		if($this->getIsNew()){
			$this->emailAddresses = [new ContactEmailAddress()];
			$this->emailAddresses = [new ContactPhone()];
			$this->customfields = [new ContactCustomFields()];
		}
	}

	public static function defineRelations(){
		
		self::belongsTo('owner', User::className(), 'ownerUserId');
		self::hasMany('roles', ContactRole::className(), 'contactId');
		self::hasMany('emailAddresses', ContactEmailAddress::className(), 'contactId');
		self::hasMany('phoneNumbers', ContactPhone::className(), 'contactId');
		self::manyMany('tags', Tag::className(), ContactTag::className(), 'contactId');
		self::hasMany('tagLink', ContactTag::className(), 'contactId');
		self::hasMany('addresses', ContactAddress::className(), 'contactId');
		self::hasMany('dates', ContactDate::className(), 'contactId');
		self::hasMany('employees', Contact::className(), 'companyContactId');
		self::belongsTo('company', Contact::className(), 'companyContactId');			
		self::belongsTo('user', User::className(), 'userId');			
		self::hasMany('timeline', Item::className(), 'contactId');	
		self::hasOne('customfields', ContactCustomFields::className(), 'id');
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
	public function photoFile(){
		if(empty($this->_photoFilePath)){
			
			$gender= $this->gender != 'F' ? 'male' : 'female';
			
			return new File(App::config()->getLibPath().'/Modules/Contacts/Resources/'.$gender.'.png');
		}
		
		return new File(self::getPhotosFolder().'/'.$this->_photoFilePath);
		
	}
	
	public function getPhoto(){		
		//Added modified at so browser will reload when dynamically changed with js
		return App::router()->buildUrl("contacts/".intval($this->id)."/thumb", ['modifiedAt' => $this->modifiedAt]); 
	}

	/**
	 * Set a photo
	 *
	 * @param string $temporaryImagePath
	 */
	public function setPhoto($temporaryImagePath) {

		$photosFolder = self::getPhotosFolder();
		
		$file = new File(App::session()->getTempFolder().'/'.$temporaryImagePath);
		
		$destinationFile = $photosFolder->createFile(uniqid().'.'.$file->getExtension());
		$destinationFile->delete();

		$file->move($destinationFile);
		$this->_photoFilePath = $file->getRelativePath($photosFolder);
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
		
		if($this->isModified('_photoFilePath') && $this->_photoFilePath==""){
			//remove photo file after save
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