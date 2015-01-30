<?php

namespace GO\Modules\Upload\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;
use GO\Core\Exception\Forbidden;
use GO\Core\Fs\File;
use GO\Core\Util\Image;

/**
 * Trait to implement a thumbnail controller action.
 * 
 * <p>For example put this code in your controller:</p>
 * 
 * <code>
 * 	use ThumbControllerTrait;	

	protected function thumbGetFolder() {
		return App::session()->getTempFolder();
	}

	protected function thumbUseCache() {
		return false;
	}
 * </code>
 * 
 * @see FlowController
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class AbstractThumbController extends AbstractController {

	/**
	 * Get the relative folder the image should be fetched from.
	 * Be careful, images in this folder are available to anyone that can access this controller.
	 * 
	 * @return File
	 */
	abstract protected function thumbGetFile();
	
	
	/**
	 * Return true if you want to enable thumbnail caching. This is recommended.
	 * 
	 * @return bool
	 */
	abstract protected function thumbUseCache();


	/**
	 * Image thumbnailer.
	 *
	 * @param string $src Relative path to the image from folder returned in $this->thumbGetFolder();
	 * @param int $w
	 * @param int $h
	 * @param bool $zoomCrop
	 * @param bool $fitBox
	 */
	protected function actionDownload($w = 0, $h = 0, $zoomCrop = false, $fitBox = false) {

		App::session()->closeWriting();

		try{
			$file = $this->thumbGetFile();
		}catch(Forbidden $e){
			App::request()->redirect('https://www.placehold.it/'.$w.'x'.$h.'/EFEFEF/AAAAAA&text=Forbidden');
		}
		
		if($w == 0 && $h == 0){
			
			//output original
			$this->_thumbHeaders(false, $file);
			$file->output();
			exit();
		}
		
		

		$useCache = $this->thumbUseCache();


		if (!$file || !$file->exists()) {
			App::request()->redirect('https://www.placehold.it/'.$w.'x'.$h.'/EFEFEF/AAAAAA&text=No+image');
		}

		if ($file->getSize() > 4 * 1024 * 1024) {
			App::request()->redirect('https://www.placehold.it/'.$w.'x'.$h.'/EFEFEF/AAAAAA&text=Image+too+large');
		}
		
		
//		if($file->getExtension() ==='svg'){
//			header('Content-Type: image/svg+xml');
//			header('Content-Disposition: inline; filename="' . $file->getName() . '"');
//			header('Content-Transfer-Encoding: binary');
//			
//			$file->output();
//			exit();
//		}
		

		$cacheDir = App::config()->getTempFolder()->createFolder('thumbcache')->create();

		if (!$useCache) {
			$cacheFilename = str_replace(array('/', '\\'), '_', $file->getFolder()->getPath() . '_' . $w . '_' . $h);
			if ($zoomCrop) {
				$cacheFilename .= '_zc';
			}

			if ($fitBox) {
				$cacheFilename .= '_fb';
			}

			$cacheFilename .= urlencode($file->getName());

			$readfile = $cacheDir->getPath() . '/' . $cacheFilename;
			$thumbExists = file_exists($cacheDir->getPath() . '/' . $cacheFilename);
			$thumbMtime = $thumbExists ? filemtime($cacheDir->getPath() . '/' . $cacheFilename) : 0;
		}

		if ($useCache || !$thumbExists || $thumbMtime < $file->getModifiedAt() || $thumbMtime < $file->getCreatedAt()) {
			$image = Image::newInstance($file->getPath());
			if (!$image) {
				App::request()->redirect('https://www.placehold.it/' + $image->getWidth() + 'x' + $image->getHeight() + '/EFEFEF/AAAAAA&text=Could+not+load+image');
			} else {
				if ($zoomCrop) {
					$success = $image->zoomcrop($w, $h);
				} else if ($fitBox) {
					$success = $image->fitBox($w, $h);
				} elseif ($w && $h) {
					$success = $image->resize($w, $h);
				} elseif ($w) {
					$success = $image->resizeToWidth($w);
				} else {
					$success = $image->resizeToHeight($h);
				}

				if (!$success) {
					App::request()->redirect('https://www.placehold.it/' + $image->getWidth() + 'x' + $image->getHeight() + '/EFEFEF/AAAAAA&text=Could+not+resize+image');
				}

				if (!$useCache) {

					$success = $image->save($readfile);

					if (!$success) {
						App::request()->redirect('https://www.placehold.it/' + $image->getWidth() + 'x' + $image->getHeight() + '/EFEFEF/AAAAAA&text=Could+not+resize+image');
					}

					$this->_thumbHeaders($useCache, $file);
					readfile($readfile);
				} else {
					$this->_thumbHeaders($useCache, $file);

					$image->output();
				}
			}
		} else {
			$this->_thumbHeaders($useCache, $file->getName());
			readfile($readfile);
		}
	}

	private function _thumbHeaders($useCache, File $file) {

		if ($useCache) {
			$this->cacheHeaders(new \DateTime('@'.$file->getModifiedAt()), $file->getMd5Hash());
		}
		
		header('Content-Type: '.$file->getContentType());
		header('Content-Disposition: inline; filename="' . $file->getName() . '"');
		header('Content-Transfer-Encoding: binary');
	}

}
