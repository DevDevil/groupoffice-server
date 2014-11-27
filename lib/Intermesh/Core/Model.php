<?php

namespace Intermesh\Core;

use ReflectionClass;
use ReflectionProperty;

/**
 * The abstract model class. It implements validation.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class Model extends AbstractObject {

	private $_validationErrors = [];

	/**
	 * You can override this function to implement validation in your model.
	 * 
	 * @return boolean
	 */
	public function validate() {
		return !$this->hasValidationErrors();
	}

	/**
	 * Return all validation errors of this model
	 * 
	 * @return array 
	 */
	public function getValidationErrors() {
		return $this->_validationErrors;
	}

	/**
	 * Get the validationError for the given attribute
	 * If the attribute has no error then fals will be returned
	 * 
	 * @param string $key
	 * @return array eg. array('code'=>'maxLength','info'=>array('length'=>10)) 
	 */
	public function getValidationError($key) {
		$validationErrors = $this->getValidationErrors();
		if (!empty($validationErrors[$key])) {
			return $validationErrors[$key];
		} else {
			return false;
		}
	}

	/**
	 * Set a validation error for the given field.
	 * If the error key is equal to a model attribute name, the view can render 
	 * an error on the associated form field.
	 * The key for an error must be unique.
	 * 
	 * @param string $key 
	 * @param string $code  Code for the client. eg. "required" or "maxLength"
	 */
	protected function setValidationError($key, $code, $info = array()) {
		$this->_validationErrors[$key] = array('code' => $code, 'info' => $info);
	}

	/**
	 * Returns a value indicating whether there is any validation error.
	 * @param string $key attribute name. Use null to check all attributes.
	 * @return boolean whether there is any error.
	 */
	public function hasValidationErrors($key = null) {
		$validationErrors = $this->getValidationErrors();

		if ($key === null) {
			return count($validationErrors) > 0;
		} else {
			return isset($validationErrors[$key]);
		}
	}

	/**
	 * Convert this model to an array for JSON output
	 * 
	 * @param array $attributes
	 * @return array
	 */
	public function toArray(array $attributes = []) {

		$arr = ['attributes' => []];

		if (empty($attributes) || ($asteriskKey = array_search('*', $attributes)) !== false) {
			
			if(isset($asteriskKey)){
				unset($attributes[$asteriskKey]);
			}
			
			$reflection = new ReflectionClass($this);
			
			$props = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
			foreach($props as $prop){
				
				if(!$prop->isStatic()){
					$attributes[] = $prop->getName();					
				}
			}
		} 
		
		foreach ($attributes as $attribute) {

			$value = $this->$attribute;

			if(is_object($value) && method_exists($value, 'toArray')){
				$value = $value->toArray();
			}
			
			if(is_array($value)){
				if(isset($value[0]) && is_object($value[0]) && method_exists($value[0], 'toArray')){
					
					$arr['attributes'][$attribute] = [];
					
					foreach($value as $v){
						$arr['attributes'][$attribute][] = $v->toArray();
					}
					
					continue;
				}
			}			
			$arr['attributes'][$attribute] = $value;
			
		}
		

		return $arr;
	}

}
