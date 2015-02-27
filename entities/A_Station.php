<?php

namespace Routelandia\Entities;

use Respect\Relational\Mapper;
use Routelandia\DB;

/**
 * Represents a single Station
 *
 * == Notes about Stations in the Portal Database ==
 * Station's have ID's grouped into thousands:
 *   1000's: Inductive loop detectors
 *   2000's: HOV lane detectors in vancouver. (Or maybe elsewhere later)
 *   3000's: HD Radar detectors.
 *   5000's: Onramp detectors.
 *
 * NOTE: This is defined in a file called A_Station, because the .php file
 * appears to need to come before (alphabetically) the OrderedStation class
 * declaration, otherwise OrderedStation can't seem to find the Station class.
 * This seems odd, to be sure, but I suspect it's one of the gotchas related
 * to __autoload() that the PHP docs warns you about.
 */
class Station {


  /************************************************************
   * STATIC CLASS FUNCTIONS
   ************************************************************/

  /**
   * Retrieve all results from the stations table.
   *
   * Returns everything, as a raw station object.
   *
   * @throws \Luracast\Restler\RestException
   */
  public static function fetchAll() {
    $ss = DB::mapper()->stations()->fetchAll();
    if(!$ss) {
      throw new \Luracast\Restler\RestException(404, "No stations could be found.");
    }

    //This should *hopefully* never happen

    /* If stations start using decoded JSON rather than just raw
     * Segments then we'll need this.
    foreach($ss as $elem) {
      $elem->decodeSegmentsJson();
    }
    */
    return $ss;
  }



  /**
   * Return a a single station with the given ID
   *
   * @throws \Luracast\Restler\RestException
   */
  public static function fetch($id) {
    $s = DB::mapper()->stations[$id]->fetch();
    if(!$s) {
      throw new \Luracast\Restler\RestException(404, "Station ID not found");
    }

    // Might need this later if we want decoded segments
    //$s->decodeSegmentsJson();
    return $s;
  }



  /**
   * Return all the stations for the highway with the given ID
   *
   * NOTE: This shouldn't be done. There should be a ".stations" on the Highway entity.
   * But for now...
   *
   * @throws \Luracast\Restler\RestException
   */
  public static function fetchForHighway($hid) {
    // TODO: This should use stations()->highways[$id] instead of hardcoding 'highwayid'.
    //         Unfortunately that seems to throw an error in Mapper.
    $ss = DB::mapper()->stations(array('highwayid='=>$hid))->fetchAll();
    if(!$ss) {
      throw new \Luracast\Restler\RestException(404, "No stations were found for the highway you requested");
    }

    /* Might need this later to decode raw segments
    foreach($ss as $elem) {
      $elem->decodeSegmentsJson();
    }
    */
    return $ss;
  }


  /**
   * Returns the ID of the onramp detector related to this station.
   *
   * Related onramps are detected by having the "same" ID in the 5000 range.
   * i.e. Station 1037 should have an onramp 5037, if such an onramp exists.
   * Note that onramps aren't useful for speed, because they're just a single loop.
   * @return int -1 if not possible, otherwise the ID that the onramp *should* be.
   */
  public static function calculateRelatedOnrampID($tid) {
    if($tid < 4000 && $tid >= 1000 ) {
      // First we strip it down to the base ID. (not in the thousands range.)
      while($tid > 1000) {
        $tid = $tid-1000;
      }

      return $tid+5000;
    } else {
      return null;
    }
  }


    /**Takes in a list of startstations and end stations and figures out which of them are valid
     *
     * Takes in a list of start stations and end stations and validates if they're on the same highway
     * and returns the only valid pairings (i.e. which stations properly appear after each other on a
     * highway)
     *
     * @param array $startStations
     * @param array $endStations
     * @return array $finalStationPair The valid start and end stations plus their highwayid
     * @throws Exception
     */
    public static function ReduceStationPairings($startStations,$endStations)
    {
        //Arrange stations into tuples separate by highwayIds
        $arrayOfHighwayIds = array();
        $x = 0;
        $startError = array();
        $endError = array();
        foreach($startStations as $skey=>$svalue)
        {
            array_push($startError,$svalue->stationid);
            foreach($endStations as $ekey=>$evalue)
            {
                array_push($endError, $evalue->stationid);

                if($svalue->highwayid == $evalue->highwayid)
                {
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
        if(empty($arrayOfHighwayIds)){
            $startString = implode(",",$startError);
            $endString = implode(",",$endError);
            throw new \Exception("Start Stations: $startString End Stations: $endString");
        }

        //Use tuple data structure to find correct start/end pair
        $finalStationPair = null;
        foreach($arrayOfHighwayIds as $highwayId => $stations) {
            $listOfHighwayStations = OrderedStation::fetchForHighway($highwayId);
            $startCount = 0;
            $endCount = 0;
            $finalHighwayId = 0;
            $finalStartStation = 0;
            $finalEndStation = 0;
            foreach($arrayOfHighwayIds as $akey=>$avalue){
                $count = 0;
                foreach($listOfHighwayStations as $highwayStation) {
                    ++$count;
                    foreach ($avalue as $stationkey => $stationvalue) {
                        for($x = 0; $x<=1;++$x) {
                            if ($stationvalue[$x] == $highwayStation->stationid) {
                                if ($startCount == 0) {
                                    $finalHighwayId = $highwayStation->highwayid;
                                    $startCount = $count;
                                    $finalStartStation = $highwayStation->stationid;
                                } else {
                                    $endCount = $count;
                                    $finalEndStation = $highwayStation->stationid;
                                }
                                break;
                            }
                        }
                    }
                }
                if($startCount < $endCount){
                    $finalStationPair[0] = $finalStartStation;
                    $finalStationPair[1] = $finalEndStation;
//          $finalStationPair[2] = $finalHighwayId;
                }
            }
        }
        return $finalStationPair;

    }
}
