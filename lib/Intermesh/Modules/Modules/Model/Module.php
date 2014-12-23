<?php
namespace Intermesh\Modules\Modules\Model;

use Intermesh\Core\AbstractModule;
use Intermesh\Core\App;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Core\Db\SoftDeleteTrait;
use Intermesh\Core\Db\Utils;
use Intermesh\Modules\Auth\Model\RecordPermissionTrait;
use Intermesh\Modules\Files\Model\File;
use SebastianBergmann\Exporter\Exception;

/**
 * Module model
 * 
 * Each module that can be used in the application must have a database entry.
 *
 * @property int $id
 * @property string $name
 * @property int $version
 * 
 * @property ModuleRole $roles
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends AbstractRecord{
	
	/**
	 * When a module is installed it will install dependencies allong
	 * This boolean prevents an endless loop installation
	 * 
	 * @var boolean 
	 */
	public $dontInstallDatabase = false;
	
	use SoftDeleteTrait {
        delete as softDelete;
    }	
	
	use RecordPermissionTrait;
	
	public $ownerUserId = 1;
	
	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->hasMany('roles', ModuleRole::className(), 'moduleId')
			];
	}	
	
//	
//	public static function installCoreModules(){
//		foreach(self::$coreModules as $moduleName) {
//			try{
//				$module = Module::find(['name' => $moduleName])->single();
//			} catch (\PDOException $ex) {
//				//tables not created yet
//				$module = false;
//			}
//			
//			
//			if(!$module) {
//				$module = new Module();
//				$module->name = $moduleName;
//				$module->save();
//			}
//		}
//	}
	
	/**
	 * Get the module manager file
	 * 
	 * @return AbstractModule
	 */
	public function manager(){	
		
		return new $this->name;
	}
	
	public function save() {
		
		$isNew = $this->getIsNew();
		
		$ret = parent::save();
		if(!$ret){
			return $ret;
		}
		
		if($isNew && !$this->dontInstallDatabase){
			$depends = $this->manager()->getRecursiveDependencies();	
			$this->runModuleUpdates($depends);
		}
		
		return $ret;	
	}	
	
	
	public static function runModuleUpdates(array $moduleManagers = null){
		
		$updates = self::collectModuleUpgrades($moduleManagers);
		
		App::dbConnection()->getPDO()->beginTransaction();
			
		try{
			foreach($updates as $update){

				$file = $update[1];
				$moduleManagerClass = $update[0];
				
				$module = Module::find(['name' => $moduleManagerClass])->single();
				if(!$module) {
					$module = new Module();
					$module->name = $moduleManagerClass;
					$module->dontInstallDatabase = true;					
				}

				if($file->getExtension() === 'php'){
					require($file->path());
				}else
				{					
					Utils::runSQLFile($file);
				}
				
				$module->version++;
				$module->save();
				
			}
			
			App::dbConnection()->getPDO()->commit();
		} catch (Exception $e){
			App::dbConnection()->getPDO()->rollBack();

			throw $e;
		}
	}
	
	
	/**
	 * Get all update files sorted.
	 * 
	 * @return File[]
	 */
	private static function collectModuleUpgrades(array $moduleManagers = null) {
		
		if(!isset($moduleManagers)){
			$modules = Module::find();

			$updates = [];

			foreach ($modules as $module) {
				$moduleManagers[] = $module->name;
			}
		}
	
		$updates = [];
		
		

		foreach ($moduleManagers as $moduleManagerClass) {
			
			
			
			$manager = new $moduleManagerClass;
			

				
			$modUpdates = $manager->databaseUpdates();

			foreach ($modUpdates as $updateFile) {
				$suffix = "";

				while (isset($updates[$updateFile->getName() . $suffix])) {
					$suffix++;
				}

				$updates[$updateFile->getName() . $suffix] = [$moduleManagerClass, $updateFile];
			}

		}


		return array_values($updates);
	}
	
	

	
	public function delete() {
		
		if (in_array($this->name, self::$coreModules)) {
			$this->setValidationError('id', 'coreModuleDeleteForbidden');
			return false;
		}
		
		return $this->softDelete();
	}
	
	
	
	
}