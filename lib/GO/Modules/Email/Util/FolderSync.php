<?php
namespace GO\Modules\Email\Util;

use GO\Modules\Email\Model\Folder;


class FolderSync{
	
	private $_folder;
	
	public function __construct(Folder $folder) {
		$this->_folder = $folder;
	}
	
	private $stage; //new, update, delete
	
	public function start(){
		
	}
	
}