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



  /******************************************************************************
   * STATIC CLASS METHODS
   ******************************************************************************/

  /**
   * Returns all useful highways
   *
   * Scopes highways to only those highways which actually have stations attached to them.
   * (We don't have much use for a highway with no stations in the context of this app...)
   * @return  [Highway] Useful highways.
   * @throws \Luracast\Restler\RestException
   */
  public static function fetchAll() {
    $hs = DB::mapper()->highwaysHavingStations->fetchAll();
    if(!$hs) {
      throw new \Luracast\Restler\RestException(404, "No highways were found.");
    }

    return $hs;
  }


  /**
   * Returns the single requested highway
   *
   * Will return whichever highwayID you request, regardless of if it's "useful" or not.
   *
   * @param $id
   * @return Highway The Highway entity representation.
   * @throws \Luracast\Restler\RestException
   */
  public static function fetch($id) {
    $h = DB::mapper()->highways[$id]->fetch();
    if(!$h) {
      throw new \Luracast\Restler\RestException(404, "Highway ID not found");
    }

    return $h;
  }
}
