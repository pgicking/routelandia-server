<?php

use Respect\Data\Collections\Filtered;
use Routelandia\Entities\Detector;
use Routelandia\Entities\OrderedStation;

class Detectors
{

    /**
     * Return a single detector
     *
     * Returns the detector with the provided detectorid
     *
     * @acces public
     * @param int $id detector's database ID.
     * @return [Detector]
     */
    function get($id) {
      return Detector::fetch($id);
    }

    /**
     * Return all detectors for a given station
     *
     * Will return all the detectors associated with that station
     *
     * @access private
     * @param int $id The stationid to get detectors for
     * @return [Detector]
     */
    function getForStation($id) {
      Detector::fetchForStation($id);
    }


    /**
     * Return all available detectors
     *
     * Makes no effort to filter detectors in any way. This is the big list.
     *
     * @access public
     * @return [Detector] A list of all detectors.
     */
    function index() {
      return Detector::fetchAll();
    }

    /**
     * Indicate whether the detector is still active or not
     *
     * Indicate whether or not the detector is still active or not.
     * Aka does the detector have an end date in the database.
     *
     * @param int $id id of the detector
     * @return bool
     * @url GET {id}/stillactive
     */
    public function stillActive($id){
      $thisDetector = Detector::fetch($id);
      return $thisDetector->stillActive();
    }

    /**
     * Get the associated station for the given detector id
     *
     * Returns the associated station JSON object when given a detector ID
     *
     * @param int $detectorid The detectors ID
     * @return [Station] The associated station
     * @url GET {detectorid}/relatedstation
     */
    public function RelatedStation($detectorid){
        $thisDetector = Detector::fetch($detectorid);
        $s = OrderedStation::Fetch($thisDetector->stationid);
        $s->decodeSegmentsJson();
        return $s;
    }
}
