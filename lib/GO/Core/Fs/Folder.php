<?php
namespace GO\Core\Fs;

use Exception;

/**
 * A folder object
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Folder extends FileSystemObject {

	/**
	 * Get folder directory listing.
	 *
	 * @param boolean $includeFiles
	 * @param boolean $includeFolders
	 * 
	 * @return File[]|Folder[]
	 */
	public function getChildren($includeFiles = true, $includeFolders = true) {
		if (!$dir = opendir($this->path)) {
			return false;
		}

		$children = [];
		while ($item = readdir($dir)) {
			
			$folderPath = $this->path . '/' . $item;
			
			if ($item !== "." && $item !== "..") {

				if (is_file($folderPath)) {
					if (!$includeFiles) {
						continue;
					}
					$o = new File($folderPath);
				} else {

					if (!$includeFolders) {
						continue;
					}

					$o = new Folder($folderPath);
				}
				$children[] = $o;				
			}
		}

		closedir($dir);

		return $children;
	}
	
	/**
	 * Get the parent folder object
	 *
	 * @return Folder Parent folder object
	 */
	public function getParent() {

		$parentPath = dirname($this->path);
		if ($parentPath == $this->path) {
			return false;
		}

		return new Folder($parentPath);
	}

	/**
	 * Delete the folder
	 *
	 * @return boolean
	 */
	public function delete() {
		if (!$this->exists()){
			return true;
		}

		//just delete symlink and not contents of linked folder!
		if (is_link($this->path)) {
			return unlink($this->path);
		}

		$items = $this->getChildren(true);

		foreach ($items as $item) {
			if (!$item->delete()) {
				return false;
			}
		}

		return !is_dir($this->path) || rmdir($this->path);
	}
	/**
	 * Move the folder to a new location
	 *
	 * @param Folder $destinationFolder This folder may not exist
	 */
	public function move(Folder $destinationFolder) {
		if (!$this->exists()) {
			throw new Exception("Folder '" . $this->getPath() . "' does not exist");
		}

		if ($destinationFolder->exists()) {
			throw new Exception("Destination folder already exists!");
		}

//		if(is_link($this->path)){
//			$link = new File($this->path);
//			return $link->move(new File($destinationFolder->path()));
//		}

		if($destinationFolder->isChildOf($this)){
			throw new Exception("Can't move this folder into a child folder!");
		}

		$newPath = $destinationFolder->getPath();

		//do nothing if path is the same.
		if ($newPath === $this->getPath()) {
			return true;
		}

		if (!@rename($this->getPath(), $newPath)) { // Notice suppressed by @
			//	throw new Exception("Rename failed");
			// If renaming is throwing an error then do it the old way.
			// This is done because of problems when moving items across partitions.
			// See https://bugs.php.net/bug.php?id=50676 for more info about this.
			// If rename fails then try the old method
			$movedFolder = new Folder($newPath);
			$movedFolder->create();

			$ls = $this->getChildren(true);
			foreach ($ls as $fsObject) {
				if ($fsObject->isFile()) {
					$fsObject->move($movedFolder->createFile($fsObject->getName()));
				} else {
					$fsObject->move($movedFolder->createFolder($fsObject->getName()));
				}
			}

			$this->delete();

			$newPath = $movedFolder->getPath();
		}

		$this->path = $newPath;

		return true;
	}

	/**
	 * Copy a folder to a new location
	 *
	 * @param Folder $destinationFolder This folder may not exist
	 * @return Folder
	 */
	public function copy(Folder $destinationFolder) {
		
		if($destinationFolder->isChildOf($this)){
			throw new Exception("Can't copy this folder into a child folder!");
		}

		if ($destinationFolder->exists()) {
			throw new Exception("Destination folder already exists!");
		}

		$copiedFolder = new Folder($destinationFolder->getPath());
		if (!$destinationFolder->create()) {
			throw new Exception("Could not create " . $destinationFolder->getPath());
		}

		$ls = $this->getChildren(true);
		foreach ($ls as $fsObject) {
			if ($fsObject->isFolder()) {
				//$newDestinationFolder= new Folder($destinationFolder->path().'/'.$this->name());
				$fsObject->copy($copiedFolder->createFolder($fsObject->getName()));
			} else {
				$fsObject->copy($copiedFolder->createFolder($fsObject->getName()));
			}
		}

		return $copiedFolder;
	}

	/**
	 * Create the folder
	 *
	 * @param int $permissionsMode <p>
	 * Note that mode is not automatically
	 * assumed to be an octal value, so strings (such as "g+w") will
	 * not work properly. To ensure the expected operation,
	 * you need to prefix mode with a zero (0):
	 * </p>
	 *
	 * @return self|boolean
	 */
	public function create($permissionsMode = null) {

		if (!isset($permissionsMode)) {
			$permissionsMode = isset($this->createMode) ? $this->createMode : 0755;
		}

		if (is_dir($this->path)) {
			return $this;
		}

		if (mkdir($this->path, $permissionsMode, true)) {
			if (isset($this->changeGroup)) {
				chgrp($this->path, $this->changeGroup);
			}

			return $this;
		} else {
			throw new Exception("Could not create folder " . $this->path);
		}
	}

	/**
	 * Create a symbolic link in this folder
	 *
	 * @param Folder $target
	 * @param string $linkName optional link name. If omitted the name will be the same as the target folder name
	 * @return File
	 * @throws Exception
	 */
	public function createLink(Folder $target, $linkName = null) {

		if (!isset($linkName)) {
			$linkName = $target->getName();
		}

		$link = $this->createFile($linkName);
		if ($link->exists()) {
			throw new Exception("Path " . $link->getPath() . " already exists");
		}

		if (symlink($target->getPath(), $link->getPath())) {
			return $link;
		} else {
			throw new Exception("Failed to create link " . $link->getPath() . " to " . $target->getPath());
		}
	}

	/**
	 * Checks if a filename exists and renames it.
	 *
	 * @param string $filepath The complete path to the file
	 * @return string  New filepath
	 */
	public function appendNumberToNameIfExists() {
		$origPath = $this->path;
		$x = 1;
		while ($this->exists()) {
			$this->path = $origPath . ' (' . $x . ')';
			$x++;
		}
		return $this->path;
	}

	/**
	 * Calculate size of the directory in bytes.
	 *
	 * @return int/false
	 */
	public function calculateSize() {
		$cmd = 'du -sb "' . $this->path . '" 2>/dev/null';

		$io = popen($cmd, 'r');

		if ($io) {
			$size = fgets($io, 4096);
			$size = preg_replace('/[\t\s]+/', ' ', trim($size));
			$size = substr($size, 0, strpos($size, ' '));

			return $size;
		} else {
			return false;
		}
	}

}
