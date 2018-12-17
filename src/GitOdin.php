<?php
namespace GitOdin;

use GitOdin\Request\EventGroup;
use GitOdin\Request\Event;
use GitOdin\Request\Authentication;
use GitOdin\Request\Payload;
use GitOdin\Traits\InstanceInterface;

/**
 * GitOdin.io PHP API Package
 *
 * @link http://GitOdin.io/documentation
 */

class GitOdin {
	use InstanceInterface;

	private $config = array();
	public $messageQueue = array();
	private $pem_cert = null;
	public $errors = array(); // No Storage Limit. Erase when you need more memeory...
	public $lastRequestTime = 0;
	public $lastRequestRoundTime = 0;
	public $lastHTTPCode = 0;
	public $tStart = 0;

	/**
	 * This is just a an Alias of the getInstance function.
	 * @NOTE: This will save the Main instance to the Class Static Instance so
	 * it can call all of the same instance.
	 */
	public static function summon(){
		$in = func_get_args();
		return call_user_func_array("self::getInstance", $in); // Handled by Instance Trait.
	}


	const Allow = true;
  const Deny = false;

	/**
	 * Used to Track the API Version, This gets used on the Backend
	 *  for proper API compatibility with the request.
	 */
	const VERSION = 1.8;

	/**
	 * Create Instance
	 * If nothing is passed it, it will check the ENV for the Config
	 *
	 * @param String App Secret
	 * @param String Server Address Connecting to for the API
	 * @param String Server Address Connecting to for the Auth API for Socket Connections
	 */
	public function __construct($secret = false, $region_server_name = false, $authServer = false){

		$this->tStart = microtime(true); // Set Class Init Time

		$this->errors[] = "[   SETUP  ] Starting New Instance";

		if($secret == false){
			$secret = getenv('GITODIN_SECRET', FALSE);
			$this->errors[] = "[   SETUP  ] Secret Not Defined, Fallback ENV";
		}
		if($region_server_name == false){
			$region_server_name = getenv('GITODIN_SERVER', FALSE);
			$this->errors[] = "[   SETUP  ] Server Not Defined, Fallback ENV";
		}
		if($authServer == false){
			$authServer = getenv('GITODIN_SERVERAUTH', FALSE);
			$this->errors[] = "[   SETUP  ] Auth Server Not Defined, Fallback ENV";
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
	 * @param GitOdin\Request\Authentication Packet for the Authentication Data
	 * @return Boolean Response Boolean from the Server
	 */
	public function authorize(Authentication $aPacket){
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
		$this->errors[] = "[ CURL:URL ] ".$url;

		$ch = curl_init($url);

		// Check if the Root Pem file is defiend, Yes? Verify Connection
		if(isset($this->pem_cert) && @file_exists($this->pem_cert)){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_CAINFO, $this->pem_cert);
		}
		else{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "GitOdin-PHP/".self::VERSION);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($content)
			));
		$result = curl_exec($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if(curl_error($ch) != ""){ $this->errors[] = "[   ERROR  ] ". curl_error($ch); /* Log Error */ }

		if($result !== ""){
			if (preg_match('~Location: (.*)~i', $result, $match)) {
		   $location = trim($match[1]);
			 	$this->errors[]  = "[  REQUEST ] Redirected to another address.";
			}

		}

		$this->lastRequestRoundTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
		$this->lastHTTPCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->lastRequestTime = microtime(true)- $this->tStart;

		if($http_code == 0){
			$this->errors[] = "[ REQUEST  ] Bad Request! (CURL Error)";
			$this->errors[] = "[  ^CURL   ]   ".curl_error($ch);
			$this->errors[] = "[  ^URL    ]   ".$url;
			$this->errors[] = "[  ^AGENT  ]   "."GitOdin-PHP/".self::VERSION;
			$this->errors[] = "[  ^PAYLOAD]   ".$content;
		}
		else if($http_code == 200 || $http_code == 201 || $http_code == 204){
			$this->errors[] = "[ REQUEST  ] Good Response from Server ";
			$this->errors[] = "[          ] ". str_replace("\r\n","",$result);
			$this->errors[] = "[ REQUEST^ ] Request Time: ".curl_getinfo($ch, CURLINFO_TOTAL_TIME);
			return true;
		}
		else if($http_code == 500){
			$this->errors[] = "[ REQUEST  ] AHHH! Da Server is F*cked up again! ".$http_code;
			$this->errors[] = "[  ^URL    ]   ".$url;
			$this->errors[] = "[  ^AGENT  ]   "."GitOdin-PHP/".self::VERSION;
			$this->errors[] = "[  ^PAYLOAD]   ".$content;
		}
		else if($http_code == 404){
			$this->errors[] = "[ REQUEST  ] Hmm. I think you need to check the api URL! (Hint: Its Wrong!) ".$http_code;
			$this->errors[] = "[  ^URL    ]   ".$url;
			$this->errors[] = "[  ^AGENT  ]   "."GitOdin-PHP/".self::VERSION;
			$this->errors[] = "[  ^PAYLOAD]   ".$content;
		}
		else if($http_code == 413){
			$this->errors[] = "[ REQUEST  ] Uhh, You have a Big Requst! Split the Request so you can send it! ".$http_code;
			$this->errors[] = "[  ^URL    ]   ".$url;
			$this->errors[] = "[  ^AGENT  ]   "."GitOdin-PHP/".self::VERSION;
			$this->errors[] = "[  ^PAYLOAD]   (Large Payload)";
		}
		else {
			$this->errors[] = "[ REQUEST  ] Bad Response from Server! ".$http_code;
			$this->errors[] = "[          ] ". str_replace("\r\n","",$result);
			$this->errors[] = "[  ^URL    ]   ".$url;
			$this->errors[] = "[  ^AGENT  ]   "."GitOdin-PHP/".self::VERSION;
			$this->errors[] = "[  ^PAYLOAD]   ".$content;
		}

		curl_close($ch);
		return false;
	}

