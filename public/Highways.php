<?php

// Bring some things into local scope for convenience.
use Routelandia\Entities\Highway;
use Routelandia\Entities\HighwaysHavingStation;
use Routelandia\Entities\OrderedStation;
use Routelandia\Entities\ApiResult;

class Highways {

  /**
   * Return a list of all useful highways.
   *
   * This returns only highways which actually have stations attached to them, since those are the only stations
   * that are useful for our purposes.
   * Other highways will exist, but without stations we can't use them to generate statistics, so we're pretending
   * that they don't exist for the purpose of this list.
   *
   * @access public
   * @return [Highway] A list of available highways.
   */
  function index() {
    return new ApiResult(HighwaysHavingStation::fetchAll());
  }


  /**
   * Return only a single highway.
   *
   * Returns the highway with the passed in ID with no processing or alteration.
   *
   * @access public
   * @param int $id The database ID of the highway you'd like to view.
   * @return [Highway] The highway requested.
   */
  //Do NOT change the return statement to @return Highway [Highway] unless you want a bad day
  function get($id) {
    return new ApiResult(Highway::fetch($id));
  }


  /**
   * Get stations for the specified highway
   *
   * Retrieves all relevant stations for the specific highway, ordered by the
   * order they are in as part of the linked-list of stations representing this
   * highway.
   *
   * NOTE: We have uncovered at least 2 highways (12 and 54) that have more than one linked-list in them!
   *       This problem is part of the client dataset and so no workaround is available.
   *       These cause problems in the ordering of the returned stations, as it interleaves the linked lists.
   *       (i.e. all "first" elements, then all "second" elements...)
   *       Clients wishing to work with these linked lists
   *
   * @access public
   * @param int $id Highway ID
   * @return  [Station]
   * @throws \Luracast\Restler\RestException
   * @url GET {id}/stations
   */
  public function getStations($id) {
    return new ApiResult(OrderedStation::fetchForHighway($id));
  }


  /**
   * Show the pairs of freeways that we're working with.
   *
   * Every freeway should have a corresponding "opposite" freeway heading the other direction.
   * In order to be able to color a "highway" the same color we need to know what these opposite
   * highways are, so they can both be the same color.
   *
   * This will return an array of tuples, each tuple representing the paired highwayids.
   *
   * @url GET /pairs
   */
  public function getPairs() {
    return new ApiResult(HighwaysHavingStation::pairs());
  }

}
