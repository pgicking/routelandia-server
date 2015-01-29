<?php
namespace Routelandia\Entities;

use Respect\Relational\Mapper;
use Routelandia\DB;

/** Represents a single Detector
 *
 * Stations are collections of detectors
 */
class Detector{

  public $detectorid;
  public $stationid;
  public $locationtext;
  public $lanenumber;
  public $end_date;
  public $start_date;

  // Hide some of the database attributes
  protected $enabledflag;
  protected $detectortype;
  protected $controllerid;
  protected $rampid;
  protected $milepost;
  protected $detectorclass;
  protected $detectortitle;
  protected $detectorstatus;


  /**
   * Detectors have a "start_date" and "end_Date value". This function will
   * determine if the detector is still active be looking if the end_date is null or not
   *
   * @return True or false
   */
  public function stillActive(){
      if($this->end_date == null)
          return true;
      else
          return false;
  }



  /******************************************************************************
   * STATIC CLASS METHODS
   ******************************************************************************/


  /**
   * Return all detectors, making no effort to filter them.
   */
  public static function fetchAll() {
    return DB::mapper()->detectors()->fetchAll();
  }

  /**
   * Return the detector with the given ID.
   *
   * @throws \Luracast\Restler\RestException
   */
  public static function fetch($id) {
    $d = DB::mapper()->detectors()[$id]->fetch();
    if(!$d) {
      throw new \Luracast\Restler\RestException(404, "Requested Detector ID not found");
    }

    return $d;
  }



  /**
   * Return all the detectors attached to a given station.
   *
   * This should probably be a method on Station rather than here, but for now this works.
   *
   * @throws \Luracast\Restler\RestException
   */
  public static function fetchForStation($stationid) {
    $d = DB::mapper()->detectors(array('stationid='=>$stationid))->fetchAll();
    if(empty($d)) {
      throw new \Luracast\Restler\RestException(404, "No detectors for the requested Station ID could be found");
    }

    return $d;
  }

}
