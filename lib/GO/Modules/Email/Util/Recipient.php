<?php

namespace GO\Modules\Email\Util;

class Recipient {
	
	public $email;
	
	public $personal;
	
	public function __construct($email, $personal = null) {
		$this->email = $email;
		$this->personal = $personal;
	}
	
	public function __toString() {
		if (!empty($this->personal)) {
			return '"' . $this->personal . '" <' . $this->email . '>';
		} else {
			return $this->email;
		}
	}	
}