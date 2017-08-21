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
class Service extends Client {

	/**
	 * True if a printed invoice is required for this service
	 * @var bool
	 */
	private $invoiceRequired;

	/**
	 * True if liability insurance is available from DPD Local for this service
	 * @var bool
	 */
	private $isLiabilityAllowed;

	/**
	 * Encapsulation of DpdLocalCountry's
	 * @var array
	 */
	private $countries = array();

	/**
	 * Code represeting this product/service combo
	 * @var string
	 */
	private $networkCode;

	/**
	 * Human readable string representing this product/service combo
	 * @var string
	 */
	private $networkDescription;

	/**
	 * Code representing the product eg. ExPak1, ExPak5, Parcel, etc
	 * @var string
	 */
	private $productCode;

	/**
	 * Human readable product name
	 * @var string
	 */
	private $productDescription;

	/**
	 * Code representing the service eg. Next day, Next day before 12, etc
	 * @var string
	 */
	private $serviceCode;

	/**
	 * Human readable service name
	 * @var string
	 */
	private $serviceDescription;

	/**
	 * Helper to load a service by it's countries ISO 3 digit code
	 * @param string $serviceCode
	 * @return array Encapsulation of DpdLocalService's
	 */
	public static function loadByServiceCode(string $serviceCode) {
		$self = new self();
		$reqStr = "/shipping/network/" . $serviceCode;
		$response = $self->query($reqStr);
		return self::getObjects($response);
	}

	/**
	 * Get a list of services for the given consignment information
	 * @param int $direction
	 * @param int $NoOfParcels
	 * @param int $weight
	 * @param int $type
	 * @param DpdLocalAddress $collectionAddress
	 * @param DpdLocalAddress $deliveryAddress
	 * @return array Encapsulation of DpdLocalService's
	 */
	public static function loadByAddress(int $direction, int $NoOfParcels, int $weight, int $type, DpdLocalAddress $collectionAddress, DpdLocalAddress $deliveryAddress) {
		$data = array(
			'deliveryDirection' => $direction,
			'numberOfParcels' => $NoOfParcels,
			'totalWeight' => $weight,
			'shipmentType' => $type,
			'collectionDetails' => $collectionAddress->toArray(),
			'deliveryDetails' => $deliveryAddress->toArray(),
		);

		$self = new self();

		// Convert the array into a URL query string
		$queryStr = http_build_query($data);

		// http_build_query uses object[property] for query params, but the
		// DPD Local API expects params in object.property format, so well use
		// str_replace to replace [ with a period and ] with nothing
		$dataStr = str_replace(array('%5B', '%5D'), array('.', ''), $queryStr);
		$reqStr = "/shipping/network/?" . $dataStr;
		$response = $self->query($reqStr);
		return self::getObjects($response);
	}

	/**
	 * Convert the response from the API into an encapsulation of DpdLocalService's
	 * @param array $data From the API response
	 * @return array Encapsulation of DpdLocalService's
	 */
	private static function getObjects(array $data) {
		if (!is_array($data)) {
			$data = array($data);
		}
		$ret = array();
		foreach ($data as $row) {
			$service = new self();
			$service->argCheck($row, 'network');
			$service->invoiceRequired = (isset($row['invoiceRequired'])) ? $row['invoiceRequired'] : null;
			$service->isLiabilityAllowed = (isset($row['isLiabilityAllowed'])) ? $row['isLiabilityAllowed'] : null;
			$service->networkCode = $row['network']['networkCode'];
			$service->networkDescription = $row['network']['networkDescription'];
			if (isset($row['network']['country'])) {
				foreach ($row['network']['country'] as $countryInfo) {
					$service->countries[] = DpdLocalCountry::create($countryInfo['countryCode'], $countryInfo['countryName'], '', $countryInfo['isEUCountry'], $countryInfo['isLiabilityAllowed'], $countryInfo['isoCode'], $countryInfo['isPostcodeRequired'], $countryInfo['liabilityMax']);
				}
			}
			if (isset($row['product'])) {
				$service->productCode = $row['product']['productCode'];
				$service->productDescription = $row['product']['productDescription'];
			}
			if (isset($row['service'])) {
				$service->serviceCode = $row['service']['serviceCode'];
				$service->serviceDescription = $row['service']['serviceDescription'];
			}

			$ret[] = $service;
		}

		return $ret;
	}

}
