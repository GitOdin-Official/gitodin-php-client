<?php
namespace Pushthis;

/**
 * PushThis.io PHP API Package
 *
 * @link http://pushthis.io/documentation
 */
class Pushthis {
	private $config = array();
	public $channel = null;
	public $event = null;
	public $messageQueue = array();
	private $pem_cert = null;
	public $errors = array();

	/**
	 * Used to Track the API Version, This gets used on the Backend
	 *  for proper API compatibility with the request.
	 */
	const VERSION = 1.0;

	/**
	 * Create Instance
	 *
	 * @param String App Key
	 * @param String App Secret
	 * @param String Server Address Connecting to for the API
	 */
	public function __construct($key, $secret, $region_server_name){
		$this->config['key'] = $key;
		$this->config['secret'] = $secret;
		$this->config['server'] = $region_server_name;
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
	 * Allow or Deny access to a Private Channel by the Socket Id and channel.
	 *
	 * @param Boolean Specify weather to allow the Client or not
	 * @param String Channel to Allow or Decline Access
	 * @param String Socket Id of the request to act upon
	 * @return String Response Text from the Server
	 */
	public function authorize(Boolean $allow = false, String $channel, String $socketId){
		if(!$this->is_url_auth()) {
			// THE URL IS NOT CORRECT FOR A AUTH REQUEST
			$this->errors[] = "URL is not the Auth Access Point. Please Refer to the Pushthis.io Documentation for more information.";
			return false;
		}
		if(!isset($channel) || !isset($socketId)){ return false; }
		// Start the Request Data
			$post = array(
				"key" => $this->config['key'],
				"secret" => $this->config['secret'],
				"payload" => array()
			);
		// Payload Template
			$t = array(
				'channel' => $channel,
				'authorized'   => $allow,
				'socket_id'    => $socketId
			);

		$post['payload'] = $t;
		return $this->curl_post($post, $this->config['server']);
	}

	/**
	 * Using CURL Make the Request
	 *
	 * @param Array Payload Data to Send to the Given URL
	 * @param String URL to Post the Data to
	 * @return String Response Text from the Server
	 */
	private function curl_post(Array $data, String $url){
		$content = json_encode($data);
		$ch = curl_init($url);

		// Check if the Root Pem file is defiend, Yes? Verify Connection
		if(isset($this->pem_cert) && file_exists($this->pem_cert)){
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
		curl_setopt($ch, CURLOPT_USERAGENT, "Pushthis-PHP/".self::VERSION);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($content)
			));
		$result = curl_exec($ch);
		if(curl_error($ch) != ""){ $this->errors[] = curl_error($ch); /* Log Error */ }
		curl_close($ch);
		return $result;
	}

	/**
	 * Used to set defaults in Requests, Channel
	 *
	 * @param String Channel to Set
	 */
	public function set_channel(String $in){
		return $this->channel = $in;
	}

	/**
	 * Used to set defaults in Requests, Events
	 *
	 * @param String Event to Set
	 */
	public function set_event(String $in){
		return $this->event = $in;
	}

	/**
	 * Is Multidimensional Array
	 *
	 * @link https://pageconfig.com/post/checking-multidimensional-arrays-in-php
	 * @param Array Array to Read
	 * @return Boolean If the Array given has any Arrays in it.
	 */
	private function is_marray(Array $arr) {
		if(!is_array($arr)) { return false; }
		unset($arr['data']); // Remove False Posative
		rsort( $arr );
		return isset( $arr[0] ) && is_array( $arr[0] );
	}

	/**
	 * Add another Data to the Payload
	 *
	 * @param Array Payload Data
	 */
	public function add($input = null){
		if($input == null || empty($input)) { return false; }
		$t = array(
			'channel' => $this->channel,
			'event'   => $this->event,
			'data'    => null
		); // Payload Template

		$this->messageQueue[] = array_merge($t, $input);
	}

	/**
	 * SINGLE PAYLOAD and SEND FOR MULTI
	 *
	 * Prepair the Request and Tell curl_post to do it.
	 * This Function just needs the Payload Data and you can Provide the
	 *
	 * @param Array|String If Null is Specified then it Sends the Queue, If a String is provided
	 *  then the Payload is the String, If an Array is provided then it gets added as a full payload followed by getting sent.
	 * @return String Response Text from the Server
	 */
	public function send($data = null){
		if(!$this->is_url_api()) {
			// THE URL IS NOT CORRECT FOR A SEND REQUEST
			$this->errors[] = "URL is not the API Access Point. Please Refer to the Pushthis.io Documentation for more information.";
			return false;
		}

		// Start the Request Data
			$post = array(
				"key" => $this->config['key'],
				"secret" => $this->config['secret'],
				"payload" => array()
			);
		// Payload Template
			$t = array(
				'channel' => $this->channel,
				'event'   => $this->event,
				'data'    => null
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

	  // Payload->Data Array, Multi Request
		else if($this->is_marray($data)){
			// Handle if it is a Multidimensional Array
			$post['payload'] = array_merge( $post['payload'], $data );
		}

	  // Payload->Data Array, Single Request
		else if(is_array($data) && ( isset($data['channel']) || isset($data['event']) || isset($data['data']) )){
			// Running for the Payload Defenition that allows for
			$post['payload'][] = array_merge($t, $data); // Add to the Payload
		}

		// Payload->Data Array
		else if(is_array($data)){
			$post['payload'][] = array_merge($t, array('data' => $data)); // Add to the Payload
		}
	  // Payload->Data String
		else if(is_string($data)){
			$post['payload'][] = array_merge($t, array('data' => $data) ); // Add to the Payload
		}

		else {
			throw new Exception("Hmm... Pushthis is Pushed Out!");
		}

		return $this->curl_post($post, $this->config['server']);
	}

	/**
	 * Prepair the Request and Tell curl_post to do it.
	 * This funciton requires you to provide all of the Needed info for the Payload
	 *
	 * @param Array Send a Full Package
	 * @return String Response Text from the Server
	 */
	public function send_raw(Array $data){
		// Start the Request Data
			$post = array(
				"key" => $this->config['key'],
				"secret" => $this->config['secret'],
				"payload" => array(
					'channel' => $this->channel,
					'event'   => $this->event,
					'data'    => null
				)
			);
		$post['payload'] = array_merge($post['payload'], $data);
		return $this->curl_post($post, $this->config['server']);
	}
}
?>
