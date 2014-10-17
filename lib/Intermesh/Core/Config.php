<?php
namespace Intermesh\Core;

use Intermesh\Core\Fs\Folder;

/**
 * Config class with all configuration options. It can configure all objects
 * that extend the AbstractObject class.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class Config {

	/**
	 * Name of the application
	 *
	 * @var string
	 */
	public $productName = 'Intermesh Framework';

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
	 * Get path to library
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
}