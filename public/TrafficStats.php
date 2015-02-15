<?php


use Luracast\Restler\RestException;
use Respect\Data\Collections\Filtered;
use Routelandia\Entities;
use Routelandia\Entities\OrderedStation;

class TrafficStats{

    //To test this, use
    /*
curl -X POST http://localhost:8080/api/trafficstats -H "Content-Type: application/json" -d '
{
    "startpt": {
       "lat": -122.78281856328249,
       "lng": 45.44620177127501
       },
    "endpt": {
       "lat": -122.74895907829,
       "lng": 45.424207914266
    },
    "time": {
       "midpoint": "17:30",
       "weekday": "Thursday"
    }
}
'
     */
    /**
     * Takes in a JSON object and returns traffic calculations
     *
     * The JSON object sent to describe the request should be in the following format:
     *
     * <code><pre>
     * {<br />
     *  &nbsp;&nbsp; "startpt": {<br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;           "lat": -122.00, <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;           "lng": 45.00 <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;         }, <br />
     *  &nbsp;&nbsp; "endpt":   { <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;            "lat": -122.01, <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;            "lng": 45.00 <br />
     *  &nbsp;&nbsp;            }, <br />
     *  &nbsp;&nbsp; "time":    { <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;            "midpoint": "17:30", <br />
     *  &nbsp;&nbsp;   &nbsp;&nbsp;           "weekday": "Thursday" <br />
     *  &nbsp;&nbsp;            } <br />
     * }
     * </pre></code>
     *
     * The lat and lng sholud be sent as numbers. Midpoint could be sent either as either "17:30" or "5:30 PM".
     * The weekday parameter should be a text string with the name of the day of the week to run statistics on.
     *
     * @param array $request_data  JSON payload from client
     * @param $startpt
     * @param $endpt
     * @param $time
     * @return array Spits back what it was given
     * @throws RestException
     * @url POST
     */
    // If we want to pull aprt the json payload with restler
    // http://stackoverflow.com/questions/14707629/capturing-all-inputs-in-a-json-request-using-restler
    public function doPOST ($startpt, $endpt, $time,$request_data=null)
    {
        if (empty($request_data)) {
            throw new RestException(412, "JSON object is empty");
        }
         // To grab data from $request_data, syntax is
         // $request_data['startPoint'];

        $startPoint[0] = $startpt['lat'];
        $startPoint[1] = $startpt['lng'];
        $endPoint[0] = $endpt['lat'];
        $endPoint[1] = $endpt['lng'];
        try {
            $validStations = $this->getNearbyStations($startPoint,$endPoint);
        }catch (Exception $e){
            throw new RestException(400,"Given coordinates refer to stations on different highways");
        }
        print("\n");


        date_default_timezone_set('America/Los_Angeles');
        $STUPID_DEMO_RESULT = Array();
        $STUPID_DEMO_TIME = "15:45";
        $STUPID_DEMO_HIGHWAYS = OrderedStation::FetchForHighway(12);
        for($i=0; $i<12; $i++) {
          $STUPID_DEMO_OBJ = new stdClass;
          $STUPID_DEMO_TIME = strtotime("+15 minutes", strtotime($STUPID_DEMO_TIME));
          $STUPID_DEMO_OBJ->time_of_day = date('h:i', $STUPID_DEMO_TIME);
          $STUPID_DEMO_OBJ->duration = rand(2,30);
          $STUPID_DEMO_OBJ->stations_used = $STUPID_DEMO_HIGHWAYS;
          array_push($STUPID_DEMO_RESULT, $STUPID_DEMO_OBJ);
        }
        return $STUPID_DEMO_RESULT;
    }

    /**
     * Takes in a float coordinate and returns the station object closest to that point.
     *
     * Takes in a float coordinate and returns the station object closest to that point.
     *
     * @param $startPoint
     * @param $endPoint
     * @return array OrderedStation
     * @throws Exception
     * @internal param array $point 2 element array with two floats
     */
    function getNearbyStations($startPoint,$endPoint){

        $startStations = OrderedStation::getStationsFromCoord($startPoint);
        $endStations = OrderedStation::getStationsFromCoord($endPoint);
        //this type validation should probably be in a different function
        try {
            $finalStations = Stations::ReduceStationPairings($startStations, $endStations);
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
        return $finalStations;

    }

    /**
     * Converts string coordinates into floats
     *
     * Converts string coordinates into floats
     * This takes string from $request_data['point'] and converts it
     * into real floats.
     * NOTE: 90% sure this wont be needed but it should
     * be kept incase we change how the project is structured again.
     *
     * @param String $coord The String containing coords
     * @return array float The two coords separated into an array
     */
    function parseCoordFromString($coord){
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
     * @deprecated deprecated since team meeting 1/22/15 restructured the project
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

    /**Checks if the station id is valid
     *
     * Checks if the station id is valid
     *
     * @param int $id ID of the station
     * @return bool True or false if it exists or not
     */
    function isValid($id){
        $s = OrderedStation::fetch($id);
        if(!$s)
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

