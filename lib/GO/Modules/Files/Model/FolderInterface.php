<?php
namespace GO\Modules\Files\Model;

interface FolderInterface extends FileSystemObjectInterface{
	
	public function getChildren($includeFiles = true, $includeFolders = true);
	
	public function getParent();
	
	public function move($destinationFolder);
}