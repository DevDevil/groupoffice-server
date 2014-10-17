<?php
namespace Intermesh\Modules\Modules\Model;

use Intermesh\Modules\Auth\Model\AbstractRole;

/**
 * @var int $roleId
 * @var int $moduleId
 * @var bool $useAccess 
 * @var bool $createAccess
 */
class ModuleRole extends AbstractRole{	
	
	const useAccess = 'useAccess';
	
	const createAccess = 'createAccess';	
	
	public static function resourceKey() {
		return 'moduleId';
	}	
	
	protected static function defineRelations(\Intermesh\Core\Db\RelationFactory $r) {
		$relations = parent::defineRelations($r);
		
		$relations[] = $r->belongsTo('module', Module::className(), 'moduleId');
		
		return $relations;
	}
}