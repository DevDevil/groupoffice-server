<?php
namespace Intermesh\Modules\Email\Util;

use Intermesh\Modules\Email\Model\Folder;


class FolderSync{
	
	private $_folder;
	
	public function __construct(Folder $folder) {
		$this->_folder = $folder;
	}
	
	private $stage; //new, update, delete
	
	public function start(){
		
	}
	
}