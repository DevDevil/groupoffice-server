<?php
namespace GO\Core\Modules\Model;

use GO\Core\Auth\Model\AbstractRole;

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
	
	protected static function defineRelations(\GO\Core\Db\RelationFactory $r) {
		$relations = parent::defineRelations($r);
		
		$relations[] = $r->belongsTo('module', Module::className(), 'moduleId');
		
		return $relations;
	}
}