<?php

use Respect\Data\Collections\Filtered;
use Routelandia\Entities\OrderedStation;
use Routelandia\Entities\Detector;
use Routelandia\Entities\Station;
use Routelandia\Entities\ApiResult;

require_once"../Util.php";

class Stations {

  /**
   * Return all available stations.
   *
   * This is the big list. All "relevant" stations are returned, and because
   * we're using the orderedstations view they are ordered by their position
   * inside *their* linked-list. This means that they'll be returned with all
   * HEADS first, followed by all first elements, etc...
   *
   * @access public
   * @param int $highwayid The desired Highway id
   * @return [Station] A list of all stations.
   */
  function index($highwayid=null) {
    return new ApiResult(OrderedStation::fetchAll());
  }



  /**
   * Return a single station.
   *
   * Returns the station with the provided stationid
   * NOTE: This will only return "relevant" stations, which are those that
   *       ID's >=100 and < 3000. Asking for any other stationid will result
   *       in an error since we are using the orderedstations view, which
   *       strips out the stations we don't care about.
   *
   * @acces public
   * @param int $id Station's database ID.
   * @return [Station]
   */
  function get($id) {
    return new ApiResult(OrderedStation::fetch($id));
  }


  /**
   * Return the ID of the related onramp for the given station
   *
   * Note that this is an example method that shows how you can create a custom
   * URL and call custom functions on a model.
   * This method violates the API spec by not returning JSON formatted data, and
   * should ultimately be replaced by mixing in a "relatedOnrampId" field to the
   * station model itself!
   *
   * @param int $id The station ID to calculate related onramp ID for
   * @return stdClass $retVal An object containing the ID searched for, the calculated result, and an array of relation onramps.
   * @throws \Luracast\Restler\RestException
   * @url GET {id}/relatedonramp
   */
  public function getRelatedOnramp($id) {
	try {
		$tempStation = Stations::get($id);
	} catch (Exception $e) {
		throw $e;
	}
	$retVal = new stdClass;
    $retVal->stationid = $id;
    $retVal->relatedOnrampId = Routelandia\Entities\Station::calculateRelatedOnrampID($id);
    try {
    	$retVal->relatedOnrampInfo = OrderedStation::fetchRelatedOnramp($retVal->relatedOnrampId);
    } catch (Exception $e) {
    	$retVal->relatedOnrampInfo = null;
    }
    return new ApiResult($retVal);
  }



  /**
   * Get detectors for the given station
   *
   * Returns a list of detectors associated with the
   * given station.
   *
   * @access public
   * @param int $id station ID
   * @return [Detector]
   * @url GET {id}/detectors
   */
  public function getDetectors($id) {
    return new ApiResult(Detector::fetchForStation($id));
  }




}
