<?php
namespace GO\Modules\Files\Model;

use Exception;

trait RecordFolderTrait{
	
	
	public function getFolderPath(){
		return 'Contacts/'.$this->name;
	}
	
	/**
	 * 
	 * @return File|boolean
	 */
	public function getFolder($autoCreate = false){		
			
		//Upload makes concurrent requests so we must ensure the folder is not added multiple times.
		File::lock();		
		
		$path = $this->getFolderPath();		
		
		if($autoCreate){
			$parts = explode('/', $path);

			$rootFolderName = array_shift($parts);

			$rootFolder = File::find([
				'parentId' => null, 
				'name' => $rootFolderName
					])->single();

			if(!$rootFolder){
				$rootFolder = new File();
				$rootFolder->name = $rootFolderName;
				$rootFolder->isFolder = true;
				$rootFolder->setModel($this);
				$rootFolder->readOnly = true;

				if(!$rootFolder->save()){
					throw new Exception(var_export($rootFolder->getValidationErrors(), true));
				}
			}

			$folder = $rootFolder->createFolder(
					implode('/', $parts), 
					['readOnly' => true]
					);
		}else
		{
			$folder = File::findByPath($this->getFolderPath());			
		}
		
		\GO\Core\App::dbConnection()->unlockTables();
		
		return $folder;
	}	
}