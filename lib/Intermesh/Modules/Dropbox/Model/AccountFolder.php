<?php

namespace Intermesh\Modules\Dropbox\Model;

use Intermesh\Core\Db\AbstractRecord;


/**
 * The AccountFolder model
 *
 * @property int $folderId
 * @property int $accountId
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class AccountFolder extends AbstractRecord {
	public static function primaryKeyColumn() {
		return ['folderId', 'accountId'];
	}
}