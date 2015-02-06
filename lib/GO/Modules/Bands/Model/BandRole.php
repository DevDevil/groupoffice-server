<?php
namespace GO\Modules\Bands\Model;

use GO\Core\Auth\Model\AbstractRole;

/**
 * The band roles model
 * 
 * @property int $roleId
 * @property int $bandId
 * @property int $permissionType
 */
class BandRole extends AbstractRole {
	protected static function defineResource(){
		return self::belongsTo('band', Band::className(), 'bandId');
	}
}