<?php

namespace GO\Modules\Contacts\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\Forbidden;
use GO\Core\Exception\NotFound;
use GO\Core\Auth\Model\User;
use GO\Modules\Contacts\ContactsModule;
use GO\Modules\Contacts\Model\Contact;

/**
 * The controller for contacts
 * 
 * See {@see Contact} model for the available properties
 * 
 * 
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ContactController extends AbstractController {
	

//	protected $checkModulePermision = true;
//
//	use ThumbControllerTrait;
//
//	protected function thumbGetFile() {
//
//		$contact = false;
//
//		if (isset($_GET['userId'])) {
//			$user = User::findByPk($_GET['userId']);
//
//			if ($user) {
//				$contact = $user->contact;
//			}
//		} else if (isset($_GET['contactId'])) {
//			$contact = Contact::findByPk($_GET['contactId']);
//		} elseif (isset($_GET['email'])) {
//
//			$query = Query::newInstance()
//					->joinRelation('emailAddresses')
//					->groupBy(['t.id'])
//					->where(['emailAddresses.email' => $_GET['email']]);
//
//			$contact = Contact::findPermitted($query, 'readAccess')->single();
//		}
//
//
//		if ($contact) {
//
//			if (!$contact->checkPermission('readAccess')) {
//
//
//				throw new Forbidden();
//			}
//
//			return $contact->getPhotoFile();
//		}
//
//
//		return false;
//	}

//	protected function thumbUseCache() {
//		return true;
//	}
	
	
	

	/**
	 * Fetch contacts
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	public function actionStore($orderColumn = 'name', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where = null) {

		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset);

		if (!empty($searchQuery)) {
			$query->search($searchQuery, ['name']);
		}

		if (!empty($where)) {

			$where = json_decode($where, true);

			if (count($where)) {
				$query
						->groupBy(['t.id'])
						->whereSafe($where);
			}
		}

		$contacts = Contact::findPermitted($query);


		$store = new Store($contacts);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
	protected function actionNew($returnAttributes = []){
		$contact = new Contact();
		if(!ContactsModule::model()->permissions->has(ContactsModule::PERMISSION_CREATE)){
			throw new Forbidden();
		}
		return $this->renderModel($contact, $returnAttributes);
	}
	
	
	/**
	 * GET a list of contacts or fetch a single contact
	 *
	 * The attributes of this contact should be posted as JSON in a role object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"name":"test",...}}}
	 * </code>
	 * 
	 * @param int $contactId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($contactId, $returnAttributes = []){
		
		if($contactId == "current"){
			$contact = User::current()->contact;
		}else
		{
			$contact = Contact::findByPk($contactId);
		}

		if (!$contact) {
			throw new NotFound();
		}

		return $this->renderModel($contact, $returnAttributes);

	}

	
	/**
	 * Create a new field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"field":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionCreate($returnAttributes = []) {
		
		$contact = new Contact();		
		
		if(!ContactsModule::model()->permissions->has(ContactsModule::PERMISSION_CREATE)){
			throw new Forbidden();
		}
		
		$contact->setAttributes(App::request()->payload['data']);
		$contact->save();
		

		return $this->renderModel($contact, $returnAttributes);
	}

	/**
	 * Update a field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"field":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param int $contactId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($contactId, $returnAttributes = []) {

		if($contactId == "current"){
			$contact = User::current()->contact;
		}else
		{
			$contact = Contact::findByPk($contactId);
		}

		if (!$contact) {
			throw new NotFound();
		}

		$contact->setAttributes(App::request()->payload['data']);
		$contact->save();
		
		return $this->renderModel($contact, $returnAttributes);
	}

	/**
	 * Delete a field
	 *
	 * @param int $contactId
	 * @throws NotFound
	 */
	public function actionDelete($contactId) {
		$contact = Contact::findByPk($contactId);

		if (!$contact) {
			throw new NotFound();
		}

		$contact->delete();

		return $this->renderModel($contact);
	}
}