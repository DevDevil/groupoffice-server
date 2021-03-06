<?php
namespace GO\Core\Modules\Model;

use GO\Core\AbstractModule;
use GO\Core\App;
use GO\Core\Auth\Model\RecordPermissionTrait;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\SoftDeleteTrait;
use GO\Core\Db\Utils;
use GO\Modules\Files\Model\File;
use ReflectionClass;
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
	
	
	const PERMISSION_USE = 0;
	
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
	
	use RecordPermissionTrait{
		permissionTypes as traitPermissionTypes;
	}
	
	public $ownerUserId = 1;
	
	protected static function defineRelations() {		
		self::hasMany('roles', ModuleRole::className(), 'moduleId');
	}	
	
	
	/**
	 * Get the module manager file
	 * 
	 * @return AbstractModule
	 */
	public function manager(){	
		
		
		if(!class_exists($this->name)){
			throw new \Exception("Module ".$this->name." is not found!");
		}
		
		return new $this->name;
	}
	
	/**
	 * 
	 * {@inheritdoc}
	 * @todo Transaction on install
	 */
	public function save() {
		
		$isNew = $this->isNew();
		
		if($isNew){			
			//Grant access to admins by default
			$this->roles = [['roleId' => 1, 'permissionType' => Module::PERMISSION_USE]];
		}
		
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
	
	/**
	 * Run all module upgrades of the given modules
	 * 
	 * @param array $moduleManagers
	 * @throws Exception
	 */
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
		} catch (\Exception $e){
			App::dbConnection()->getPDO()->rollBack();

			$msg = "An exception ocurred in upgrade file ".$file->getPath().". If you're a developer, you might need to skip the upgrade file because you already applied the changes. You can raise the version number in the 'modulesModules' table to do that.\n\n".$e->getMessage();

			throw new \Exception($msg);
		}
	}
	
	
	/**
	 * Get all update files of the given modules sorted by date.
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
		
		ksort($updates);

		return array_values($updates);
	}
	
	public function delete() {
		
		throw new \Exception("todo check module deps!");
		
		return $this->softDelete();
	}
	
	
	
	public function permissionTypes(){
		
		$types = $this->traitPermissionTypes();
		
		$reflectionClass = new ReflectionClass($this->name);

		foreach($reflectionClass->getConstants() as $name => $value){
			if(substr($name, 0, 11) == 'PERMISSION_') {
				$types[strtolower(substr($name,11))] = $value;
			}
		}
		
		
		return $types;
		
	}
	
}