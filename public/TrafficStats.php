<?php

require_once "../database.php";

use Respect\Data\Collections\Filtered;

class TrafficStats{

    /**
     * Takes two station id, returns traffic information
     *
     * Takes two station ids and calculates all the stations inbetween them
     * to get traffic information for the segment of highway
     *
     * @param $start
     * @param $end
     * @return mixed
     * @throws exception
     * @url GET {$start}/{$end}
     */
    public function trafficInfo($start,$end){
        $s = new Station;

        if($s->isValid($start)){
            throw new exception('Invalid {$start} station id');
        }
        elseif ($s->isValid($end)){
            throw new exception('Invalid {$end} station id');
        }
        else
            return "Traffic info will go here";
    }

    /** A function created out of frustration
     *
     * Trying to debug why other functions aren't seen
     * by the web, so I made this
     *
     * @return string
     * @url GET sanity
     */
    public function sanityCheck(){
        return "This is working";
    }
}

