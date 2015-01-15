<?php
namespace GO\Modules\Files\Model;

interface FileInterface extends FileSystemObjectInterface{	

	public function move($destinationFile);
	
	public function putContents($data);
	
	public function getContents();
	
	public function getContentType();
	
	public function getSize();
	
	public function getFolder();
	
}