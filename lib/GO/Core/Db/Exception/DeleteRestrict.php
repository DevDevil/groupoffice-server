<?php
namespace GO\Core\Db\Exception;

use Exception;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Relation;

/**
 * Throw when an operation was forbidden.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class DeleteRestrict extends Exception
{
	public function __construct(AbstractRecord $model, Relation $relation) {
		parent::__construct("model: ".$model->className().' relation: '.$relation->getName());
	}
}