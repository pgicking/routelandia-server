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
