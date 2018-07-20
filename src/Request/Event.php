<?php
namespace GitOdin;

require_once("Payload.base.php");

/**
 *
 */
class Event implements Payload {

  private $EventData = array(
    'channel' => "",
    'event'   => "",
    'data'    => null
  );

  public function __construct(String $channel, String $event, $data){
    $this->EventData['channel'] = $channel;
    $this->EventData['data'] = $data;
    $this->EventData['event'] = $event;
  }

  public function getPayload(){
    return $this->EventData;
  }

}


?>
