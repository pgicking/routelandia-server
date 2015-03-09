<?php

use Luracast\Restler\RestException;
use Respect\Data\Collections\Filtered;
use Routelandia\Entities;
use Routelandia\Entities\OrderedStation;
use Routelandia\Entities\Detector;
use Respect\Relational\Mapper;
use Routelandia\DB;
use Routelandia\Entities\Station;
use Routelandia\Entities\ApiResult;

class TrafficStats{

    /**
     * Takes in a JSON object and returns traffic calculations
     *
     * The JSON object sent to describe the request should be in the following format:
     *
     * <code><pre>
     * {<br />
     *  &nbsp;&nbsp; "startpt": {<br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;           "lng": -122.78281856328249, <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;           "lat": 45.44620177127501 <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;         }, <br />
     *  &nbsp;&nbsp; "endpt":   { <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;            "lng": -122.74895907829, <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;            "lat": 45.424207914266 <br />
     *  &nbsp;&nbsp;            }, <br />
     *  &nbsp;&nbsp; "time":    { <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;            "midpoint": "17:30", <br />
     *  &nbsp;&nbsp;   &nbsp;&nbsp;           "weekday": "Thursday" <br />
     *  &nbsp;&nbsp;            } <br />
     * }
     * </pre></code>
     *
     * The lat and lng should be sent as numbers. Midpoint should be sent in the form "17:30".
     * The weekday parameter should be a text string with the name of the day of the week to run statistics on.<br />
     * <br />
     * Possible response codes:<br />
     * 200 - No errors<br />
     *<br />
     * 400 - Couldn't get stations from users given taps, user somehow tapped more than 200 meters away from a station<br />
     * 400 - Given stations refer to different highways, please choose only one highway<br />
     *<br />
     * 404 - Couldn't find the requested object, usually because the given ID doesnt exist in the database<br />
     *<br />
     * 412 - The sent JSON object is empty<br />
     *
     * @param array $startpt Contains the keys "lat" and "lng" representing the starting point.
     * @param array $endpt Contains the keys "lat" and "lng" representing the ending point
     * @param array $time Contains keys for "midpoint" and "weekday"
     * @param array $request_data JSON payload from client
     * @return array A list of tuples representing time/duration results
     * @throws RestException
     * @url POST
     */
    public function doPOST ($startpt, $endpt, $time,$request_data=null)
    {
        if (empty($request_data)) {
            throw new RestException(412, "JSON object is empty");
        }
         // To grab data from $request_data, syntax is
         // $request_data['startPoint'];
        $validStations = null;
        $startPoint[0] = $startpt['lat'];
        $startPoint[1] = $startpt['lng'];
        $endPoint[0] = $endpt['lat'];
        $endPoint[1] = $endpt['lng'];
        try {
            $validStations = $this->getNearbyStations($startPoint,$endPoint);
        }catch (Exception $e){
            throw new RestException(400,$e->getMessage());
        }

        // First thing we'll do is build the start and end times that we're interested in...
        // We're given the midpoint and go 2 hours to either side of it.
        // We also need to know the integer (0=sunday) day of week it is...
        $timeF = new DateTime($time['midpoint']);

        //round to nearest 15 minutes
        $minute = $timeF->format("i");
        $hour = $timeF->format("H");
        $minute = round(($minute / 15)) * 15;
        $timeF->setTime($hour,$minute);

        $timeStart = $timeF->modify("-2 hours")->format("H:i");
        $timeEnd = $timeF->modify("+4 hours")->format("H:i");
        $timeF->modify("this {$time['weekday']}");
        $dow = intval($timeF->format("w"));


        // Next we'll do is build a list of detectors that were live for all stations in the
        // linked-list we've found (including and between the start and end stations)
        // NOTE that we're putting utter faith in the function that determined that the end station
        // is a valid downstream for the start station. This isn't super great, but it works for now.
        $stationids = array();
        $curStationId = $validStations[0];
        while($curStationId != null) {
          $stationids[] = $curStationId;
          if($curStationId == $validStations[1]) {
            $curStationId = null; // Kick out of the loop
          } else {
            $curStationId = OrderedStation::getDownstreamIdFor($curStationId);
          }
        }
        $stationstring = "{";
        $i = count($stationids);
        foreach($stationids as $s) {
          $stationstring .= "{$s}";

          $next = !!(--$i);
          if($next) {
            $stationstring .= ",";
          }
        }
        $stationstring .= "}";

        // Now that we have the detectors that were valid during that time period
        $qRes = DB::sql()->select("*")->from("agg_15_minute_for('{$stationstring}'::integer[], {$dow}, '{$timeStart}', '{$timeEnd}')")->fetchAll(array());

        $infoObj = new stdClass;
        $infoObj->stations = $stationids;

        $debugQuery = new stdClass;
        $debugQuery->dow = $dow;
        $debugQuery->timeStart = $timeStart;
        $debugQuery->timeEnd = $timeEnd;

        $retVal= new ApiResult($qRes, $debugQuery);
        $retVal->info = $infoObj;
        return $retVal;
    }



    /**
     * Takes in a float coordinate and returns the station object closest to that point.
     *
     * Takes in a float coordinate and returns the station object closest to that point.
     *
     * @param array $startPoint Array of the lat/long start point
     * @param array $endPoint Array of the lat/long end point
     * @return array Station IDs for the first and last stations to use
     * @throws Exception
     * @internal param array $point 2 element array with two floats
     */
    function getNearbyStations($startPoint,$endPoint){

        try {
            $startStations = OrderedStation::getStationsFromCoord($startPoint);
            $endStations = OrderedStation::getStationsFromCoord($endPoint);
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
        //this type validation should probably be in a different function
        try {
            $finalStations = Station::ReduceStationPairings($startStations, $endStations);
        }catch (Exception $e){
            throw new Exception("Given coordinates refer to stations on different highways. ".$e->getMessage());
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

}

