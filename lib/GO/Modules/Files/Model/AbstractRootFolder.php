<?php
namespace GO\Modules\Files\Model;

use GO\Core\Exception\Forbidden;

/**
 * Any folder that extends this interface will appear in the root
 */
abstract class AbstractRootFolder implements FolderInterface {
	public function createFile($name) {
		throw new Forbidden();
	}

	public function delete() {
		throw new Forbidden();
	}

	public function getParentFolder() {
		return false;
	}

	public function hasReadPermission() {
		return true;
	}

	public function hasWritePermission() {
		return false;
	}

	public function move($destinationFolder) {
		throw new Forbidden();
	}

	public function setName($name) {
		throw new Forbidden();
	}

}