<?php
namespace Routelandia\Entities;

use Luracast\Restler\RestException;
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
    return DB::instance()->detectors()->fetchAll();
  }

  /**
   * Return the detector with the given ID.
   */
  public static function fetch($id) {
    $d = DB::instance()->detectors()[$id]->fetch();
    if(!$d)
      throw new RestException(404, "Detector ID not found");
    return $d;
  }



  /**
   * Return all the detectors attached to a given station.
   *
   * This should probably be a method on Station rather than here, but for now this works.
   */
  public static function fetchForStation($stationid) {
    $d = DB::instance()->detectors(array('stationid='=>$stationid))->fetchAll();
    if(empty($d))
      throw new RestException(404, "Station ID not found");
    return $d;
  }

}
