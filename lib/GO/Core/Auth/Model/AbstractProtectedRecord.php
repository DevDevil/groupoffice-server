<?php
namespace GO\Core\Auth\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Exception\Forbidden;


/** 
 * Proteced record
 * 
 * Extend this record class if you want to protect your model with READ, WRITE 
 * and DELETE permissions.
 * 
 * Your record needs a roles relation. See {@see \GO\Core\Auth\Model\RecordPermissionTrait}.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class AbstractProtectedRecord extends AbstractRecord {
	
	
	use RecordPermissionTrait;
	
	/**
	 * Allow read access to the role
	 */
	const PERMISSION_READ = 0;
	
	/**
	 * Allow write access to the role
	 */
	const PERMISSION_WRITE = 1;	
	
	/**
	 * Allow delete access to the role
	 */
	const PERMISSION_DELETE = 2;
	
	
	/**
	 * {@inheritdoc}	 
	 * @throws Forbidden
	 */
	public function __construct() {
		
		parent::__construct();
		
		if(!$this->isNew() && !$this->permissions->has(self::PERMISSION_READ)){
			throw new Forbidden();
		}
	}
	
	/**
	 * 
	 * {@inheritdoc}
	 * 
	 * @throws Forbidden
	 */
	public function save() {
		
		if(!$this->isNew() && !$this->permissions->has(self::PERMISSION_WRITE)){
			throw new Forbidden();
		}
		
		return parent::save();
	}
	
	/**
	 * {@inheritdoc}
	 * 
	 * @throws Forbidden
	 */
	public function delete() {
		
		if(!$this->permissions->has(self::PERMISSION_DELETE)){
				throw new Forbidden();
		}
		
		return parent::delete();
	}	
}