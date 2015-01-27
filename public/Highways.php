<?php

// Bring some things into local scope for convenience.
use Routelandia\Entities\Highway;
use Routelandia\Entities\OrderedStation;

class Highways {

  /**
   * Return a list of all available highways.
   *
   * Simply returns every row from the highways table.
   *
   * @access public
   * @return [Highway] A list of available highways.
   */
  function index() {
    return Highway::fetchAll();
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
    return Highway::fetch($id);
  }


  /**
   * Get stations for the specified highway
   *
   * Retrieves all relevant stations for the specific highway, ordered by the
   * order they are in as part of the linked-list of stations representing this
   * highway.
   *
   * @access public
   * @param int $id Highway ID
   * @return  [Station]
   * @throws \Luracast\Restler\RestException
   * @url GET {id}/stations
   */
  public function getStations($id) {
    $h = OrderedStation::fetchForHighway($id);
    if(empty($h))
      throw new \Luracast\Restler\RestException(404, "Highway ID not found");
    return $h;
  }

}
