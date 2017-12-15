<?php

//      ____                     __     __     __      _                _        
//     / __ \  __  __   _____   / /_   / /_   / /_    (_)   _____      (_)  ____ 
//    / /_/ / / / / /  / ___/  / __ \ / __/  / __ \  / /   / ___/     / /  / __ \
//   / ____/ / /_/ /  (__  )  / / / // /_   / / / / / /   (__  )  _  / /  / /_/ /
//  /_/      \__,_/  /____/  /_/ /_/ \__/  /_/ /_/ /_/   /____/  (_)/_/   \____/ 
//                                                                               
define("PUSHTHIS_VERSION_PHP", 1.0);

class PushThis {
	private $servers = array(
		"na" => "http://na.pushthis.io/api",
		"eu" => "http://eu.pushthis.io/api"
	);
	private $config = array();
	public $channel = null;
	public $event = null;
	public $messageQueue = array();
	
	public function __construct($key = null, $secret = null, $region_server_name = "na"){
		if($key === null || $secret === null){
			throw new PushThisException("Key or Secret not Provided!");
		}
		
		$this->config['key'] = $key;
		$this->config['secret'] = $secret;
		
		// Set the Server based off of the Region Tag or set by URL in.
		$this->config['server'] = isset($this->servers[$region_server_name]) ? $this->servers[$region_server_name] : $region_server_name;
	}
	
	private function get_extension($file) {
		$extension = end(explode(".", $file));
		return $extension ? $extension : false;
	}
	
	/**
	 * Checks if the String is an Array
	 * @link https://stackoverflow.com/questions/6041741/
	 */
	private function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	
	/**
	 * JSON, ARRAY
	 * Reurns an Array if it is an Array or a JSON Array.
	 */
	private function ja($str) {
		if(is_array($str)) { return $str; }
		else {
			$r = json_decode($string);
			if(json_last_error() == JSON_ERROR_NONE){
				return $r;
			}
		}
		return false;
	}
	
	/**
	 * Using CURL Make the Request
	 */
	private function curl_post($data, $url){
		$content = json_encode($data);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Pushthis-PHP/".PUSHTHIS_VERSION_PHP);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($content)
			));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	// Used With Single Payloads
	public function set_channel ($in){
		return $this->channel = $in;
	}
	public function set_event ($in){
		return $this->event = $in;
	}
	
	/**
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
	 * Prepair the Request and Tell curl_post to do it.
	 * This Function just needs the Payload Data and you can Provide the 
	 */
	public function send($data = null){
		// Start the Request Data
		$post = array(
			"key" => $this->config['key'],
			"secret" => $this->config['secret'],
			"payload" => array()
		);
		$t = array(
			'channel' => $this->channel,
			'event'   => $this->event,
			'data'    => null
		); // Payload Template
		
		
		// Check if you are Sending for the Pending Payloads.
		if($data === null) {
			if(!empty($this->messageQueue)){
				// Running for Message Queue
				$post['payload'] = $this->messageQueue; // Add the Messages to the Payload
				$this->messageQueue = array(); // Clear Queue
			}
			else{ /* Queue is Empty! */ return false; }
		}
	///// NOT QUEUE RELATED
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
		//return json_encode($post);
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
			)
		);
		$post['payload'] = array_merge($post['payload'], $data);
		print_r($post);
		//return $this->curl_post($post, $this->config['server']);
	}
}
?>