<?php

namespace Routelandia\Entities;

class ApiResult {
	function __construct($input, $debug = null) {
		is_array($input) ? $this->results = $input : $this->results = array($input);
		is_null($debug) ? : $this->debug = $debug;
	}
}
