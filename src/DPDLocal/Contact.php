<?php

namespace MacroMan\DPDLocal;

class Contact extends Client {

	/**
	 * Contact name for the DPD Local driver
	 * @var string
	 */
	private $name;

	/**
	 * Contact number for the DPD Local driver
	 * @var string
	 */
	private $phoneNumber;

	/**
	 * Notification email address
	 * @var string
	 */
	private $emailAddress;

	/**
	 * Notification mobile number (for text notifications)
	 * @var string
	 */
	private $mobileNumber;

	/**
	 * Physical address for the DPD Local driver
	 * @var DpdLocalAddress
	 */
	private $address;

	public static function create(DpdLocalAddress $address, string $name, string $phoneNumber, string $emailAddress = '', string $mobileNumber = '') {
		$self = new self();
		$self->address = $address;
		$self->name = $name;
		$self->emailAddress = $emailAddress;
		$self->phoneNumber = $phoneNumber;
		$self->mobileNumber = $mobileNumber;
		$self->sanitizePhoneNumbers();
		return $self;
	}

	private function sanitizePhoneNumbers() {
		$search = array('/\D/', '/^0044/', '/^44/');
		$replace = array('', '0', '0');
		$this->phoneNumber = preg_replace($search, $replace, $this->phoneNumber);
		if (substr($this->phoneNumber, 0, 2) == '07' && !$this->mobileNumber) {
			$this->mobileNumber = $this->phoneNumber;
		}
		$this->mobileNumber = preg_replace($search, $replace, $this->mobileNumber);
	}

	/**
	 * Converts properties into an array
	 * @return array
	 */
	public function toArray() {
		return array(
			"contactDetails" => array(
				"contactName" => $this->name,
				"telephone" => $this->phoneNumber,
			),
			"address" => $this->address->toArray(),
			'notificationDetails' => array(
				"email" => $this->emailAddress,
				"mobile" => $this->mobileNumber,
			),
		);
	}

}
