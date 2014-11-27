<?php
namespace Intermesh\Modules\System\Controller;

use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractRESTController;
use PDOException;

/**
 * Perform system check
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class CheckController extends AbstractRESTController{

	

	/**
	 * Run system tests
	 */
	public function httpGet(){

		echo "<h1>".App::config()->productName." system test</h1>";

		$this->_check(
						"PHP version",
						function(){
							if(version_compare(phpversion(), "5.4", ">=")){
								return true;
							}else
							{
								return "Your PHP version is too old :(";
							}
						});

		$this->_check(
						"Database connection",
						function(){
							try{
								$conn = App::dbConnection()->getPDO();
								return true;
							}catch(PDOException $e){
								return "Couldn't connect to database. Please check the config. PDO Exception: ".$e->getMessage();
							}
						});

		$this->_check(
						"Temp folder",
						function(){
							$file = App::config()->getTempFolder()->createFile("test.txt");

							if($file->touch()){

								$file->delete();

								return true;
							}else
							{
								return "'".App::config()->getTempFolder()."' is not writable!";
							}
						});

		$this->_check(
						"Data folder",
						function(){

							$folder = App::config()->getDataFolder();

							if(!$folder->exists()){
								return '"'.$folder.'" doesn\'t exist';
							}

							$file = $folder->createFile("test.txt");

							if($file->touch()){

								$file->delete();

								return true;
							}else
							{
								return "'".$folder."' is not writable!";
							}
						});

	}

	private function _check($testName, $function){
		$html = '<p>'.$testName.': <span style="';

		$result = $function();

		if($result===true){
			$html .= 'color:green">OK';
		}else
		{
			$html .= 'color:red">ERROR: '.$result;
		}

		$html .= '</span></p>';

		echo $html;
	}
}