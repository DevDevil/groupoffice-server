<?php
namespace GO\Modules\Tennis\Controller;

use GO\Core\Controller\AbstractController;
use GO\Core\Db\Query;
use GO\Core\Http\Client;
use GO\Modules\Contacts\Model\Contact;

class SpeelsterkteController extends AbstractController{
	
	protected function authenticate() {
		return true;
	}
	
	private $_url = 'http://publiek.mijnknltb.nl/spelersprofiel.aspx?bondsnummer=';
	
	public function httpGet(){
		
		$q = Query::newInstance()
				->joinRelation('customfields')
				->where(['!=',['customfields.Bondsnummer'=>'']]);
//				->andWhere(['firstName' => 'Frank', 'lastName'=>'Pot']);
		
		$contacts = Contact::find($q);
		
		
		
		$httpClient = new Client();
		
		$count++;
		foreach($contacts as $contact){
			$html = $httpClient->request($this->_url.$contact->customfields->Bondsnummer);
			
			$contact->customfields->{"Speelsterkte enkel"} = $this->_findRating($html, true);			
			$contact->customfields->{"Speelsterkte dubbel"} = $this->_findRating($html, false);
			
//			var_dump($contact->customfields->getAttributes());
			
			if(!$contact->customfields->save()){
				var_dump($contact->customfields->getValidationErrors());
			}
//			break;
			
			$count++;
		}
		
		return $this->renderJson(['success' => true, 'count' => $count]);
		
//		var_dump(\GO\Core\App::debugger()->entries);
		
	}
	
	private function _findRating($html, $single = true){
		
		$str = $single ? "Rating enkel" : "Rating Dubbel";
		$pos = strpos($html, $str);
		if(!$pos){
			return null;
		}
		$tdPos = strpos($html, '<td>', $pos);
		
		if(!$tdPos){
			return null;
		}
		
		$tdPos += 4;
		
		$closeTdPos = strpos($html, '</td>', $tdPos);
		
		if(!$closeTdPos){
			return null;
		}
		
		$rating = substr($html, $tdPos, $closeTdPos - $tdPos);
		
		return str_replace(',', '.', $rating);
	}
	
}