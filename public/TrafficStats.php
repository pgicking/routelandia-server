<?php


use Respect\Data\Collections\Filtered;
use Routelandia\Entities;

class TrafficStats{

    //TODO: Create function to accept a JSON payload/list of tuples for segments
    /**
     * Takes two station id, returns traffic information
     *
     * Takes two station ids and calculates all the stations inbetween them
     * to get traffic information for the segment of highway
     *
     * @param int $start
     * @param int $end
     * @return mixed
     * @throws exception
     * @url GET {start}/{end}
     */
    public function trafficInfo($start,$end){

        $this->isValid(1091);
        if($this->isValid($start) == false){
            throw new exception('Invalid {$start} station id');
        }
        elseif ($this->isValid($end) == false){
            throw new exception('Invalid {$end} station id');
        }
        else
            return "Traffic info will go here";
    }

    function isValid($id){
        $s = DB::instance()->orderedStations(array('stationid='=>$id))->fetch();
        if(is_bool($s))
            return false;
        else
            return true;
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

