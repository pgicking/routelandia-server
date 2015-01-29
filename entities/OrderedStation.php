<?php

namespace Routelandia\Entities;

use Luracast\Restler\RestException;
use Respect\Relational\Mapper;
use Routelandia\DB;

/**
 * Represents a single row in the orderedStations view.
 *
 * An OrderedStation is much like a
 * station, except it's coming in via
 * the OrderedStations view, which scopes
 * both the columns chosen, which stations
 * are selected (Throws out all but 1000/3000),
 * and orders the station by their position in
 * the linked-list of stations.
 * The orderedStations view is designed to give
 * either a single station, or all stations for a
 * specific highway. (The ordering doesn't make much
 * sense when you select all of them)
 */
class OrderedStation extends Station {

  public $stationid;
  public $upstream;
  public $downstream;
  public $highwayid;
  public $opposite_stationid;
  public $milepost;
  public $length;
  public $locationtext;
  protected $linked_list_path;
  public $linked_list_position;

  // We're going to convert these and output them as the geojson_x columns
  // so we'll hide the raw columns by setting them protected.
  protected $segment_raw;
  protected $segment_50k;
  protected $segment_100k;
  protected $segment_250k;
  protected $segment_500k;
  protected $segment_1000k;

  /**
   * @Relational\isNotColumn
   */
  public $geojson_raw;


  /************************************************************
   * PRIVATE CLASS FUNCTIONS
   ************************************************************/

  /**
   * Decodes the "string" of JSON returned by postgres
   * to an actual object so it can be printed correctly.
   * NOTE: This should be handled automatically by the ORM
   *       which is something we'll continue to work on, but
   *       in the meantime this gets the JSON out to the API
   *       so the client team can continue to move forward.
   */
  public function decodeSegmentsJson() {
    if(is_bool($this->geojson_raw = json_decode($this->segment_raw))){
      throw new RestException(404,"Invalid ID request");
    }
  }



  /**
   * Decode the "array" string returned by postgres into an actual array
   *
   * PHP docs say to do this. [ sigh ] Apparently PHP can't interpret the
   * column AS an array, which it really ought to be doing.
   */
  private function linkedListPathAsArray() {
    $r = str_getcsv(str_replace('\\\\', '\\', trim($this->linked_list_path, "{}")), ",", "");
    return array_map('intval', $r);
  }

  /** Will take two coordinates and return the closest station
   *
   * Will take two coordinates and return the closest station
   *
   * TODO: Pass coords into SQL query to let postGIS do the heavy lfting finding the closest station
   *
   * @param array $coord 2 float Coordinates from client
   * @return [OrderedStation] [NYI]
   */
  public function getStationFromCoord($coord){
    return $coord;
  }

  /************************************************************
   * STATIC CLASS FUNCTIONS
   ************************************************************/

  /**
   * Retrieve all results from the orderedStations view.
   *
   * Returns everything, formatted in the OrderedStations entity way.
   */
  public static function fetchAll() {
    $ss = DB::instance()->orderedStations()->fetchAll();
    if(!$ss){
      throw new RestException(404, "No stations could be found");
    }

    foreach($ss as $elem) {
      $elem->decodeSegmentsJson();
    }
    return $ss;
  }



  /**
   * Return a a single station with the given ID
   *
   */
  public static function fetch($id) {
    $s = DB::instance()->orderedStations(array('stationid='=>$id))->fetch();
    if(!$s){
      throw new RestException(404, "Could not find the stationID requested");
    }

    $s->decodeSegmentsJson();
    return $s;
  }


  /**
   * Return all the stations for the highway with the given ID
   *
   * NOTE: This shouldn't be done. There should be a ".stations" on the Highway entity.
   * But for now...
   */
  public static function fetchForHighway($hid) {
    // TODO: This should use stations()->highways[$id] instead of hardcoding 'highwayid'.
    //         Unfortunately that seems to throw an error in Mapper.
    $ss = DB::instance()->orderedStations(array('highwayid='=>$hid))->fetchAll();
    if(!$ss) {
      throw new \Luracast\Restler\RestException(404, "No stations for the requested highway could be found");
    }

    foreach($ss as $elem) {
      $elem->decodeSegmentsJson();
    }
    return $ss;
  }


  /**
   * Accepts an station ID and returns the related onramps.
   *
   * Currently will only return a single onramp, but the possibility is there...
   */
  public static function fetchRelatedOnramps($id) {
    return Station::fetch($id);
  }
}
