<?php


use Luracast\Restler\RestException;
use Respect\Data\Collections\Filtered;
use Routelandia\Entities;
use Routelandia\Entities\OrderedStation;

class TrafficStats{

    //To test this, use
    //curl -d 'derp' http://localhost:8080/api/trafficstats/test
    //curl -X POST http://localhost:8080/api/trafficstats -H "Content-Type: application/json" -d '{"how_derpy" : "soooo derpy"}'
    //curl -X POST http://localhost:8080/api/trafficstats -H "Content-Type: application/json" -d ‘{{“startPoint":"[45.44620177127501,-122.78281856328249]" } , {"endPoint": "[45.481798761799084,-122.79243160039188]" }}'
    /**
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

        //Avoid any extra json_decodes
        $startPoint = $request_data['startPoint'];
        $endPoint = $request_data['endPoint'];

        print($request_data['startPoint']);
        print($request_data['endPoint']);

        return array($request_data);
    }

    function parseJSON($json){

    }

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

