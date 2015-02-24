<?php
namespace GO\Modules\Projects\Model;

use GO\Core\Db\AbstractRecord;

/**
 * @property int $id
 * @property int $createdBy
 * @property datetime $createdAt
 * @property datetime $modifiedAt
 * 
 * Relations
 * @property User $owner						Linked on $createdBy
 * @property Project $project				Linked on $id
 */

class Proposal extends AbstractRecord{
	
	/**
	 * Get the total amount of revisions that are made for this proposal
	 * 
	 * @return int count of available revisions
	 */
	public function getRevisions(){
		
		
		return ;	
	}
	
	/**
	 * Get the proposalItems that are part of the requested revision
	 * 
	 * @param int $revision
	 * @return ProposalItems[] The proposalItems for the given revision
	 */
	public function getItems($revision=0){
		
		if ($revision > $this->getRevisions()) {
			Throw new Exception('Revision doesn\'t exist.');
		}

		$proposalItems = [];
		
		// Search the proposalItems and add them to the $proposalItems array
		
		
		return $proposalItems;
	}
	
}