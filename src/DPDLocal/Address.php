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
class Address extends Client {

	/**
	 * Company or organisation name eg. DPD Group Ltd
	 * @var string
	 */
	public $organisation;

	/**
	 * Property name, number or combination eg. Flat 12 Smethwick Court
	 * @var string
	 */
	public $property;

	/**
	 * Street name of the address eg. Roebuck Lane
	 * @var string
	 */
	public $street;

	/**
	 * Area of a town/city. eg. "Chelsea" (in London)
	 * @var string
	 */
	public $locality;

	/**
	 * Town or city name eg. Brighton
	 * @var string
	 */
	public $town;

	/**
	 * County eg. Surrey
	 * @var string
	 */
	public $county;

	/**
	 * Postcode eg. BN12 7HT
	 * @var string
	 */
	public $postcode;

	/**
	 * ISO 2 digit country code eg. GB
	 * @var string
	 */
	public $countryCode;

	/**
	 * Helper function to create new object in one function call
	 * @param string $organisaton
	 * @param string $property
	 * @param string $street
	 * @param string $locality
	 * @param string $town
	 * @param string $county
	 * @param string $postcode
	 * @param string $countryCode
	 * @return \DpdLocalAddress
	 */
	public static function create(string $organisaton, string $property, string $street, string $locality, string $town, string $county, string $postcode, string $countryCode) {
		$self = new self();
		$self->organisation = substr($organisaton, 0, 35);
		$self->property = substr($property, 0, 35);
		$self->street = substr($street, 0, 35);
		$self->locality = substr($locality, 0, 35);
		$self->town = substr($town, 0, 35);
		$self->county = substr($county, 0, 35);
		$self->postcode = $postcode;
		$self->countryCode = $countryCode;
		return $self;
	}

	/**
	 * Converts all properties of type string into an array
	 * @return \DpdLocalAddress
	 */
	public function toArray() {
		$ret = array();

		foreach ($this as $key => $val) {
			if ($val && is_string($val)) {
				$ret[$key] = Client::clean($val);
			}
		}

		return $ret;
	}

}
