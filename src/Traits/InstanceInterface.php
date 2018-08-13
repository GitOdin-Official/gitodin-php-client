<?php

namespace GitOdin\Traits;

// Legacy Version for things before version 5.6.0 and is compatible with current versions.

/**
 * Useing Classes you can use this to Create Instances and Get Current Instances.
 *  When you create your class add `use \Instance` after you start the first `{`
 * @link http://php.net/manual/en/language.oop5.traits.php#language.oop5.traits.precedence
 */

// Check if Instance Trait Exists, If it does not, Create Trait
trait InstanceInterface {
  private static $instance = null;

  /**
   * Static Funciton to Call
   * @return Self Instance.
   */
  public static function getInstance(){
    $in = func_get_args();
    if(self::$instance == null){
      //self::$instance = self::precall_getInstance($in);
      self::$instance = call_user_func_array("self::precall_getInstance", $in);
    }
    return self::$instance;
  }

  /**
   * Called to Create the Instance.
   *  Here you can make it get Cookie data to get some User ID or something
  *   to use for __construct of the class.
   * @return Self Instance.
   */
  private static function precall_getInstance(){
    $in = func_get_args();
    $reflect  = new \ReflectionClass(__CLASS__);
    return $reflect->newInstanceArgs($in);
  }

  /**
   * Used to Clear the Global Static Instance
   */
  public static function clearInstance(){
    self::$instance = null;
  }

  /**
	 * Check the Instance Var in the Class.
	 * @return bool If Instance is defined.
	 */
	public static function hasInstance(){
		if(self::$instance !== null){
			return true;
		}
		return false;
	}

  /**
   * Used to Set the Global Instance for the Class that is using it.
   */
  public function setGlobalInstance(){
    self::$instance = $this;
  }

}

?>
