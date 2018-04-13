<?php

namespace MacroMan\DPDLocal;

class Consignment extends Client {

	/**
	 * Set by the DPD Local API when send is called on DpdLocalShipment
	 * @var string
	 */
	protected $number;

	/**
	 * Unused
	 * @var null
	 */
	private $ref;

	/**
	 * Unused
	 * @var array
	 */
	private $parcels = array();

	/**
	 * Set by the DPD Local API when send is called on DpdLocalShipment
	 * @var array
	 */
	protected $parcelNumbers = array();

	/**
	 * Contact where the parcel(s) are due to be collected
	 * @var DpdLocalContact
	 */
	private $collectionDetails;

	/**
	 * Contact where the parcel(s) are due to be delivered
	 * @var DpdLocalContact
	 */
	private $deliveryDetails;

	/**
	 * Network service that will be used for this consignment.
	 * Use DpdLocalService::loadByAddress to get a list of available services
	 * @var string
	 */
	private $networkCode;

	/**
	 * Number of induvidual packages in this consignment
	 * @var int
	 */
	private $numberOfParcels;

	/**
	 * Total weight of the consignment in KG
	 * @var float
	 */
	private $weight;

	/**
	 * Free text field
	 * @var string
	 */
	private $shippingRef1;

	/**
	 * Free text field
	 * @var string
	 */
	private $shippingRef2;

	/**
	 * Free text field
	 * @var string
	 */
	private $shippingRef3;

	/**
	 * Value of the total consignment for the purpose of customs
	 * @var float
	 */
	private $customsValue;

	/**
	 * Free text field
	 * @var string
	 */
	private $deliveryInstructions;

	/**
	 * A small human readable description of the parcel
	 * @var string
	 */
	private $parcelDescription;

	/**
	 * The amount the product should be insured for
	 * @var int
	 */
	private $liabilityValue;

	/**
	 * True if the product should be insured
	 * @var bool
	 */
	private $liability;

	/**
	 * Helper function to create new object in one function call
	 * @param Contact $collectionDetails
	 * @param Contact $deliveryDetails
	 * @param string $networkCode
	 * @param int $numberOfParcels
	 * @param int $weight
	 * @param string $instructions
	 * @param string $ref1
	 * @param string $ref2
	 * @param string $ref3
	 * @param string $parcelDescripton
	 * @param float $value
	 * @param bool $liability
	 * @param float $liabilityValue
	 * @return \DpdLocalConsignment
	 */
	public static function create($collectionDetails, $deliveryDetails, string $networkCode, int $numberOfParcels, int $weight, string $instructions = '', string $ref1 = '', string $ref2 = '', string $ref3 = '', string $parcelDescripton = '', float $value = null, bool $liability = false, float $liabilityValue = null) {
		$self = new self();
		$self->collectionDetails = $collectionDetails;
		$self->deliveryDetails = $deliveryDetails;
		$self->networkCode = $networkCode;
		$self->numberOfParcels = $numberOfParcels;
		$self->weight = $weight;
		$self->deliveryInstructions = $instructions;
		$self->shippingRef1 = $ref1;
		$self->shippingRef2 = $ref2;
		$self->shippingRef3 = $ref3;
		$self->parcelDescription = $parcelDescripton;
		$self->customsValue = $value;
		$self->liability = $liability;
		$self->liabilityValue = $liabilityValue;
		return $self;
	}

	/**
	 * Converts properties into an array
	 * @return array
	 */
	public function toArray() {
		return array(
			'consignmentNumber' => $this->number,
			'consignmentRef' => Client::clean($this->ref),
			'parcels' => $this->parcels,
			'collectionDetails' => $this->collectionDetails->toArray(),
			'deliveryDetails' => $this->deliveryDetails->toArray(),
			'networkCode' => $this->networkCode,
			'numberOfParcels' => $this->numberOfParcels,
			'totalWeight' => $this->weight,
			'shippingRef1' => Client::clean($this->shippingRef1),
			'shippingRef2' => Client::clean($this->shippingRef2),
			'shippingref3' => Client::clean($this->shippingRef3),
			'customsValue' => Client::clean($this->customsValue),
			'deliveryInstructions' => Client::clean($this->deliveryInstructions),
			'parcelDescription' => Client::clean($this->parcelDescription),
			'liabilityValue' => $this->liabilityValue,
			'liability' => $this->liability,
		);
	}

	public function setNumber($number) {
		$this->number = $number;
	}

	public function setParcelNumbers($numbers) {
		$this->parcelNumbers = $numbers;
	}

}
