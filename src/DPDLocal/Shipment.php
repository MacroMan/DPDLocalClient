<?php

namespace MacroMan\DPDLocal;

/**
 * Provides direct and easy access to the DPD Local API
 *
 * @category	Client Library
 * @package		PHP client library for the DPD Local API v1.0.6
 * @version		v1.0.6 - The version number follows the version of the
 *  documentation released by DPDGroup for the DPD Local API. This ensures you
 *  are using the correct version of this library for the current implentation
 *  by DPDGroup.
 *
 * @license		http://www.gnu.org/licenses/gpl-3.0.txt  GNU GENERAL PUBLIC
 * 	LICENCE Version 3, 29 June 2007
 * @author		David Wakelin <david@davidwakelin.co.uk>
 * @copyright	(c) 2017, David Wakelin
 */
class Shipment extends Client {

	/**
	 * Unused
	 * @var null
	 */
	private $jobId = null;

	/**
	 * Unused
	 * @var bool
	 */
	private $collectionOnDelivery = false;

	/**
	 * Unused
	 * @var null
	 */
	private $invoice = null;

	/**
	 * The date that the item should be collected
	 * @var DateTime
	 */
	private $collectionDate;

	/**
	 * Whether to combine shipments if similar enough
	 * @var bool
	 */
	private $consolidate;

	/**
	 * The shipment needs a consignment
	 * @var DpdLocalConsignment
	 */
	protected $consignment;

	/**
	 * DPD's ID for this shipment
	 * @var int
	 */
	protected $shipmentId;

	/**
	 * True if this shipment will be combined with another
	 * @var bool
	 */
	protected $consolidated;

	/**
	 * Helper function to create new object in one function call
	 * @param Consignment $consignment
	 * @param DateTime $collectionDate
	 * @param bool $consolodate
	 * @return \DpdLocalShipment
	 */
	public static function create($consignment, \DateTime $collectionDate, bool $consolodate = false) {
		$self = new self();
		$self->collectionDate = $collectionDate;
		$self->consolidate = $consolodate;
		$self->consignment = $consignment;
		return $self;
	}

	/**
	 * Converts properties into an array
	 * @return array
	 */
	public function toArray() {
		return array(
			'job_id' => $this->jobId,
			'collectionOnDelivery' => $this->collectionOnDelivery,
			'invoice' => $this->invoice,
			'collectionDate' => $this->collectionDate->format("Y-m-d\TH:i:s"),
			'consolidate' => $this->consolidate,
			'consignment' => array($this->consignment->toArray()), // JSON object inside array. Don't ask.
		);
	}

	/**
	 * Send the data to DPD to actually create the shipment
	 * @return \DpdLocalShipment
	 */
	public function send() {
		$data = $this->query('/shipping/shipment', array(), json_encode($this->toArray()));
		if (!$data) {
			throw new \Exception($this->error);
		}
		$this->shipmentId = $data['shipmentId'];
		$this->consolidated = $data['consolidated'];
		$this->consignment->setNumber($data['consignmentDetail'][0]['consignmentNumber']);
		$this->consignment->setParcelNumbers($data['consignmentDetail'][0]['parcelNumbers']);
		return $this;
	}

	public function getLabels($shipmentId = null) {
		if (!$shipmentId) {
			$shipmentId = $this->shipmentId;
		}
		if (!$shipmentId) {
			throw new \Exception("No shipmentId provided to getLabels");
		}

		return $this->query("/shipping/shipment/$shipmentId/label/", array("Accept" => "text/vnd.citizen-clp"), null, false);
	}

}
