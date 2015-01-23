<?php

namespace Routelandia\Entities;

use Respect\Relational\Mapper;
use Routelandia\DB;

/**
 * Represents a single highway.
 *
 */
class Highway {
  public $highwayid;
  public $direction;
  public $highwayname;
  public $bound;
  public $fullGeoJson;

  // Hide some of the params that the client won't need.
  protected $highwaylength;
  protected $startmp;
  protected $endmp;


  /**
   * Gets all the stations for this highway, and concatenates their JSON segments together
   * into a single giant JSON polyline, saving that into the $fullGeoJson field.
   */
  function buildBigLine() {
    $output = new \stdClass();
    $output->type = "Linestring";
    $output->coordinates = array();

    $ss = OrderedStation::fetchForHighway($this->highwayid);
    foreach($ss as $ts) {
      foreach($ts->geojson_raw->coordinates as $tc) {
        $output->coordinates[] = $tc;
      }
    }

    $this->fullGeoJson = $output;
  }

  /******************************************************************************
   * STATIC CLASS METHODS
   ******************************************************************************/

  /**
   * Returns all highways
   */
  public static function fetchAll() {
    return DB::instance()->highways->fetchAll();
  }


  /**
   * Returns the single requested highway
   */
  public static function fetch($id) {
    return DB::instance()->highways[$id]->fetch();
  }
}
