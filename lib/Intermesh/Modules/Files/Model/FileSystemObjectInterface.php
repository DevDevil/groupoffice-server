<?php
namespace Intermesh\Modules\Files\Model;

use Intermesh\Core\AbstractModel;

interface FileSystemObjectInterface {
	
	public function __construct($path);
	
	public function getName();
	
	public function rename($name);
	
	public function getPath();
	
	public function delete();
	
	public function getModifiedAt();
	
	public function getCreatedAt();
	
	public function isWritable();
	
	public function isReadable();
	
}