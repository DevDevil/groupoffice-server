<?php
namespace GO\Core\Model;

use GO\Modules\Auth\Model\User;
use GO\Core\App;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\RelationFactory;

/**
 * Session model
 * 
 * User session data is stored in this model.
 *
 * @property int $id
 * @property int $userId
 * @property string $data
 * 
 * @property User $user
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Session extends AbstractRecord{

	public static function tableName() {
		return 'coreSession';
	}
	
	protected static function defineRelations(RelationFactory $r) {
		return array(
			$r->belongsTo('user', User::className(), 'userId')
		);
	}
	
	public function delete() {

		//clean up temp files
		$folder = App::config()->getTempFolder(false)->createFolder($this->id);
		$folder->delete();

		return parent::delete();
	}
}