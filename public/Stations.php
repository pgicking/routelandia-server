<?php

use Respect\Data\Collections\Filtered;
use Routelandia\Entities\OrderedStation;
use Routelandia\Entities\Detector;
use Routelandia\Entities\Station;

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
   * @return [Station] A list of all stations.
   */
  function index($highwayid=null) {
    return OrderedStation::fetchAll();
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
    return OrderedStation::fetch($id);
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
    return $retVal;
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
    return Detector::fetchForStation($id);
  }


  /** Checks if all the stations are on the same highway
   *
   * Checks if all the given stations are on the same highway
   * @param array $startStations
   * @param array $endStations
   * @return bool
   */
  //TODO: Join the station ids with same highwaysids into tuples, into sets by highwayids
  public static function ReduceStationPairings($startStations,$endStations)
  {
    //Arrange stations into tuples separate by highwayIds
    $arrayOfHighwayIds = array();
    foreach($startStations as $skey=>$svalue)
    {
      foreach($endStations as $ekey=>$evalue)
      {
        if($svalue->highwayid == $evalue->highwayid)
        {
//          print("\nDebugg: svalue->highwayid: ".$svalue->highwayid);
//          print("\nDebugg: svalue->stationid: ".$svalue->stationid);
//          print("\nDebugg: evalue->highwayid: ".$evalue->highwayid);
//          print("\nDebugg: evalue->stationid: ".$evalue->stationid);
          $tuple[0] = $svalue->stationid;
          $tuple[1] = $evalue->stationid;

          if (!array_key_exists($svalue->highwayid, $arrayOfHighwayIds))
          {
            //create empty index
            $arrayOfHighwayIds[$svalue->highwayid] = array();
          }
          array_push($arrayOfHighwayIds[$svalue->highwayid],$tuple);

        }
      }
    }
    //Use tuple data structure to find correct start/end pair
//    var_dump($arrayOfHighwayIds);
    $finalStationPair = null;
    foreach($arrayOfHighwayIds as $highwayId => $stations) {
//      settype($highwayId,"string");
      print("\nDebugg: Type: ".gettype($highwayId));
      print("\nDebugg: Value: ".$highwayId."\n");
      //TODO: Figure out why this keeps erroring out
      $listOfHighwayStations = OrderedStation::fetchForHighway($highwayId);
      $startCount = 0;
      $endCount = 0;
      $finalHighwayId = 0;
      foreach($arrayOfHighwayIds as $akey=>$avalue){
        $count = 0;
        foreach($listOfHighwayStations as $highwayStation) {
          ++$count;
//          print("\nDebugg: count: " . $count);
//          print("\nDebugg: avalue: ");
//          print_r($avalue[0]);
//          print(" stationid: " . $highwayStation->stationid);
          foreach ($avalue as $stationkey => $stationvalue) {
            for($x = 0; $x<=1;++$x) {
              print("\nDebugg: count: " . $count);
              print("\nDebugg: stationvalue: ");
              print_r($stationvalue[0]);
              print(" stationid: " . $highwayStation->stationid);

              if ($stationvalue[$x] == $highwayStation->stationid) {
                if ($startCount == 0) {
                  $finalHighwayId = $highwayStation->highwayid;
                  $startCount = $count;
                  $finalStartStation = $highwayStation->stationid;
                  print("\nDebugg: Startcount: " . $startCount);
                  print("\nDebugg: finalstartStation: " . $finalStartStation);
                } else {
                  $endCount = $count;
                  $finalEndStation = $highwayStation->stationid;
                  print("\nDebugg: endcount: " . $endCount);
                  print("\nDebugg: finalEndStation: " . $finalEndStation);
                }
              break;
              }
            }
          }
        }
        if($startCount < $endCount){
          $finalStationPair[0] = $finalStartStation;
          $finalStationPair[1] = $finalEndStation;
          $finalStationPair[2] = $finalHighwayId;
        }
      }
    }
    echo "\n\n";
    var_dump($finalStationPair);
    return $finalStationPair;

  }


}
