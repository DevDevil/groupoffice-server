<?php

namespace Intermesh\Core\Fs;

use Exception;
use Intermesh\Core\AbstractObject;
use Intermesh\Core\Util\String;

/**
 * Base class for files and folders on the filesystem
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class FileSystemObject extends AbstractObject {

	protected $path;

	/**
	 * Default file or folder create mode
	 *
	 * @var octal
	 */
	public $createMode;

	/**
	 * Default group for a new folder or file.
	 *
	 * @var string
	 */
	public $changeGroup;

	/**
	 * Disallow these chars for files and folders.
	 */
	const INVALID_CHARS = '/[\/:\*\?"<>|\\\]/';

	/**
	 * Constructor of a file or folder
	 *
	 * @param string $path The absolute path must be suplied
	 * @throws Exception
	 */
	public function __construct($path) {

		parent::__construct();

//		\App::debug("FS construct $path");

		if (empty($path)) {
			throw new Exception("Path may not be empty in Base");
		}

		//normalize path slashes
//		if(App::server()->isWindows())
//			$path=str_replace('\\','/', $path);


		if (!self::checkPathInput($path)) {
			throw new Exception("The supplied path '$path' was invalid");
		}

//		$parent = dirname($path);
//		if ($parent != '/') {
//			$this->path = $parent;
//		} else {
//			$this->path = '';
//		}
//
//		$this->path .= '/' . self::utf8Basename($path);
		
		$this->path = $path;
	}

	/**
	 * Create a folder or file from a path string
	 *
	 * @param string $path
	 * @return File|Folder
	 */
	public static function createFromPath($path) {
		if (is_file($path)) {
			return new File($path);
		} else {
			return new Folder($path);
		}
	}

	/**
	 * Set's default permissions and group ownership
	 */
	public function setDefaultPermissions() {

		if (isset($this->createMode)) {
			chmod($this->path, $this->createMode);
		}

		if (isset($this->changeGroup)) {
			chgrp($this->path, $this->changeGroup);
		}
	}	

	/**
	 * Get an existing child file or folder.
	 *
	 * @param string $relativePath
	 * @return File|Folder|boolean
	 */
	public function getChild($relativePath) {
		$childPath = $this->path . '/' . $relativePath;
		
		if (is_file($childPath)) {
			return new File($childPath);
		} elseif (is_dir($childPath)) {
			return new Folder($childPath);
		} else {
			return false;
		}
	}

	/**
	 * Create a new file object. Filesystem file is not created automatically.
	 *
	 * @param string $relativePath
	 * @return File
	 */
	public function createFile($relativePath) {
		$childPath = $this->path . '/' . $relativePath;

		return new File($childPath);
	}

	/**
	 * Create a new folder object. Filesystem folder is not created automatically.
	 *
	 * @param string $relativePath
	 * @return Folder
	 */
	public function createFolder($relativePath) {
		$childPath = $this->path . '/' . $relativePath;

		return new Folder($childPath);
	}

	/**
	 * Return absolute filesystem path
	 *
	 * @return String
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * Return the modification unix timestamp
	 *
	 * @return int Unix timestamp
	 */
	public function getModifiedAt() {
		return filemtime($this->path);
	}

	/**
	 * Return the creation unix timestamp
	 *
	 * @return int Unix timestamp
	 */
	public function getCreatedAt() {
		return filectime($this->path);
	}

	/**
	 * Filesize in bytes
	 *
	 * @return int Filesize in bytes
	 */
	public function getSize() {
		return filesize($this->path);
	}

	/**
	 * Get the name of this file or folder
	 *
	 * @return String
	 */
	public function getName() {

		if (!function_exists('mb_substr')) {
			return basename($this->path);
		}

		if (empty($this->path)) {
			return '';
		}
		$pos = mb_strrpos($this->path, '/');
		if ($pos === false) {
			return $this->path;
		} else {
			return mb_substr($this->path, $pos + 1);
		}
	}

	/**
	 * Check if the file or folder exists
	 * @return boolean
	 */
	public function exists() {
		return file_exists($this->path);
	}

	/**
	 * Check if the file or folder is writable for the webserver user.
	 *
	 * @return boolean
	 */
	public function isWritable() {
		return is_writable($this->path);
	}
	
	/**
	 * Check if the file or folder is readable for the webserver user.
	 *
	 * @return boolean
	 */
	public function isReadable() {
		return is_readable($this->path);
	}

	/**
	 * Change owner
	 * @param string $user
	 * @return boolean
	 */
	public function chown($user) {
		return chown($this->path, $user);
	}

	/**
	 * Change group
	 *
	 * @param string $group
	 * @return boolean
	 */
	public function chgrp($group) {
		return chgrp($this->path, $group);
	}

	/**
	 *
	 * @param int $permissionsMode <p>
	 * Note that mode is not automatically
	 * assumed to be an octal value, so strings (such as "g+w") will
	 * not work properly. To ensure the expected operation,
	 * you need to prefix mode with a zero (0):
	 * </p>
	 *
	 * @return boolean
	 */
	public function chmod($permissionsMode) {
		return chmod($this->path, $permissionsMode);
	}

	/**
	 * Delete the file
	 *
	 * @return boolean
	 */
	public function delete() {
		return false;
	}

	public function __toString() {
		return $this->path;
	}

	/**
	 * Checks if a path send as a request parameter is valid.
	 *
	 * @param String $path
	 * @return boolean
	 */
	public static function checkPathInput($path) {
		$path = '/' . str_replace('\\', '/', $path);
		return strpos($path, '/../') === false;
	}

	/**
	 * Get's the filename from a path string and works with UTF8 characters
	 *
	 * @param String $path
	 * @return String
	 */
	public static function utf8Basename($path) {
		if (!function_exists('mb_substr')) {
			return basename($path);
		}
		//$path = trim($path);
		if (substr($path, -1, 1) == '/') {
			$path = substr($path, 0, -1);
		}
		if (empty($path)) {
			return '';
		}
		$pos = mb_strrpos($path, '/');
		if ($pos === false) {
			return $path;
		} else {
			return mb_substr($path, $pos + 1);
		}
	}

	/**
	 * Remove unwanted characters from a string so it can safely be used as a filename.
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function stripInvalidChars($filename, $replace = '') {
		$filename = trim(preg_replace(self::INVALID_CHARS, $replace, $filename));

		//IE likes to change a double white space to a single space
		//We must do this ourselves so the filenames will match.
		$filename = preg_replace('/\s+/', ' ', $filename);

		//strip dots from start
		$filename = ltrim($filename, '.');

		if (empty($filename)) {
			$filename = 'unnamed';
		}


		if (strlen($filename) > 255) {
			$filename = substr($filename, 0, 255);
		}

		return $filename;
	}

	/**
	 * Check if this object is a folder.
	 *
	 * @return boolean
	 */
	public function isFolder() {
//		return is_dir($this->path);
		return is_a($this, "\Intermesh\Core\Fs\Folder"); //works with non existing files
	}

	/**
	 * Check if this object is a file.
	 *
	 * @return boolean
	 */
	public function isFile() {
//		return is_file($this->path);
		return is_a($this, "\Intermesh\Core\Fs\File"); //works with non existing files
	}

	/**
	 * Rename a file or folder
	 *
	 * @param String $name
	 * @return boolean
	 */
	public function rename($name) {
		$oldPath = $this->path;
		$newPath = dirname($this->path) . '/' . $name;

		if (rename($oldPath, $newPath)) {
			$this->path = $newPath;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if the given folder is a subfolder of this folder.
	 *
	 * @param Folder $parent
	 * @return boolean
	 */
	public function isChildOf(Folder $parent) {
		return strpos($this->getPath(), $parent->getPath() . '/') === 0;
	}

	/**
	 * Get's the relative path from a given parent folder
	 * 
	 * @param Folder $fromFolder
	 * @return string|boolean
	 */
	public function getRelativePath(Folder $fromFolder) {
		if (!$this->isChildOf($fromFolder)) {
			return false;
		} else {
			return substr($this->getPath(), strlen($fromFolder->getPath()) + 1);
		}
	}

}
