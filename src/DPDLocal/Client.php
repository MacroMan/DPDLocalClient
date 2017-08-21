<?php

namespace MacroMan\DPDLocal;

use Exception;

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
class Client {

	/**
	 * The API enpoint URL
	 * @var string
	 */
	private $url = "https://api.dpdlocal.co.uk";

	/**
	 * The cURL timeout value in seconds (as a string)
	 * @var string (number)
	 */
	private $timeout = "5";

	/**
	 * The useragent header to send the DPD Local API
	 * @var string
	 */
	private $userAgent = "PHP client library for the DPD Local API v1.0.6";

	/**
	 * The username to access the DPD Local API
	 * Provided by the GeoPost IT Service Helpdesk
	 * @var string
	 */
	private $username = "";

	/**
	 * The username to access the DPD Local API
	 * Provided by the GeoPost IT Service Helpdesk
	 * @var string
	 */
	private $password = "";

	/**
	 * The DPD Local account number, in the form of "account/1234567"
	 * Provided by DPD Local or the GeoPost IT Service Helpdesk
	 * @var string
	 */
	private $accountNo = "account/1234567";

	/**
	 * The authorization header retrieved from the DPD Local API
	 * @var string
	 */
	private $dpdSession;

	/**
	 * The cURL object
	 * @var cURL handle
	 */
	private $curl;

	/**
	 * Possible error codes returned by the DPD Local API
	 * @var array
	 */
	private $httpErrorCodes = array(
		401, 403, 404, 500, 503
	);

	/**
	 * Header stating what type of data we are sending to the DPD Local API
	 * @var string (mimeType)
	 */
	private $contentType = 'application/json';

	/**
	 * Header requesting the format of the returned data
	 * @var string (mimeType)
	 */
	private $returnFormat = 'application/json';

	/**
	 * Initilizes cURL and requests authorization
	 */
	public function init() {
		$this->curl = curl_init();

		$data = $this->query('/user/?action=login', array(
			"Authorization" => "Basic " . base64_encode($this->username . ':' . $this->password),
		));

		// Unset credentials here to prevent the library leaking the them
		$this->password = null;

		$this->dpdSession = $data['geoSession'];
	}

	/**
	 * Close the cURL session when finished
	 */
	public function __destruct() {
		if ($this->curl) {
			curl_close($this->curl);
		}
	}

	/**
	 * Execute the low level cURL functions
	 * @param string $action The action to perfom
	 * @param array $additionalHeaders
	 *  Additional headers to add to the HTTP request
	 * @return array $data The response from the API
	 * @throws Exception for cURL error
	 * @throws Exception for HTTP error
	 */
	public function query(string $action, array $additionalHeaders = array(), $payload = null, $decodeOutput = true) {
		if (!$this->curl) {
			$this->init();
		}
		echo "Stack trace:\n";
		debug_print_backtrace();
		$headers = array_merge(array(
			"Content-Type" => $this->contentType,
			"Accept" => $this->returnFormat,
			"GEOClient" => "{$this->username}/{$this->accountNo}",
				), $additionalHeaders);

		$method = 'GET';

		if ($this->dpdSession) {
			$headers["GEOSession"] = $this->dpdSession;
		} else {
			$method = 'POST';
		}

		if ($payload) {
			$method = 'POST';
			$length = strlen($payload);
			$headers["Content-Length"] = $length;
		}
		$this->setCurlOptions($method, $action, $headers, $payload);

		$exec = curl_exec($this->curl);
		$httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		if ($decodeOutput) {
			$data = json_decode($exec, true);
		} else {
			$data = $exec;
		}

		if (curl_error($this->curl)) {
			throw new Exception('Error connecting to API: ' . curl_error($this->curl));
		} elseif (in_array($httpCode, $this->httpErrorCodes)) {
			$this->httpError($httpCode);
		} elseif (!$decodeOutput) {
			return $data;
		} elseif (isset($data['error']) && empty($data['error']) == false) {
			$this->responseError($data['error']);
		} elseif (isset($data['data']) == false || empty($data['data'])) {
			throw new Exception('Empty dataset returned from DPD Local API');
		} else {
			return $data['data'];
		}
	}

	/**
	 * Set the options needed for a low level cURL execute
	 * @param string $method HTTP request method. Possible values are 'GET' and 'POST'
	 * @param string $action The action to perfom
	 * @param array $headers Headers to add to the HTTP request
	 */
	private function setCurlOptions(string $method, string $action, array $headers, $payload = null) {
		curl_setopt_array($this->curl, array(
			CURLOPT_URL => $this->url . $action,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => $this->timeout,
			CURLOPT_USERAGENT => $this->userAgent,
			CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $payload,
		));
	}

	private function formatHeaders($headers) {
		$ret = array();
		foreach ($headers as $header => $value) {
			$ret[] = "$header: $value";
		}
		return $ret;
	}

	/**
	 * Exception handler for query()
	 * @param type $httpCode
	 * @throws Exception
	 */
	private function httpError(string $httpCode) {
		switch ($httpCode) {
			case '401':
				throw new Exception('Username / Password incorrect');
			case '403':
				throw new Exception('dpdSession header not found or invalid');
			case '404':
				throw new Exception('An attempt was made to call an API in which the URL cannot be found');
			case '500':
				throw new Exception('The ESG server had an internal error');
			case '503':
				throw new Exception('The API being called is temporarily out of service');
		}
	}

	/**
	 * Extract error information from error returned by the DPD Local API and
	 *  throw an exception with that information
	 * @param array $error
	 * @throws Exception
	 */
	private function responseError(array $error) {
		$err = (isset($error[0])) ? $error[0] : $error;
		throw new Exception('Code: ' . $err['errorCode'] . ' Type: ' . $err['errorType'] . ' Message: ' . $err['obj'] . ' / ' . $err['errorMessage']);
	}

	/**
	 * Check for the presence of array keys and throw an exception if not
	 * @param object $class The class name is extracted from this
	 * @param array $array
	 * @param array $args
	 * @throws Exception
	 */
	public function argCheck($array, ...$args) {
		foreach ($args as $arg) {
			if (!isset($array[$arg])) {
				throw new Exception("Missing required arg '$arg'");
			}
		}
	}

	/**
	 * Magic getter to expose read only to private properties
	 * @param string $name Property name to retrieve
	 * @return DpdLocal|string|bool|int
	 */
	public function __get($name) {
		if (property_exists($this, $name)) {
			return $this->$name;
		}

		return parent::__get($name);
	}

}
