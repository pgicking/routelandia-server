<?php


use Luracast\Restler\RestException;
use Respect\Data\Collections\Filtered;
use Routelandia\Entities;
use Routelandia\Entities\OrderedStation;

class TrafficStats{

    //To test this, use
    //curl -d 'derp' http://localhost:8080/api/trafficstats/test
    //curl -X POST http://localhost:8080/api/trafficstats -H "Content-Type: application/json" -d '{"startPoint":"[45.44620177127501,-122.78281856328249]" ,"endPoint": "[45.481798761799084,-122.79243160039188]" }'
    /**
     * Takes in a JSON object and returns traffic calculations
     *
     * Takes in a JSON object nd returns traffic calculations.
     * NOTE: $request_Data is not a true JSON object but an
     * internal restler associative array that handles JSON.
     * Might need to be changed in the future to dump into a
     * true JSON object.
     *
     * @param $request_data
     * @return array
     * @throws RestException
     * @url POST
     */
    public function doPOST ($request_data)
    {
        if (empty($request_data)) {
            throw new RestException(412, "JSON object is empty");
        }

        return array($request_data);
    }

    /**
     * Takes in a float coordinate and returns the station object closest to that point.
     *
     * Takes in a float coordinate and returns the station object closest to that point.
     *
     * @param float $point
     * @return null|OrderedStation
     */
    function getRelatedStation($point){
        $s = new Stations();
        $Station = new OrderedStation();

        $Station = $s->getStationfromCoord($point);

        return $Station;

    }

    /**
     * Converts JSON object coordinates into floats
     *
     * Converts JSON object coordinates into floats
     * NOTE: 90% sure this wont be needed but it should
     * be kept incase we change how the project is structured again.
     *
     * @param String $coord The String containing JSON coords
     * @return array float The two coords separated into an array
     */
    function parseCoord($coord){
        $coord = trim($coord,"[]");
        $pieces = explode(",",$coord);
        $p1 = (double)$pieces[0];
        $p2 = (double)$pieces[1];

        return array($p1,$p2);

    }

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
        $s = OrderedStation::fetch($id);
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

