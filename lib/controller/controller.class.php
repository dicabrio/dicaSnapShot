<?php

/**
 * Every controller should implement this interface 
 *
 */
interface Controller {
	
	/**
	 * Index when no metod is specified
	 *
	 */
	public function _index();
	
	/**
	 * Default method if no other method is found
	 *
	 */
	public function _default();
	
	/**
	 * Set the arguments to the controller
	 *
	 * @param mixed $arguments
	 */
	public function setArguments($arguments);
}

class ControllerException extends Exception {};

?>