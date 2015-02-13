<?php

namespace Routelandia\Entities;

use Routelandia\DB;

/**
 * Simply a different view, containing all Highway data.
 * So we'll just make it do exactly what a highway does.
 */
class HighwaysHavingStation extends Highway {

  /**
   * Return an array of tuples, each tuple being the opposite highwayid of the other.
   * i.e. NB I-5 and SB I-5 are in the same tuple.
   */
  public static function pairs() {
    $pairs = DB::sql()->select("array_agg(highwayid) as \"agg\"")->from("highwaysHavingStations")->group("")->by("highwayname")->fetchAll();
    $res = Array();
    foreach($pairs as $p) {
      array_push($res, array_map("intval", explode(',', trim($p->agg, '{}'))));
    }
    return $res;
  }
}
