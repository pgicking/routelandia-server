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

  // Hide some of the params that the client won't need.
  protected $highwaylength;
  protected $startmp;
  protected $endmp;

  /**
   * @Relational\isNotColumn
   */
  public $fullGeoJson;



  /**
   * Gets all the stations for this highway, and concatenates their JSON segments together
   * into a single giant JSON polyline, saving that into the $fullGeoJson field.
   */
  private function buildBigLine() {
    $output = new \stdClass();
    $output->type = "Linestring";
    $output->coordinates = array();

    $ss = OrderedStation::fetchForHighway($this->highwayid);
    foreach($ss as $ts) {
      // This is sort of a bad hack. It results in the fullGeoJson object being present, but not
      // having any coordinates.
      // It would be preferable if the fullGeoJson was simply null if there were no stations to
      // get coordinates from.
      if($ts->geojson_raw) {
        foreach($ts->geojson_raw->coordinates as $tc) {
          $output->coordinates[] = $tc;
        }
      }
    }

    $this->fullGeoJson = $output;
  }

  /******************************************************************************
   * STATIC CLASS METHODS
   ******************************************************************************/

  /**
   * Returns all useful highways
   *
   * Scopes highways to only those highways which actually have stations attached to them.
   * (We don't have much use for a highway with no stations in the context of this app...)
   *
   * @return [Highway] Useful highways.
   */
  public static function fetchAll() {
    $hs = DB::instance()->highwaysHavingStations->fetchAll();
    foreach($hs as $elem) {
      $elem->buildBigLine();
    }
    return $hs;
  }


  /**
   * Returns the single requested highway
   *
   * Will return whichever highwayID you request, regardless of if it's "useful" or not.
   *
   * @return Highway The Highway entity representation.
   */
  public static function fetch($id) {
    $h = DB::instance()->highways[$id]->fetch();
    $h->buildBigLine();
    return $h;
  }
}
