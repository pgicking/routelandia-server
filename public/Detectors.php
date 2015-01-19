<?php
/**
 * Created by PhpStorm.
 * User: pgicking
 * Date: 1/15/15
 * Time: 4:27 PM
 */

require_once "../database.php";
use Respect\Data\Collections\Filtered;

class Detectors
{

    function __construct()
    {
        DB::instance()->selectCols = Filtered::by('detectorid'
            , 'stationid'
            , 'enabledflag'
            , 'locationtext'
            , 'detectortype'
            , 'lanenumber'
            , 'controllerid'
            , 'end_date'
            , 'start_date'
            , 'milepost'
            , 'rampid'
            , 'enabledflag');
    }

    /**
     * Return a single detector
     *
     * Returns the detector with the provided stationid
     *
     * @acces public
     * @param int $id detector's database ID.
     * @return [Detector]
     */
    function get($id) {
        return DB::instance()->selectCols->detectors()[$id]->fetch();
    }

    /**
     * Return all detectors for a given station
     *
     * @access private
     * @param int $id The stationid to get detectors for
     * @return [Detector]
     */
    function getForStation($id) {
        return DB::instance()->selectCols->detectors(array('stationid='=>$id))->fetchAll();
    }
    /**
     * Return all available detectors
     *
     * Makes no effort to filter detectors in any way. This is the big list.
     *
     * @access public
     * @return [Detector] A list of all detectors.
     */
    function index($highwayid=null) {
        return DB::instance()->selectCols->detectors()->fetchAll();
    }

    /**
     * Indicate whether the detector is still active or not
     * aka does the detector have an end date in the database
     *
     * @param int $id id of the detector
     * @return bool
     * @url GET {id}/stillactive
     */
    public function stillActive($id){
        $thisDetector = DB::instance()->detectors[$id]->fetch();
        return $thisDetector->stillActive();
    }


}