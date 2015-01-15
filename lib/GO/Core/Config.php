<?php
namespace GO\Core;

use GO\Core\Fs\Folder;

/**
 * Config class with all configuration options. 
 * 
 * It can configure all objects that extend the AbstractObject class.
 * 
 * You can also set any value to it and it will be stored in the database.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Config {

	/**
	 * Name of the application
	 *
	 * @var string
	 */
	public $productName = 'GO Framework';
        
  /**
	 * Set the default timezone for PHP.
	 * 
	 * @var string 
	 */
	public $defaultTimeZone;

	/**
	 * Temporary files folder to use. Defaults to the <system temp folder>/ifw
	 *
	 * @var string
	 */
	public $tempFolder;

	/**
	 * Data folder to store permanent files. Defaults to "/home/ifw"
	 *
	 * @var string
	 */
	public $dataFolder = '/home/ifw';

	/**
	 * Configuration for all objects that extend AbstractObject
	 *
	 * @see AbstractObject
	 * @var array
	 */
	public $classConfig = [];
	
	/**
	 * The composer class loader
	 * 
	 * @var \Composer\Autoload\ClassLoader 
	 */
	public $classLoader;

	/**
	 * Set's class config options.
	 *
	 * More information in App::init();
	 *
	 * @see App::init()
	 * @param array $config
	 */
	public function setConfig(array $config) {
		$this->classConfig = $config;

		$className = get_class($this);

		if (isset($this->classConfig[$className])) {
			foreach ($this->classConfig[$className] as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * Get path to lib/GO
	 *
	 * @return string
	 */
	public function getLibPath() {
		return realpath(dirname(__FILE__) . '/../');
	}

	/**
	 * Get temporary files folder
	 *
	 * @return Folder
	 */
	public function getTempFolder($autoCreate = true) {

		if (!isset($this->tempFolder)) {
			$this->tempFolder = sys_get_temp_dir() . '/ifw/';
		}

		$folder = new Folder($this->tempFolder);

		if ($autoCreate) {
			$folder->create();
		}

		return $folder;
	}

	/**
	 * Get temporary files folder
	 *
	 * @return Folder
	 */
	public function getDataFolder() {
		return new Folder($this->dataFolder);
	}
	
	
	public function __set($name, $value) {
		$model = Model\Config::findByPk($name);
		if (!$model) {
			$model = new Model\Config;
			$model->name = $name;
		}
		
		$model->value = $value;	

		$success = $model->save();	
		
		return $success !== false;
	}
	
	public function __get($name) {
		$model = Model\Config::findByPk($name);
		
		return $model ? $model->value : null;
	}
	
	public function __isset($name) {
		$model = Model\Config::findByPk($name);
		return $model !== false;
	}
	
	public function __unset($name) {
		$model = Model\Config::findByPk($name);
		if($model){
			$model->delete();
		}
	}
}