<?php

namespace GO\Modules\Import\Controller;

use Exception;
use GO\Core\App;
use GO\Core\Controller\AbstractRESTController;
use GO\Core\Db\Column;
use GO\Core\Db\Relation;
use GO\Core\Auth\Model\Role;
use GO\Modules\Contacts\Model\Contact;
use GO\Modules\Contacts\Model\ContactRole;

class CsvController extends AbstractRESTController {

	public static $delimiter = ',';
	public static $enclosure = '"';
	private $_record;

	public function httpPost() {


		//Don't be strict on imported values
		App::dbConnection()->getPDO()->query("SET sql_mode=''");

		$modelName = Contact::className();
		//Zoekcode,Naam,Voornaam,Tussenvoegsel,Achternaam,Voorletters,Geboortedatum,Titel,Geslacht,Functie,Bankrekening (BBAN),IBAN/bankrekening,BIC,Datum ondertekening,Datum beëindiging,Laatste incassodatum,Eerste incasso SEPA,Eenmalige machtiging,Mandaatkenmerk,Betalingswijze,Relatiebeheerder,Niet actief,Straat hoofdadres,Postcode hoofdadres,Plaats hoofdadres,Provincie hoofdadres,Land hoofdadres,E-mailadres,Faxnummer,Telefoonnummer,Mobiel nummer,Webpagina,Straat postadres,Postcode postadres,Plaats postadres,Provincie postadres,Land postadres,Afkoop bardienst,Bardienst avond,Bardienst middag,Bardienst ochtend,Blad,Bondsnummer,Comm,Fotonummer,SD,SE,Factuur toesturen,Begindatum lidmaatschap,Einddatum lidmaatschap,Postadres via ander lid,Betalend lid,KvK-nummer,Soort relatie,Persoontype,Naam contactpersoon,Afdeling,Is afzonderlijke relatie,BTW-nummer,BrancheZoekcode,BrancheBranchenaam,RechtsvormAfkorting,RechtsvormRechtsvorm,Bedrijfsgrootte,Valuta voor relatie,Debiteur,Crediteur,Verkoopdagboek,Verkooppostnummer,Verkoper bij debiteur,Kortingsmarge debiteur,Prijslijst bij debiteur,Kredietlimiet debiteur,Debiteur geblokkeerd,Betalingsvoorwaarde debiteur,Leveringsvoorwaarde debiteur,Inkoopdagboeknummer,Inkooppostnummer,Kortingsmarge crediteur,Kredietlimiet crediteur,Betalingsvoorwaarde crediteur,Leveringsvoorwaarde crediteur,Straat afleveradres,Postcode afleveradres,Plaats afleveradres,Provincie afleveradres,Land afleveradres,Straat factuuradres,Postcode factuuradres,Plaats factuuradres,Provincie factuuradres,Land factuuradres
		$filename = '/home/mschering/Downloads/leden.csv';

		$mapping = [
			'name' => 'Naam',
			'firstName' => 'Voornaam',
			'middleName' => 'Tussenvoegsel',
			'lastName' => 'Achternaam',
			'gender' => 'Geslacht',
			'dates' => [['date' => 'Geboortedatum', 'type' => '"birthday"']],
			'emailAddresses' => [['email' => 'E-mailadres', 'type' => '"home"']],
			'customfields' => ['Bondsnummer' => 'Bondsnummer', 'Lid sinds' => 'Begindatum lidmaatschap'],
			'phoneNumbers' => [
				['number' => 'Telefoonnummer', 'type' => '"home"'],
				['number' => 'Mobiel nummer', 'type' => '"mobile"']
			],
			'user' => ['username' => 'Bondsnummer', 'password' => 'Postcode hoofdadres']
		];

		$fp = fopen($filename, 'r');

		$headings = fgetcsv($fp, 4096, self::$delimiter, self::$enclosure);




		while ($csvRecord = fgetcsv($fp, 4096, self::$delimiter, self::$enclosure)) {

			foreach ($headings as $index => $colName) {
				$this->_record[$colName] = $csvRecord[$index];
			}

			try {

				$model = new $modelName;

				/* @var $model Contact */


				$attributes = $this->_buildAttributes($modelName, $mapping);

				//			var_dump($attributes);
				//			exit();

				$model->setAttributes($attributes);

				$success = $model->save();

				if (!$success) {
					echo "Import failed: " . var_export($model->getValidationErrors(), true) . "\n----\n\n";
				} else {
					$cr = new ContactRole();
					$cr->contactId = $model->id;
					$cr->roleId = Role::everyoneRoleId;
					$cr->readAccess = true;
					$cr->save();
				}
			} catch (\Exception $e) {
				echo $e->getMessage() . "\n";

				var_dump($attributes);

				echo "-----------------\n\n";
			}

//			break;
		}
		echo $this->view->render('json', []);
	}

	private function _buildAttributes($modelName, $mapping) {
		$attributes = [];

		foreach ($mapping as $goField => $csvField) {

			$attributes[$goField] = $this->_buildAttributeValue($modelName, $goField, $csvField);
		}

		return $attributes;
	}

	private function _buildAttributeValue($modelName, $goField, $csvField) {

		if (is_string($csvField) && substr($csvField, 0, 1) == '"' && substr($csvField, -1) == '"') {
			//hardcoded string value

			return substr($csvField, 1, -1);
		} elseif (($column = $modelName::getColumn($goField))) {

			/* @var $column Column */

			if ($column->required && empty($this->_record[$csvField])) {

				//throw exception here so we can catch and skup required relation attributes
				throw new Exception('Column "' . $modelName . '::' . $goField . '" is required');
			}

			//plain attribute of model
			return $this->_record[$csvField];
		} elseif (($relation = $modelName::getRelation($goField))) {


			if ($relation->isA(Relation::TYPE_HAS_MANY)) {

				//has one or belongs to relation

				$hasMany = [];


				foreach ($csvField as $mapping) {
					try {
						$hasMany[] = ['attributes' => $this->_buildAttributes($relation->getRelatedModelName(), $mapping)];
					} catch (Exception $e) {
						
					}
				}

				return $hasMany;
			} else {
				//has one or belongs to relation


				return ['attributes' => $this->_buildAttributes($relation->getRelatedModelName(), $csvField)];
			}
		} else {
			throw new Exception("Invalid mapping " . $goField . " for model $modelName");
		}
	}

	private function _parseValue($modelName, $attributeName, $value) {
//		$column = $modelName::getColumn($attributeName);
//		
//		/* @var $column \GO\Core\Db\Column */
//		
//		if($column->dbType == 'date' || $column->dbType == 'datetime') {
//			
//			echo $value;
//			return strtotime($value);
//		}else
//		{
		return $value;
//		}
	}

}
