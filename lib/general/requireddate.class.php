<?php

class RequiredDate extends Date {

	public function __construct($value=null) {

		if ($value == Date::C_NULL_VALUE) {
			throw new InvalidArgumentException('empty', 1000);
		}

		if (empty($value)) {
			throw new InvalidArgumentException('empty', 1000);
		}

		if (!preg_match(Date::C_INPUT_FORMAT, $value)) {
			throw new InvalidArgumentException('wrong-format', 1001);
		}

		parent::__construct($value);
	}
}