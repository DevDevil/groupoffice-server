<?php

namespace Intermesh\Modules\Email\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;


/**
 * The Folder model
 *
 * @property int $id
 * @property int $accountId
 * @property string $name
 * @property string $syncedUntil
 * 
 * @property Account $account
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class Folder extends AbstractRecord {	
		
	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->belongsTo('account', Account::className(), 'accountId'),
		];
	}
	
	
	public function getSyncFilter(){
		if($this->syncedUntil != null){
			$since = date("d-M-Y", strtotime($this->syncedUntil));
			$filter = "SINCE ".$since;
		}else
		{
			$filter = 'ALL';
		}
		
		return $filter;
	}
	
	
}