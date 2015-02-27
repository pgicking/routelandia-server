<?php

use Luracast\Restler\RestException;
use Respect\Data\Collections\Filtered;
use Routelandia\Entities;
use Routelandia\Entities\OrderedStation;
use Routelandia\Entities\Detector;
use Respect\Relational\Mapper;
use Routelandia\DB;
use Routelandia\Entities\Station;

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
     * The lat and lng should be sent as numbers. Midpoint could be sent either as either "17:30" or "5:30 PM".
     * The weekday parameter should be a text string with the name of the day of the week to run statistics on.
     *
     * @param array $startpt Contains the keys "lat" and "lng" representing the starting point.
     * @param array $endpt Contains the keys "lat" and "lng" representing the ending point
     * @param array $time Contains keys for "midpoint" and "weekday"
     * @param array $request_data JSON payload from client
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
        $timeF = new DateTime($time['midpoint']);
        $timeStart = $timeF->modify("-2 hours")->format("H:i");
        $timeEnd = $timeF->modify("+4 hours")->format("H:i");

        // Get the most recent DATE which has the day-of-week requested.
        // Our time block will be the 6 weeks leading up to that date.
        $dateBlockEnd = new DateTime("last ".$time['weekday']);
        $dateBlockStart = clone $dateBlockEnd;
        $dateBlockStart->modify("-6 weeks");

        // Create an array of the date strings for the last 6 weeks worth of days we want.
        // We'll use this to pull from the specific tables we want.
        // fd_dates is for the freeway.data_yyyymmdd table name format
        // ut_dates is for the unique table loopdata_yyyy_mm_dd table name format
        $fd_dates = array();
        $ut_dates = array();
        $tDate = clone $dateBlockEnd;
        for($i = 0; $i<6; $i++) {
          $fd_dates[] = "freeway.data_{$tDate->format("Ymd")}";
          $ut_dates[] = "loopdata_{$tDate->format("Y_m_d")}";
          $tDate->modify("-1 week");
        }

        // Next we'll do is build a list of detectors that were live for all stations in the
        // linked-list we've found (including and between the start and end stations)
        // NOTE that we're putting utter faith in the function that determined that the end station
        // is a valid downstream for the start station. This isn't super great, but it works for now.
        $detectors = array();
        $curStationId = $validStations[0];
        while($curStationId != $validStations[1]) {
          $tds = Detector::fetchActiveForStationInDateRange($curStationId, $dateBlockStart, $dateBlockEnd);
          $detectors = array_merge($detectors, $tds);
          $curStationId = OrderedStation::getDownstreamIdFor($curStationId);
        }
        $detectorstring = "(";
        $i = count($detectors);
        foreach($detectors as $d) {
          $detectorstring .= "{$d->detectorid}";

          $next = !!(--$i);
          if($next) {
            $detectorstring .= ",";
          }
        }
        $detectorstring .= ")";

        // Now that we have the detectors that were valid during that time period
        // we can use that list of detectors, and the list of dates, to actually query
        // the loopdata to get statistics!
        // We have a big query we're going to run, but inside of it we'll need a bunch of subqueries,
        // one for each table we're unioning. We'll build those first:
        // Example of what we're building:
        // SELECT detectorid,
        //          starttime,
        //          speed,
        //          volume
        //     FROM loopdata_2015_02_19
        //     WHERE starttime::time >= '14:00'
        //       AND starttime::time <= '19:00'
        //       AND detectorid IN (100002, 100003, 100004, 100005)
        // TODO: This is duplicate code that should be extracted to a function, and we should feel bad.
        //       We're not doing that today because we're lazy declarative bastards in a hurry and trying to understand...
        $unionstring = "";
        $i = count($fd_dates);
        foreach($fd_dates as $fd) {
          $unionstring .= "\nSELECT detectorid,starttime,speed,volume FROM {$fd}";
          $unionstring .= " WHERE starttime::time >= '{$timeStart}' AND starttime::time <= '{$timeEnd}' AND detectorid IN {$detectorstring}";

          $next = !!(--$i);
          if ($next) {
            $unionstring .= "\nUNION";
          }
        }
        // Add the union between the two sets of tables, only as long as there ARE tables in both sets.
        if(count($fd_dates)>0 && count($ut_dates)>0) {
          $unionstring .= "\nUNION";
        }
        $i = count($ut_dates);
        foreach($ut_dates as $ut) {
          $unionstring .= "\nSELECT detectorid,starttime,speed,volume FROM {$ut}";
          $unionstring .= " WHERE starttime::time >= '{$timeStart}' AND starttime::time <= '{$timeEnd}' AND detectorid IN {$detectorstring}";

          $next = !!(--$i);
          if ($next) {
            $unionstring .= "\nUNION";
          }
        }


        // Now we have everything we need to build the full giant query! LET'S GO!
        $querystring = <<< END_OF_QUERY
       hour,
       minute,
       round2(median(avg_speed)) as "speed",
       round2(median(avg_traveltime)) as "traveltime"
  FROM
  (
    SELECT S.stationid,
           S.length,
           extract('year' from starttime) as "year",
           extract('month' from starttime) as "month",
           extract('day' from starttime) as "day",
           extract('hour' from starttime) as "hour",
           15*div(extract('minute' from starttime)::int, 15) as "minute",
           round2(avg(D.speed)) as "avg_speed",
           round2((S.length/avg(D.speed))*60) as "avg_traveltime"
          FROM
            ($unionstring
            ) as D
            JOIN detectors dt ON d.detectorid = dt.detectorid
            JOIN stations S on dt.stationid = S.stationid
          WHERE starttime::time >= '$timeStart'
            AND starttime::time <= '$timeEnd'
          GROUP BY S.stationid,
                   extract('year' from starttime),
                   extract('month' from starttime),
                   extract('day' from starttime),
                   extract('hour' from starttime),
                   minute
          ORDER BY S.stationid, year, month, day, hour, minute
  ) AS agg_into_quarter_hour_buckets_for_each_day
  GROUP BY hour, minute
  ORDER BY hour, minute;
END_OF_QUERY;


        // So now that we have that ugly thing we can FINALLY feed it into the database and see what happens...
        $sqlRes = DB::sql()->select($querystring)->fetchAll(array());

        return $sqlRes;
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

