<?php
namespace GO\Core\Install\Model;

use GO\Core\AbstractModel;
use GO\Core\App;
use GO\Core\Auth\Model\User;
use GO\Core\Db\Utils;
use GO\Core\Exception\Forbidden;
use GO\Core\Fs\File;
use GO\Core\Fs\Folder;
use GO\Core\Modules\Model\Module;
use GO\Core\Modules\ModuleUtils;
use PDOException;

class System extends AbstractModel{
	
	/**
	 * Check if the GroupOffice database has been installed
	 * 
	 * @return boolean
	 */
	public static function isDatabaseInstalled(){
		try {
			return App::dbConnection()->getPDO() && Utils::tableExists('modulesModule');
		}catch(PDOException $e){
			return false;
		}
	}
	
	/**
	 * Installs the GroupOffice database
	 * 
	 * @return boolean
	 */
	public function install() {
		if($this->isDatabaseInstalled()){
			throw new \Exception("The database was already installed");
		}
		
		$this->setUtf8Collation();
		
		$this->runCoreUpdates();
		
		$admin = User::findByPk(1);
		$admin->setCurrent();
		
		//Install all modules that should auto install
		$modules = ModuleUtils::getModules();
		
		foreach($modules as $module){
			if($module->autoInstall()){
				$module->install();
			}
		}
		
		return true;
	}
	
	private function setUtf8Collation(){
		//Set utf8 as collation default
		$sql = "ALTER DATABASE `".App::dbConnection()->database."` CHARACTER SET utf8 COLLATE utf8_unicode_ci;";		
		App::dbConnection()->query($sql);	
	}
	
	/**
	 * Run necessary upgrade patches
	 */
	public function upgrade(){
		
//		if(!User::current()->isAdmin()){
//			throw new Forbidden("Only admins may perform the upgrade");
//		}
		
		if(!$this->isDatabaseInstalled()){
			throw new \Exception("The database is not installed");
		}
		
		$this->runCoreUpdates();
		
		Module::runModuleUpdates();
		
		return true;
	}
	
	
	private function runCoreUpdates(){
		
		$updates = $this->databaseUpdates();
		
		App::dbConnection()->getPDO()->beginTransaction();
			
		try{
			foreach($updates as $file){
				if($file->getExtension() === 'php'){
					require($file->path());
				}else
				{					
					Utils::runSQLFile($file);
				}
				
				App::config()->dbVersion++;			
			}
			
			App::dbConnection()->getPDO()->commit();
		} catch (\Exception $e){
			App::dbConnection()->getPDO()->rollBack();
			
			$msg = "An exception ocurred in upgrade file ".$file->getPath()."\n\n".$e->getMessage();

			throw new \Exception($msg);
		}
	}
	
	
	/**
	 * Get update files.
	 * Can be SQL or PHP scripts.
	 * 
	 * @return File[] The array has the filename date stamp as key so it can be sorted
	 */
	private function databaseUpdates() {

		$dbFolder = new Folder(App::config()->getLibPath().'/Core/Install/Database');
		if (!$dbFolder->exists()) {
			return [];
		} else {

			$files = $dbFolder->getChildren(true, false);

			usort($files, function($file1, $file2) {
				return $file1->getName() > $file2->getName();
			});

			$version = $this->isDatabaseInstalled() ? App::config()->dbVersion : 0;

			if(!empty($version)){
				$files = array_slice($files, $version);
			}


			$regex = '/[0-9]{8}-[0-9]{4}\.(sql|php)/';
			foreach ($files as $file) {
				if (!preg_match($regex, $file->getName())) {
					throw new Exception("The upgrade file '" . $file->getName() . "' is not in the right filename format. It should be YYYYMMDD-HHMM.sql or YYYYMMDD-HHMM.php");
				}
			}

			return $files;
		}
	}
}