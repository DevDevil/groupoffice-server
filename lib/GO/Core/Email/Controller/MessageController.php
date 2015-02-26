<?php
namespace GO\Core\Email\Controller;

use GO\Core\Controller\AbstractController;
use GO\Core\Email\Model\SmtpAccount;
use Swift_Message;

class MessageController extends AbstractController{
	public function actionSend($smtpAccountId){
		$smtpAccount = SmtpAccount::findByPk($smtpAccountId);
		
		$message = Swift_Message::newInstance("Test")
						->addTo($smtpAccount->fromEmail, $smtpAccount->fromName)
						->setFrom($smtpAccount->fromEmail, $smtpAccount->fromName);
		
		$message->setBody("This is a test message");
						
		
		$success = $smtpAccount->mailer()->send($message);
		
		return $this->renderJson(['success' => $success]);
	}
}