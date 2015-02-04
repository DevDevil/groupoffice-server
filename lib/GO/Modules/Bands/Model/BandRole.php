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
class BandRole extends AbstractRole{	
	
	/**
	 * The column pointing to the bands table
	 * 
	 * @return string
	 */
	public static function resourceKey() {
		return 'bandId';
	}
}