	/**
	 * Add another Data to the Payload
	 *
	 * @param Array Payload Data
	 */
	public function add(Payload $input){
		$this->messageQueue[] = $input; // Add Payload to the Queue
		return $this;
	}

	/**
	 * SINGLE PAYLOAD and SEND FOR MULTI
	 *
	 * Prepair the Request and Tell curl_post to do it.
	 * This Function just needs the Payload Data and you can Provide the
	 *
	 * @param GitOdin\Request\Payload If Null is Specified then it Sends the Queue, If a String is provided
	 *  then the Payload is the String, If an Array is provided then it gets added as a full payload followed by getting sent.
	 * @return Boolean Response Text from the Server
	 */
	public function send(Payload $data = null){

		// Start the Request Data, Buffer
		$post = array(
			"authorization" => array(
				"app_key" => $this->config['secret']
			),
			"payload" => array()
		);

	  // Check if you are Sending for the Pending Payloads.
		if($data == null) {
			if(!empty($this->messageQueue)){
				// Running for Message Queue
				$post['payload'] = $this->messageQueue; // Add the Messages to the Payload to send Now
				$this->messageQueue = array(); // Clear Queue
			}
			else{ /* Queue is Empty! */ return true; }
		}

		elseif ($data !== null) {
			// Has a Payload Provided via the Input
			$post['payload'][] = $data; // Add Payload to Buffer
		}

	//// DEV: Seperate Packets untill the server has one endpoint for both payload types
		$AuthPackets = [];
		$DataPackets = [];

		// Sort Packets for Dev Purpose
		foreach($post['payload'] as $i => $packet){
			// Check for Auth Packet, Data is unique for Auth packets
			if(isset($packet->getPayload()['authorized']) == true){
				$AuthPackets[] = $packet->getPayload(); // Unwrap Instances to get just the Payload
			}

			// Check for Data Packet, Data is unique for Data packets
			else if(isset($packet->getPayload()['data']) == true){
					$DataPackets[] = $packet->getPayload(); // Unwrap Instances to get just the Payload
			}
		}

		$t = 0.00;

		// Check Queue for Auth Packets
		if(count($AuthPackets) != 0){
			$post['payload'] = $AuthPackets;
			$this->errors[] = "[POST:AUTH ] Starting Auth Requests";
			$t += $this->curl_post($post, $this->config['serverAuth']);
		}
		else {
			$this->errors[] = "[POST:AUTH ] Skipping Auth Requests, No Requests in Queue";
		}

		// Check Queue for Data Packets
		if(count($DataPackets) != 0){
			$post['payload'] = $DataPackets;
			$this->errors[] = "[POST:DATA ] Starting Data Requests";
			$t += $this->curl_post($post, $this->config['server']);
		}
		else {
			$this->errors[] = "[POST:DATA ] Skipping Data Requests, No Requests in Queue";
		}

		return $t;
	}
}
?>
