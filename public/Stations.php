<?php

require_once "../database.php";
use Respect\Data\Collections\Filtered;

class Stations {

  /**
   * Return all available stations.
   *
   * Makes no effort to filter stations in any way. This is the big list.
   *
   * @access public
   * @return [Station] A list of all stations.
   */
  function index($highwayid=null) {
    $ss = DB::instance()->orderedStations()->fetchAll();
    foreach($ss as $elem) {
      $elem->decodeSegmentsJson();
    }
    return $ss;
  }


  /**
   * Return a single station.
   *
   * Returns the station with the provided stationid
   *
   * @acces public
   * @param int $id Station's database ID.
   * @return Station
   */
  function get($id) {
    $s = DB::instance()->orderedStations(array('stationid='=>$id))->fetch();
    $s->decodeSegmentsJson();
    return $s;
  }


  /**
   * Return all stations for a specific highway.
   *
   * @access private
   * @param int $id The highwayid to get stations for
   * @return [Station]
   */
  function getForHighway($id) {
    // TODO: This should use stations()->highways[$id] instead of hardcoding 'highwayid'.
    //         Unfortunately that seems to throw an error in Mapper.
    $ss = DB::instance()->orderedStations(array('highwayid='=>$id))->fetchAll();
    foreach($ss as $elem) {
      $elem->decodeSegmentsJson();
    }
    return $ss;
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
   * @return int
   * @url GET {id}/relatedonrampid
   */
  public function getRelatedOnrampId($id) {
    $thisStation = DB::instance()->orderedStations(array('stationid='=>$id))->fetch();
    return $thisStation->getRelatedOnrampID();
  }

}
