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
class Country extends Client {

	/**
	 * ISO 2 digit country code eg. GB
	 * @var string
	 */
	private $countryCode;

	/**
	 * Human readbale country name eg. "United Kingdom"
	 * @var string
	 */
	private $countryName;

	/**
	 * DPD Local's reference to the country. Only returned by the API, no need to set
	 * @var string
	 */
	private $internalCode;

	/**
	 * True if the country is in the EU (UK excluded)
	 * @var bool
	 */
	private $isEUCountry;

	/**
	 * True if liability insurance is available from DPD Local for this country
	 * @var bool
	 */
	private $isLiabilityAllowed;

	/**
	 * ISO 3 digit country code eg. 826 (as a string)
	 * @var string
	 */
	private $isoCode;

	/**
	 * True if a postcode is required for this country
	 * @var bool
	 */
	private $isPostcodeRequired;

	/**
	 * Maximum level of liability insurance available for this country
	 * @var int
	 */
	private $liabilityMax;

	/**
	 * Helper function to create new object in one function call
	 * @param string $countryCode
	 * @param string $countryName
	 * @param string $internalCode
	 * @param bool $isEUCountry
	 * @param bool $isLiabilityAllowed
	 * @param string $isoCode
	 * @param bool $isPostcodeRequired
	 * @param int $liabilityMax
	 * @return \DpdLocalCountry
	 */
	public static function create(string $countryCode, string $countryName, string $internalCode, bool $isEUCountry, bool $isLiabilityAllowed, string $isoCode, bool $isPostcodeRequired, int $liabilityMax) {
		$self = new self();
		$self->countryCode = $countryCode;
		$self->countryName = $countryName;
		$self->internalCode = $internalCode;
		$self->isEUCountry = $isEUCountry;
		$self->isLiabilityAllowed = $isLiabilityAllowed;
		$self->isoCode = $isoCode;
		$self->isPostcodeRequired = $isPostcodeRequired;
		$self->liabilityMax = $liabilityMax;
		return $self;
	}

	/**
	 * Helper to get an object by ISO 2 digit country code
	 * @param string $countryCode
	 * @return \DpdLocalCountry
	 * @throws Exception
	 */
	public static function loadByCountryCode(string $countryCode) {
		$self = new self();
		$reqStr = "/shipping/country/$countryCode";
		$response = $self->query($reqStr);
		if (!isset($response['country'])) {
			throw new Exception("Empty repsonse from '$reqStr' in DpdLocalCountry");
		}

		$self->argCheck($response['country'], 'countryCode', 'countryName', 'isEUCountry', 'isLiabilityAllowed', 'isoCode', 'isPostcodeRequired', 'liabilityMax');
		foreach ($response['country'] as $key => $val) {
			$self->$key = $val;
		}

		return $self;
	}

	/**
	 * Helper to load all available countries
	 * @return array Encapsulation of DpdLocalCountry objects
	 * @throws Exception
	 */
	public static function loadAll() {
		$self = new self();
		$reqStr = "/shipping/country";
		$response = $self->query($reqStr);
		if (!isset($response['country'])) {
			throw new Exception("Empty repsonse from '$reqStr' in DpdLocalCountry");
		}

		$countries = array();
		foreach ($response['country'] as $country) {
			$self = new self();
			$self->argCheck($country, 'countryCode', 'countryName', 'isEUCountry', 'isLiabilityAllowed', 'isoCode', 'isPostcodeRequired', 'liabilityMax');
			foreach ($country as $key => $val) {
				$self->$key = $val;
			}
			$countries[] = $self;
		}

		return $countries;
	}

}
