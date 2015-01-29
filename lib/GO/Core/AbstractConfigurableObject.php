<?php

namespace GO\Core;

/**
 * The abstract configurable object class.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class AbstractConfigurableObject {
	
	/**
	 * The moduleName property that needs to be used for these config variables.
	 * 
	 * @var string 
	 */
	protected $moduleName;
	
	public function __set($name, $value) {
		$model = Model\Config::findByPk(['moduleName'=>$this->moduleName,'name'=>$name]);
		if (!$model) {
			$model = new Model\Config;
			$model->name = $name;
			$model->moduleName = $this->moduleName;
		}

		$model->value = $value;

		$success = $model->save();

		return $success !== false;
	}

	public function __get($name) {
		$model = Model\Config::findByPk(['moduleName'=>$this->moduleName,'name'=>$name]);

		return $model ? $model->value : null;
	}

	public function __isset($name) {
		$model = Model\Config::findByPk(['moduleName'=>$this->moduleName,'name'=>$name]);
		return $model !== false;
	}

	public function __unset($name) {
		$model = Model\Config::findByPk(['moduleName'=>$this->moduleName,'name'=>$name]);
		if ($model) {
			$model->delete();
		}
	}

}
