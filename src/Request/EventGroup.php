<?php
namespace GitOdin\Request;

/**
 *
 */
class EventGroup implements Payload {

  private $EventGroupBuffer = array();

  /**
   * Create the Event Group with adding payload data to the Buffer
   */
  public function __construct(){
    $events = func_get_args();
    foreach($events as $i => $v){
      if( !is_a($v, "GitOdin\Payload") ){
        // Remove Any that are not a Payload Type
        unset($events[$i]);
      }
    }
    $this->EventGroupBuffer = array_values($events);
  }

  /**
   * Method Needed from GitOdin\Payload
   *
   * @return Array Payload Data
   */
  public function getPayload(){
    $response = array();

    // Load all of the Event Data into a Response Package with the Proper Data
    foreach($this->EventGroupBuffer as $k => $Event){
      // Verify the Object has the basic Methods for the Payload Requests
      if(is_a($Event, 'GitOdin\Payload') || method_exists($Event, 'getPayload')){
        $response[] = $Event->getPayload();
      }
    }

    return $response;
  }

  /**
   * Add Object to the Buffer List
   *
   * @param GitOdin\Payload The Event you want to Add to the Group
   */
  public function add(GitOdin\Payload $e){
    $this->EventGroupBuffer[] = $e;
  }

}


?>
