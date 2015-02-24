<?php
namespace GO\Modules\Projects\Model;

use GO\Core\Db\AbstractRecord;

/**
 * @property int $id
 * @property int $revision
 * @property int $proposalId
 * @property string $title
 * @property string $content
 * @property datetime $agreedAt
 * @property int $sortOrder
 * @property double $minHours
 * @property double $maxHours
 */

class ProposalItem extends AbstractRecord{
	
}