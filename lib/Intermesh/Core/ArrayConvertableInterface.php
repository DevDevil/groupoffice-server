<?php
namespace Intermesh\Core;
/**
 * ArrayConvertableInterface
 * 
 * Object must implement a toArray function so they can be converted into an 
 * array for JSON output.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
interface ArrayConvertableInterface {
	
	/**
	 * Convert this model to an array for JSON output
	 * 
	 * @param array $attributes
	 * @return array
	 */
	public function toArray(array $attributes = []);
}