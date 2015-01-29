<?php

namespace GO\Core\Modules\Model;

use GO\Core\AbstractConfigurableObject;

/**
 * Module config model
 * 
 * Model to store system wide configuration values
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ModuleConfig extends AbstractConfigurableObject {

	/**
	 * Constructor for the moduleConfig class.
	 * 
	 * @param string $moduleName
	 */
	public function __construct($moduleName) {
		$this->moduleName = $moduleName;
	}
}