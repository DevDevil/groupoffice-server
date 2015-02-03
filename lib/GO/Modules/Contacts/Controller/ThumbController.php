<?php

namespace GO\Modules\Contacts\Controller;

use GO\Core\Exception\Forbidden;
use GO\Modules\Contacts\Model\Contact;
use GO\Modules\Upload\Controller\AbstractThumbController;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ThumbController extends AbstractThumbController {

	protected function thumbGetFile() {

//		$contact = false;

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

		$contact = !empty($this->router->routeParams['contactId']) ? Contact::findByPk($this->router->routeParams['contactId']) : false;

		if (!$contact) {
			$contact = new Contact();
		}else
		{			
//			if (!$contact->checkPermission('readAccess')) {
//				throw new Forbidden();
//			}
		}

		return $contact->photoFile();



		return false;
	}

	protected function thumbUseCache() {
		return true;
	}

}
