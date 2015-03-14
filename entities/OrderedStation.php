<?php

namespace Routelandia\Entities;

// A bit of an ugly hack to get around the class loading order...
require_once 'Station.php';

use Respect\Relational\Mapper;
use Respect\Relational\Sql;
use Routelandia\DB;
use Routelandia\Util;

require_once"../Util.php";

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
  protected $segment_geom;
  protected $station_geom;

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
   *
   * @throws \Luracast\Restler\RestException
   */
  public function decodeSegmentsJson() {
    if(is_bool($this->geojson_raw = json_decode($this->segment_geom))){
      throw new \Luracast\Restler\RestException(404,"Invalid ID request");
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


  /************************************************************
   * STATIC CLASS FUNCTIONS
   ************************************************************/

  /**
   * Retrieve all results from the orderedStations view.
   *
   * Returns everything, formatted in the OrderedStations entity way.
   *
   * @throws \Luracast\Restler\RestException
   */
  public static function fetchAll() {
    $ss = DB::mapper()->orderedStations()->fetchAll();
    if(!$ss){
      throw new \Luracast\Restler\RestException(404, "No stations could be found");
    }

    foreach($ss as $elem) {
      $elem->decodeSegmentsJson();
    }
    return $ss;
  }



  /**
   * Return a a single station with the given ID
   *
   * @throws \Luracast\Restler\RestException
   */
  public static function fetch($id) {
    $s = DB::mapper()->orderedStations(array('stationid='=>$id))->fetch();
    if(!$s){
      throw new \Luracast\Restler\RestException(404, "Could not find the stationID requested");
    }

    $s->decodeSegmentsJson();
    return $s;
  }

  /**
   * Will take two coordinates and return the closest stations
   *
   * NOTE: Passed coordinates must have at least 6 significant digits or
   * nothing will be returned
   *
   * @param array $coord 2 float Coordinates from client
   * @return array [OrderedStation] List of ordered stations
   */
  static public function getStationsFromCoord($coord){
     $s = Sql::select('*')->from('stations')->where("end_date IS NULL AND ST_Distance(ST_Transform(ST_GeomFromText('POINT($coord[1] $coord[0])', 4326), 3857), segment_geom) <= 500");
     $ss = DB::sql()->orderedStations()->query($s)->fetchAll('Routelandia\Entities\OrderedStation');
     if(empty($ss)){
         throw new \Luracast\Restler\RestException(400,"Could not find any stations within 200 meters of the given coordinates");
     }
    return $ss;
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
    $ss = DB::mapper()->orderedStations(array('highwayid='=>$hid))->fetchAll();
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
  public static function fetchRelatedOnramp($id) {
    return Station::fetch($id);
  }


  /**
   * Takes in a station ID and gives back the ID of the next station in the linked list.
   * @param int $id The ID of the station to get downstream for.
   * @return int The ID of the downstream station.
   */
  public static function getDownstreamIdFor($id) {
    return Station::fetch($id)->downstream;
  }
}
