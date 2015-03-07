<?php

namespace Routelandia\Entities;

/**
 * A formatter class which will ensure our results always appear in the way that we want when output by the API.
 * (i.e. results is always an array!)
 * Only outputs the optional $debug parameter if Restler is not running in production mode, allowing you to have
 * code set up to print debug data, but not risk exposing that data in production.
 *
 * Constructor has the following parameters.
 * @param input This value will go into the "Results" array!
 * @param debug Optional parameter of debug info which will be output only if Restler is NOT in production mode.
 */
class ApiResult {
  function __construct($input, $debug = null) {
    // If we're NOT inproduction mode than output the debug!
    if($GLOBALS['RUN_IN_PRODUCTION'] != true) {
      if(!is_null($debug)) { $this->debug = $debug; }
    }

    is_array($input) ? $this->results = $input : $this->results = array($input);
  }
}
