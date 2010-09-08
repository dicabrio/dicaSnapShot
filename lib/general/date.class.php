<?php

class Date extends DomainText {

	const C_INPUT_FORMAT = "/\d{4}-\d{1,2}-\d{1,2}/";

	const C_OUTPUT_FORMAT = 'Y-m-d';

	const C_NULL_VALUE = '0000-00-00';

	public function __construct($value=null) {
		
		if (empty($value) || self::C_NULL_VALUE == $value) {
			$value = self::C_NULL_VALUE;
		} 

		parent::__construct($value);
	}
}