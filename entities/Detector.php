<?php
namespace Routelandia\Entities;

use Respect\Relational\Mapper;

/** Represents a single Detector
 *
 * Stations are collections of detectors
 */
class Detector{

    /**
     * Detectors have a "start_date" and "end_Date value". This function will
     * determine if the detector is still active be looking if the end_date is null or not
     *
     * @return True or false
     */
    public function stillActive(){
        if($this->end_date == null)
            return true;
        else
            return false;
    }
}