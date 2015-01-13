<?php

require_once "../database.php";
use Respect\Data\Collections\Filtered;

class Stations {

  function __construct() {
    // Filter out the columns we don't care for from the stations table
    DB::instance()->selectCols = Filtered::by('stationid'
                                                ,'highwayid'
                                                ,'milepost'
                                                ,'locationtext'
                                                ,'length'
                                                ,'upstream'
                                                ,'downstream'
                                                ,'opposite_stationid'
                                                ,'point'
                                                ,'segment_raw'
                                                ,'segment_50k'
                                                ,'segment_100k'
                                                ,'segment_250k'
                                                ,'segment_500k'
                                                ,'segment_1000k');
  }


  /**
   * Return all available stations.
   *
   * Makes no effort to filter stations in any way. This is the big list.
   *
   * @access public
   * @return [Station] A list of all stations.
   */
  function index($highwayid=null) {
    return DB::instance()->selectCols->stations()->fetchAll();
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
    return DB::instance()->selectCols->stations()[$id]->fetch();
  }


  /**
   * Return all stations for a specific highway.
   *
   * @access private
   * @param int $id The highwayid to get stations for
   * @return [Station]
   */
  function getForHighway($id) {
    // TODO: This should use stations()->highways[$id] instead of hardcoding ID.
    //         Unfortunately that seems to throw an error in Mapper.
    return DB::instance()->selectCols->stations(array('highwayid='=>$id))->fetchAll();
  }

  /**
   * Return the ID of the related onramp for the given station
   *
   * @param int $id The station ID to calculate related onramp ID for
   * @return int
   * @url GET {id}/relatedonrampids
   */
  public function getRelatedOnrampIds($id) {
    $thisStation = DB::instance()->stations[$id]->fetch();
    return $thisStation->getRelatedOnrampID();
  }

}
