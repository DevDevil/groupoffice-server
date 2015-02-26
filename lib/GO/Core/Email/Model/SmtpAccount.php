<?php

namespace GO\Core\Email\Model;

use GO\Core\Auth\Model\User;
use GO\Core\Db\AbstractRecord;
use Swift_SmtpTransport;

/**
 * The SmtpAccount model
 *
 * @property int $id
 * @property int $ownerUserId
 * @property User $owner
 * @property string $hostname
 * @property int $port
 * @property string $encryption
 * @property string $username
 * @property string $password
 * @property string $fromName The from name of the sent messages
 * @property string $fromEmail The from email address of the sent messages
 *
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class SmtpAccount extends AbstractRecord {
	
	/**
	 * Create a swift transport with these account settings
	 * 
	 * @return \Swift_Transport
	 */
	private function transport(){
		$transport = Swift_SmtpTransport::newInstance($this->hostname, $this->port);
		
		if(isset($this->encryption)){
			$transport->setEncryption($this->encryption);
		}
		
		if(isset($this->username)){
			$transport->setUsername($this->username);
			$transport->setPassword($this->password);
		}

		return $transport;		
	}
	
	/**
	 * Get the mailer using this account settings
	 * 
	 * @return \Swift_Mailer
	 */
	public function mailer(){
		return new \Swift_Mailer($this->transport());
	}
	
}