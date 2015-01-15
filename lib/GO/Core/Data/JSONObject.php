<?php

namespace GO\Core\Data;

/**
 * JSON Object
 * 
 * An object that will convert to JSON when echo'd. the JSON view returns this
 * object so it can be modified in the controller afterwards.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class JSONObject implements \ArrayAccess {

	/**
	 * The json data in an array
	 * @var array
	 */
	public $data;

	public function __construct($data = array()) {
		$this->data = $data;
	}

	public function __toString() {
		return json_encode($this->data);
	}

	/**
	 * 
	 * @todo We need to support php 5.3.3 so we can't get by reference here.
	 */
	public function offsetGet($offset) {
		return $this->data[$offset];
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->data[] = $value;
		} else {
			$this->data[$offset] = $value;
		}
	}

	public function offsetExists($offset) {
		return isset($this->data[$offset]);
	}

	public function offsetUnset($offset) {
		unset($this->data[$offset]);
	}

	public function mergeWith(JsonResponse $response) {
		$this->data = array_merge($this->data, $response->getData());
	}
}
