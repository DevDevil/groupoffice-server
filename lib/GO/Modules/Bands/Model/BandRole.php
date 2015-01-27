<?php
namespace GO\Modules\Bands\Model;

use GO\Core\Auth\Model\AbstractRole;

/**
 * The band roles model
 * 
 * @var int $roleId
 * @var int $moduleId
 * @var bool $readAccess 
 * @var bool $editAccess
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