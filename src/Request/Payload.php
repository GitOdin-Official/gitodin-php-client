<?php

namespace GitOdin\Request;

/**
 *
 */
interface Payload {

  /**
   * This Function will get all of the Requests that are needed to finish the request..
   *
   * @return Array The Payload to Send in the Request
   */
  public function getPayload();

}


?>
