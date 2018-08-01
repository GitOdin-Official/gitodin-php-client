<?php
namespace GitOdin;

require_once("Payload.base.php");

/**
 * This is for allowing Socket Connections with the Server
 */
class Authentication implements Payload {

  const Allow = true;
  const Deny = false;

  private $EventData = array(
    'channel' => null,
    'authorized'   => false,
    'socket_id'    => 0
  );

  /**
   * Create Authentication Instance of the Data
   *
   * @param String Socket Id to allow/disallow
   * @param String Channel Name
   * @param Boolean Allow or Deny
   */
  public function __construct(String $socket, String $channel, boolean $auth = null){
    $this->EventData['socket_id'] = $socket;
    $this->EventData['channel'] = $channel;
    if($auth == null){
      $this->EventData['authorized'] = true;
    }
    else {
      $this->EventData['authorized'] = $auth;
    }
  }

  /**
   * This Function will get all of the Requests that are needed to finish the request..
   *
   * @return Array The Payload to Send in the Request
   */
  public function getPayload(){
    return $this->EventData;
  }


}


?>
