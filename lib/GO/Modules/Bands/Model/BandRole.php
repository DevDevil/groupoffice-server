<?php
namespace GO\Modules\Bands\Model;

use GO\Core\Auth\Model\AbstractRole;

/**
 * The band roles model
 * 
 * @property int $roleId
 * @property int $bandId
 * @property bool $readAccess  
 * @property bool $editAccess
 */
class BandRole extends AbstractRole {
	
	/**
	 * Allow read access to the role
	 */
	const PERMISSION_READ = 0;
	
	/**
	 * Allow write access to the role
	 */
	const PERMISSION_WRITE = 1;	
	
	/**
	 * Allow delete access to the role
	 */
	const PERMISSION_DELETE = 2;
	
	
	protected static function defineResource(){
		return self::belongsTo('band', Band::className(), 'bandId');
	}
}