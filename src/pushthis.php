<?php
namespace Pushthis;


require_once("Request/Authentication.php");
require_once("Request/Event.php");
require_once("Request/EventGroup.php");
require_once("Request/Payload.base.php");

/**
 * PushThis.io PHP API Package
 *
 * @link http://pushthis.io/documentation
 */
class Pushthis {
	private $config = array();
	public $messageQueue = array();
	private $pem_cert = null;
	public $errors = array();

	const Allow = true;
  const Deny = false;

	/**
	 * Used to Track the API Version, This gets used on the Backend
	 *  for proper API compatibility with the request.
	 */
	const VERSION = 1.0;

	/**
	 * Create Instance
	 * If nothing is passed it, it will check the ENV for the Config
	 *
	 * @param String App Secret
	 * @param String Server Address Connecting to for the API
	 * @param String Server Address Connecting to for the Auth API for Socket Connections
	 */
	public function __construct($secret = false, $region_server_name = false, $authServer = false){

		if($secret == false){
			$secret = env('PUSHTHIS_SECRET', FALSE);
		}
		if($region_server_name == false){
			$region_server_name = env('PUSHTHIS_SERVER', FALSE);
		}
		if($authServer == false){
			$authServer = env('PUSHTHIS_SERVERAUTH', FALSE);
		}

		$this->config['secret'] = $secret;
		$this->config['server'] = $region_server_name;
		$this->config['serverAuth'] = $authServer;
	}

	/**
	 * Here you can specify the Root CA for CURL to verity the Host Connection.
	 *
	 * @param String Path to the pem File for the Root CA
	 * @link https://curl.haxx.se/ca/cacert.pem
	 */
	public function setPem($filePath){
		// https://stackoverflow.com/questions/24611640/
		// https://curl.haxx.se/ca/cacert.pem
		if(file_exists($filePath)){
			$this->pem_cert = realpath($filePath);
		}
		else{
			return false;
		}
	}

	/**
	 * Check URL to see if it Ends with /auth
	 *
	 * @link https://regex101.com/r/fTPAZJ/2/
	 * @return Boolean TF if the Server URL is a Valid URL for the Connecting Auth Address
	 */
	public function is_url_auth(){
		$m = preg_match_all("/^((http[s]?):\/)?\/?([^:\/\s]+)((\/\w+)*\/)auth/", $this->config['server']);
		return $m;
	}

	/**
	 * Check URL to see if it Ends with /api
	 *
	 * @link https://regex101.com/r/fTPAZJ/2/
	 * @return Boolean TF if the Server URL is a Valid URL for the Connecting API Address
	 */
	public function is_url_api(){
		$m = preg_match_all("/^((http[s]?):\/)?\/?([^:\/\s]+)((\/\w+)*\/)api/", $this->config['server']);
		return $m;
	}

	/**
	 * Send a Packet. (Single Request)
	 *
	 * @param Pushthis\Authentication Packet for the Authentication Data
	 * @return Boolean Response Boolean from the Server
	 */
	public function authorize(Pushthis\Authentication $aPacket){
		// Start the Request Data
			$post = array(
				"authorization" => array(
					"app_key" => $this->config['secret']
				),
				"payload" => array()
			);

		$post['payload'] = $aPacket->getPayload();
		return $this->curl_post($post, $this->config['serverAuth']);
	}

	/**
	 * Using CURL Make the Request
	 *
	 * @param Array Payload Data to Send to the Given URL
	 * @param String URL to Post the Data to
	 * @return Boolean Is the Request Successfull
	 */
	private function curl_post(Array $data, String $url){
		$content = json_encode($data);
		$ch = curl_init($url);

		// Check if the Root Pem file is defiend, Yes? Verify Connection
		if(isset($this->pem_cert) && @file_exists($this->pem_cert)){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_CAINFO, $this->pem_cert);
		}
		else{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		}

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Pushthis-PHP/".self::VERSION);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($content)
			));
		$result = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if(curl_error($ch) != ""){ $this->errors[] = "[   ERROR  ]". curl_error($ch); /* Log Error */ }
		curl_close($ch);

		if($result !== ""){
			if (preg_match('~Location: (.*)~i', $result, $match)) {
		   $location = trim($match[1]);
			 	$this->errors[]  = "[  REQUEST ] Redirected to another address.";
			}

		}

		if($http_code == 200 || $http_code == 201){
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Add another Data to the Payload
	 *
	 * @param Array Payload Data
	 */
	public function add(Pushthis\Payload $input){
		$t = $input->getPayload();
		$this->messageQueue[] = array_merge($t, $input);
		return $this;
	}

	/**
	 * SINGLE PAYLOAD and SEND FOR MULTI
	 *
	 * Prepair the Request and Tell curl_post to do it.
	 * This Function just needs the Payload Data and you can Provide the
	 *
	 * @param Payload If Null is Specified then it Sends the Queue, If a String is provided
	 *  then the Payload is the String, If an Array is provided then it gets added as a full payload followed by getting sent.
	 * @return Boolean Response Text from the Server
	 */
	public function send(Pushthis\Payload $data = null){

		// Start the Request Data
		$post = array(
			"authorization" => array(
				"app_key" => $this->config['secret']
			),
			"payload" => array()
		);

	  // Check if you are Sending for the Pending Payloads.
		if($data === null) {
			if(!empty($this->messageQueue)){
				// Running for Message Queue
				$post['payload'] = $this->messageQueue; // Add the Messages to the Payload to send Now
				$this->messageQueue = array(); // Clear Queue
			}
			else{ /* Queue is Empty! */ return true; }
		}

		elseif ($data !== null) {
			// Has a Payload Provided via the Input
			$post['payload'][]  = $data->getPayload();
		}

		// DEV: Seperate Packets untill the rewrite comes back to change the Endpoint Payload
		$AuthPackets = [];
		$DataPackets = [];

		// Sort Packets for Dev Purpose
		foreach($post['payload'] as $i => $packet){

			// Check for Auth Packet, Data is unique for Auth packets
			if(isset($packet['authorized']) == true){
				$AuthPackets[] = $packet;
			}

			// Check for Data Packet, Data is unique for Data packets
			else if(isset($packet['data']) == true){
					$DataPackets[] = $packet;
			}
		}

		$post['payload'] = $AuthPackets;
		$this->curl_post($post, $this->config['serverAuth']);

		$post['payload'] = $DataPackets;
		$this->curl_post($post, $this->config['server']);

		return true;
	}
}
?>
