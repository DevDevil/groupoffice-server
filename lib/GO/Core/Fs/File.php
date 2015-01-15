<?php
namespace GO\Core\Fs;

use GO\Core\App;
use GO\Core\Fs\FileSystemObject;
use GO\Core\Fs\Folder;
use GO\Core\Util\String;

use Exception;



/**
 * A file object
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class File extends FileSystemObject {
	
	
	/**
	 * Get the parent folder object
	 *
	 * @return Folder Parent folder object
	 */
	public function getFolder() {
		$parentPath = dirname($this->path);		
		return new Folder($parentPath);
	}

	/**
	 * Get a unique temporary file.
	 *
	 * @param string $filename
	 * @param string $extension
	 * @return File
	 */
	public static function tempFile($filename = '', $extension = '') {
		$folder = App::config()->getTempFolder();

		if (!empty($filename)) {
			$p = $folder->getPath() . '/' . $filename;
		} else {
			$p = $folder->getPath() . '/' . uniqid(time());
		}

		if (!empty($extension)) {
			$p.='.' . $extension;
		}

		$file = new static($p);

		$file->delete();

		return $file;
	}

	/**
	 * Check if this is a temporary file.
	 *
	 * @return boolean
	 */
	public function isTempFile() {
		return $this->parent->isSubFolderOf(App::config()->getTempFolder());
	}

	/**
	 * Get the size formatted nicely like 1.5 MB
	 *
	 * @param int $decimals
	 * @return string
	 */
	public function humanSize($decimals = 1) {
		$size = $this->getSize();
		if ($size == 0) {
			return 0;
		}

		switch ($size) {
			case ($size > 1073741824) :
				$size = \GO\Core\Util\Number::localize($size / 1073741824, $decimals);
				$size .= " GB";
				break;

			case ($size > 1048576) :
				$size = \GO\Core\Util\Number::localize($size / 1048576, $decimals);
				$size .= " MB";
				break;

			case ($size > 1024) :
				$size = \GO\Core\Util\Number::localize($size / 1024, $decimals);
				$size .= " KB";
				break;

			default :
				$size = \GO\Core\Util\Number::localize($size, $decimals);
				$size .= " bytes";
				break;
		}
		return $size;
	}

	/**
	 * Delete the file
	 *
	 * @return boolean
	 */
	public function delete() {

		if (!file_exists($this->path)) {
			return true;
		}else{		
			return unlink($this->path);
		}
	}


	/**
	 * Get the extension of a filename
	 *
	 * @param string $filename
	 * @return string
	 */
	public function getExtension() {
		
//		if(!isset($filename)){
			$filename = $this->getName();
//		}
		
		$extension = '';

		$pos = strrpos($filename, '.');
		if ($pos) {
			$extension = substr($filename, $pos + 1);
		}
		//return trim(strtolower($extension)); // Does not work when extension on disk is in capital letters (.PDF, .XLSX)
		return trim($extension);
	}

	/**
	 * Get the file name with out extension
	 * @return String
	 */
	public function getNameWithoutExtension() {
		$filename = $this->getName();
		$pos = strrpos($filename, '.');
		
		if ($pos) {
			$filename = substr($filename, 0, $pos);
		}
		
		return $filename;
	}

	/**
	 * Checks if a filename exists and renames it.
	 *
	 * @param	string $filepath The complete path to the file
	 * @access public
	 * @return string  New filepath
	 */
	public function appendNumberToNameIfExists() {
		$dir = $this->getFolder()->getPath();
		$origName = $this->getNameWithoutExtension();
		$extension = $this->extension();
		$x = 1;
		while ($this->exists()) {
			$this->path = $dir . '/' . $origName . ' (' . $x . ').' . $extension;
			$x++;
		}
		return $this->path;
	}

	/**
	 * Put data in the file. (See php function file_put_contents())
	 *
	 * @param string $data
	 * @param type $flags
	 * @param type $context
	 * @return boolean
	 */
	public function putContents($data, $flags = null, $context = null) {
		if (file_put_contents($this->path, $data, $flags, $context)) {
			$this->setDefaultPermissions();
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the contents of this file.
	 *
	 * @return string
	 */
	public function getContents() {
		return file_get_contents($this->getPath());
	}


	/**
	 * Returns the mime type for the file.
	 * If it can't detect it it will return application/octet-stream
	 *
	 * @todo rename to contentType
	 * @return String
	 */
	public function getContentType() {
//		$types = file_get_contents(App::config()->root_path . 'mime.types');
//
//		if ($this->extension() != '') {
//
//			$pos = stripos($types, ' ' . $this->extension());
//
//			if ($pos) {
//				$pos++;
//
//				$start_of_line = \GO\Core\Util\String::rstrpos($types, "\n", $pos);
//				$end_of_mime = strpos($types, ' ', $start_of_line);
//				$mime = substr($types, $start_of_line + 1, $end_of_mime - $start_of_line - 1);
//
//				return $mime;
//			}
//		}

		//if($this->exists()){ Don't use exists function becuase MemoryFile returns true but it does not exist on disk
		if (file_exists($this->getPath())) {
			if (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				$mimetype = finfo_file($finfo, $this->getPath());
				finfo_close($finfo);
				return $mimetype;
			} elseif (function_exists('mime_content_type')) {
				return mime_content_type($this->getPath());
			}
		}

		return 'application/octet-stream';
	}

	/**
	 * Check if the file is an image.
	 *
	 * @return boolean
	 */
	public function isImage() {
		switch ($this->extension()) {
			case 'ico':
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':
			case 'xmind':
			case 'svg':

				return true;
			default:
				return false;
		}
	}

	/**
	 * Output the contents of this file to standard out (browser).
	 */
	public function output() {
		@ob_clean();
		@flush();

		//readfile somehow caused a memory exhausted error. This stopped when I added
		//ob_clean and flush above, but the browser hung with presenting the download
		//dialog until the entire download was completed.
		//The code below seems to work better.
		//
		//readfile($this->path());

		$handle = fopen($this->getPath(), "rb");

		if (!is_resource($handle)) {
			throw new Exception("Could not read file");
		}

		while (!feof($handle)) {
			echo fread($handle, 1024);
			flush();
		}
	}
	
	/**
	 * open file pointer
	 * 
	 * See php fopen function
	 * 
	 * @param string $mode
	 * @return resource
	 */
	public function open($mode){
		return fopen($this->getPath(), $mode);
	}

	/**
	 * Move a file to another folder.
	 *
	 * @param File $destination The file may not exist yet.
	 * @return boolean
	 */
	public function move(File $destination) {

		if($destination->exists()){
			throw new Exception("File exists in move!");
		}
		
		if (rename($this->path, $destination->getPath())) {
			$this->path = $destination->getPath();
			return true;
		}else{		
			return false;
		}
	}

	/**
	 * Copy a file to another folder.
	 *
	 * @param File $destinationFile
	 * @return File
	 */
	public function copy(File $destinationFile) {
	
		if (!copy($this->path, $destinationFile->getPath())) {
			return false;
		}else{

			$file = new File($destinationFile->getPath());
			$file->setDefaultPermissions();

			return $file;
		}
	}

	/**
	 * Convert and clean the file to ensure it has valid UTF-8 data.
	 *
	 * @return boolean
	 */
	public function convertToUtf8() {

		if (!$this->isWritable()){
			return false;
		}

		$str = $this->getContents();
		if (!$str) {
			return false;
		}

		$enc = $this->detectEncoding($str);
		if (!$enc) {
			$enc = 'UTF-8';
		}

		$bom = pack("CCC", 0xef, 0xbb, 0xbf);
		if (0 == strncmp($str, $bom, 3)) {
			//echo "BOM detected - file is UTF-8\n";
			$str = substr($str, 3);
		}

		return $this->putContents(String::cleanUtf8($str, $enc));
	}

	/**
	 * Get the md5 hash from this file
	 *
	 * @return string
	 */
	public function getMd5Hash() {
		return md5_file($this->path);
	}

	/**
	 * Compare this file with an other file.
	 *
	 * @param File $file
	 * @return bool True if the file is different, false if file is the same.
	 */
	public function equals(File $file) {
		if ($this->md5Hash() != $file->md5Hash()){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Create the file
	 *
	 * @param boolean $createPath Create the folders for this file also?
	 * @return self|bool $successfull
	 */
	public function touch($createPath = false) {
		if ($createPath){
			$this->getFolder()->create();
		}

		if (touch($this->getPath())) {
			return $this;
		} else {
			return false;
		}
	}

	/**
	 * Get the end of a text file.
	 *
	 * @param int $lines Number of lines
	 * @return string
	 */
	public function tail($lines = 20) {
		//global $fsize;
		$handle = fopen($this->getPath(), "r");
		$linecounter = $lines;
		$pos = -2;
		$beginning = false;
		$text = array();
		while ($linecounter > 0) {
			$t = " ";
			while ($t != "\n") {
				if (fseek($handle, $pos, SEEK_END) == -1) {
					$beginning = true;
					break;
				}
				$t = fgetc($handle);
				$pos --;
			}
			$linecounter --;
			if ($beginning) {
				rewind($handle);
			}
			$text[$lines - $linecounter - 1] = fgets($handle);
			if ($beginning)
				break;
		}
		fclose($handle);
		return implode("", array_reverse($text));
	}

}
