<?php
namespace Pushthis;

//      ____                     __     __     __      _                _        
//     / __ \  __  __   _____   / /_   / /_   / /_    (_)   _____      (_)  ____ 
//    / /_/ / / / / /  / ___/  / __ \ / __/  / __ \  / /   / ___/     / /  / __ \
//   / ____/ / /_/ /  (__  )  / / / // /_   / / / / / /   (__  )  _  / /  / /_/ /
//  /_/      \__,_/  /____/  /_/ /_/ \__/  /_/ /_/ /_/   /____/  (_)/_/   \____/ 

define("PUSHTHIS_VERSION_PHP", 1.0);
define("PUSHTHIS_VERSION_NAME", "PUSHTHIS_PHP_API_".PUSHTHIS_VERSION_PHP);

class Pushthis {
	private $config = array();
	public $channel = null;
	public $event = null;
	public $messageQueue = array();
	private $pem_cert = null;
	public $errors = array();
	
	public function __construct($key, $secret, $region_server_name){
		$this->config['key'] = $key;
		$this->config['secret'] = $secret;
		$this->config['server'] = $region_server_name;
	}
	
	/**
	 * Here you can specify the Root CA for CURL to verity the Host Connection.
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
	 * @link https://regex101.com/r/fTPAZJ/2/
	 */
	public function is_url_auth(){
		$m = preg_match_all("/^((http[s]?):\/)?\/?([^:\/\s]+)((\/\w+)*\/)auth/", $this->config['server']);
		return $m;
	}
	
	/**
	 * Check URL to see if it Ends with /api
	 * @link https://regex101.com/r/fTPAZJ/2/
	 */
	public function is_url_api(){
		$m = preg_match_all("/^((http[s]?):\/)?\/?([^:\/\s]+)((\/\w+)*\/)api/", $this->config['server']);
		return $m;
	}
	
	/** 
	 * Allow or Deny access to a Private Channel by the Socket Id and channel.
	 */
	public function authorize($allow = false, $channel, $socketId){
		if(!$this->is_url_auth()) { 
			// THE URL IS NOT CORRECT FOR A AUTH REQUEST
			$this->errors[] = "URL is not the Auth Access Point. Please Refer to the Pushthis.io Pocumentation for more information.";
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
	 */
	private function curl_post($data, $url){
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
		curl_setopt($ch, CURLOPT_USERAGENT, "Pushthis-PHP/".PUSHTHIS_VERSION_PHP);
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
	 * Used to set defaults in Requests.
	 */
	public function set_channel ($in){
		return $this->channel = $in;
	}
	public function set_event ($in){
		return $this->event = $in;
	}
	
	/**
	 * Is Multidimensional Array
	 * @link https://pageconfig.com/post/checking-multidimensional-arrays-in-php
	 */
	private function is_marray( $arr ) {
		if(!is_array($arr)) { return false; }
		unset($arr['data']); // Remove False Posative
		rsort( $arr );
		return isset( $arr[0] ) && is_array( $arr[0] );
	}
	
	/**
	 * Add another Data to the Payload
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
	 */
	public function send($data = null){
		if($this->is_url_api()) { 
			// THE URL IS NOT CORRECT FOR A SEND REQUEST
			$this->errors[] = "URL is not the API Access Point. Please Refer to the Pushthis.io Pocumentation for more information.";
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
		
		
	  /// Check if you are Sending for the Pending Payloads.
		if($data === null) {
			if(!empty($this->messageQueue)){
				// Running for Message Queue
				$post['payload'] = $this->messageQueue; // Add the Messages to the Payload
				$this->messageQueue = array(); // Clear Queue
			}
			else{ /* Queue is Empty! */ return false; }
		}
	  /// Payload->Data Array, Multi Request
		else if($this->is_marray($data)){
			// Handle if it is a Multidimensional Array
			$post['payload'] = array_merge( $post['payload'], $data );
		}
	  /// Payload->Data Array, Single Request
		else if(is_array($data) && ( isset($data['channel']) || isset($data['event']) || isset($data['data']) )){
			// Running for the Payload Defenition that allows for 
			$post['payload'][] = array_merge($t, $data); // Add to the Payload
		}
	  /// Payload->Data Array
		else if(is_array($data)){
			$post['payload'][] = array_merge($t, array('data' => $data)); // Add to the Payload
		}
	  /// Payload->Data String
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
	 */
	public function send_raw($data){
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