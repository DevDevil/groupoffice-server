<?php

namespace GO\Modules\DevTools\Controller;

use GO\Core\Controller\AbstractRESTController;
use GO\Modules\Contacts\Model\Contact;

class TestController extends AbstractRESTController {

	public function httpGet() {
		
		$contact = new Contact();
		
		var_dump($contact->isNew);
		
		$contact = Contact::find()->single();
		
		var_dump($contact->isNew);
	}

}