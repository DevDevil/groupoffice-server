Creating permissions
====================

See also the API docs: http://intermesh.io/php/docs/class-Intermesh.Modules.Auth.Model.RecordPermissionTrait.html

1. Create an abstractRole model table:

	``````````````````````````````````````````````````````````````````````````````````````````````````
	CREATE TABLE IF NOT EXISTS `coreModuleRole` (
	  `moduleId` int(11) NOT NULL,
	  `roleId` int(11) NOT NULL,
	  `useAccess` tinyint(1) NOT NULL DEFAULT '0',
	  `createAccess` tinyint(1) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`moduleId`,`roleId`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

	ALTER TABLE `coreModuleRole` ADD FOREIGN KEY ( `moduleId` ) REFERENCES `ipe`.`coreModule` (
	`id`
	) ON DELETE CASCADE ON UPDATE RESTRICT ;

	ALTER TABLE `coreModuleRole` ADD FOREIGN KEY ( `roleId` ) REFERENCES `ipe`.`authRole` (
	`id`
	) ON DELETE CASCADE ON UPDATE RESTRICT ;

	``````````````````````````````````````````````````````````````````````````````````````````````````


2. Create Module record that uses the trait:

	``````````````````````````````````````````````````````````````````````````````````````````````````
	<?php
	namespace Intermesh\Modules\Modules\Model;

	use Intermesh\Core\Db\AbstractRecord;
	use Intermesh\Modules\Auth\Model\RecordPermissionTrait;

	class Module extends AbstractRecord{
	
		use RecordPermissionTrait;
	
		public $ownerUserId = 1;
	
		protected static function defineRelations(\Intermesh\Core\Db\RelationFactory $r) {
			return [
				$r->belongsTo('group', ModuleGroup::className(), 'groupId'), 
				$r->hasMany('roles', ModuleRole::className(), 'moduleId')
				];
		}
	}
	``````````````````````````````````````````````````````````````````````````````````````````````````
