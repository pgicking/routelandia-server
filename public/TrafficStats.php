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
       "lng": -122.78281856328249,
       "lat": 45.44620177127501
       },
    "endpt": {
       "lng": -122.74895907829,
       "lat": 45.424207914266
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
     *  &nbsp;&nbsp;  &nbsp;&nbsp;           "lng": -122.00, <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;           "lat": 45.00 <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;         }, <br />
     *  &nbsp;&nbsp; "endpt":   { <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;            "lng": -122.01, <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;            "lat": 45.00 <br />
     *  &nbsp;&nbsp;            }, <br />
     *  &nbsp;&nbsp; "time":    { <br />
     *  &nbsp;&nbsp;  &nbsp;&nbsp;            "midpoint": "17:30", <br />
     *  &nbsp;&nbsp;   &nbsp;&nbsp;           "weekday": "Thursday" <br />
     *  &nbsp;&nbsp;            } <br />
     * }
     * </pre></code>
     *
     * The lat and lng should be sent as numbers. Midpoint could be sent either as either "17:30" or "5:30 PM".
     * The weekday parameter should be a text string with the name of the day of the week to run statistics on.
     *
     * @param array $request_data  JSON payload from client
     * @param object $startpt Contains the keys "lat" and "lng" representing the starting point.
     * @param object $endpt Contains the keys "lat" and "lng" representing the ending point
     * @param object $time Contains keys for "midpoint" and "weekday"
     * @return array A list of tuples representing time/duration results
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
            throw new RestException(400,$e->getMessage());
        }

        // This is what we'll be returning... Eventually.
        $retVal = array();

        // Now we have 2 valid stations, known to be on the same highway linked-list.
        // First thing we'll do is build the start and end times that we're interested in...
        // Get the most recent DATE which has the day-of-week requested.
        // Our time block will be the 6 weeks leading up to that date.
        $timeBlockStart = new DateTime("last "+$time['weekday']);
        $timeBlockEnd = new DateTime($timeBlockStart);
        $timeBlockEnd->modify("- 6 weeks");

        // Next we'll do is build a list of detectors that were live for all stations in the
        // linked-list we've found (including and between the start and end stations)
        $detectors = array();
        $curStationId = $validStations[0]->stationid;
        while($curStationId != validStations[1]['stationid']) {
          $detectors = $detectors + Detector::fetchActiveForStationInDateRange($curStationId, $timeBlockStart, $timeBlockEnd);
        }

        // Now that we have the detectors that were valid during that time period
        // we can use that list of detectors, and the list of dates, to actually query
        // the loopdata to get statistics!

        return retVal;
    }

    /**
     * Takes in a float coordinate and returns the station object closest to that point.
     *
     * Takes in a float coordinate and returns the station object closest to that point.
     *
     * @param $startPoint
     * @param $endPoint
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
            $finalStations = Stations::ReduceStationPairings($startStations, $endStations);
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

