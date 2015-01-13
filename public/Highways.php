<?php

require_once "../database.php";

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
    return DB::instance()->highways->fetchAll();
  }


  /**
   * Return only a single highway.
   *
   * Returns the highway with the passed in ID with no processing or alteration.
   *
   * @access public
   * @param int $id The database ID of the highway you'd like to view.
   * @return Highway The highway requested.
   */
  function get($id) {
    return DB::instance()->highways[$id]->fetch();
  }


  /**
   * Get stations for the specified highway
   *
   * Scopes stations and returns only the stations attached to the highwayid provided.
   * This method does not limit the types of stations shown in any way.
   *
   * @access public
   * @param int $id Highway ID
   * @return [Station]
   * @url GET {id}/stations
   */
  public function getStations($id) {
    $s = new Station;
    return $s->getForHighway($id);
  }

}
