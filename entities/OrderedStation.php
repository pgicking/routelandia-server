<?php

namespace Routelandia\Entities;

use Respect\Relational\Mapper;

/**
 * An OrderedStation is much like a
 * station, except it's coming in via
 * the OrderedStations view, which scopes
 * both the columns chosen, and which stations
 * are selected. (Throws out all but 1000/3000)
 */
class OrderedStation {


  /**
   * Decodes the "string" of JSON returned by postgres
   * to an actual object so it can be printed correctly.
   * NOTE: This should be handled automatically by the ORM
   *       which is something we'll continue to work on, but
   *       in the meantime this gets the JSON out to the API
   *       so the client team can continue to move forward.
   */
  public function decodeSegmentsJson() {
    $this->segment_raw = json_decode($this->segment_raw);
  }


  // NOTE: Copy-paste from Station.php! This is bad!
  // However, I suspect we'll be removing Station.php in favor of
  // just using orderedStations instead. If we do, no problem.
  // If we don't: This should be moved to an include that is used
  // by both entities.
  /**
   * Returns the ID of the onramp detector related to this station.
   *
   * Related onramps are detected by having the "same" ID in the 5000 range.
   * i.e. Station 1037 should have an onramp 5037, if such an onramp exists.
   * Note that onramps aren't useful for speed, because they're just a single loop.
   * @return int -1 if not possible, otherwise the ID that the onramp *should* be.
   */
  public function getRelatedOnrampID() {
    $tid = $this->stationid;
    if($tid >= 1000 && $tid < 4000) {
      // First we strip it down to the base ID. (not in the thousands range.)
      while($tid > 1000) {
        $tid = $tid-1000;
      }

      return $tid+5000;
    } else {
      return null;
    }
  }
}
