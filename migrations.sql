-- Create a schema to organize all the routelandia specific stuff.
CREATE SCHEMA routelandia;


-- VIEW: orderedStations
-- This view creates a list of stations which are ordered by their
-- upstream/downstream linked list properties, and limits the stations
-- selected to the 1000-3000 range stations that are the "correct" type.
--
-- This view includes a "path" column, which is an ordered array of the
-- nodes required to get to this station beginning at the head of the
-- linked list that the node is part of.
-- The "stationorder" column is a calculated column that uses the length
-- off the path to determine the stations position (order) in it's linked
-- list. (Note that each new path restarts with order 0.)
CREATE OR REPLACE VIEW routelandia.orderedStations AS WITH RECURSIVE stations_by_highway AS
(
  (
    SELECT   stationid
           , upstream
           , downstream
           , highwayid
           , milepost
           , length
           , locationtext
           , opposite_stationid
           , (ST_AsGeoJson(ST_Transform(segment_raw, 4326)))::json as segment_raw
           , (ST_AsGeoJson(ST_Transform(segment_50k, 4326)))::json as segment_50k
           , (ST_AsGeoJson(ST_Transform(segment_100k, 4326)))::json as segment_100k
           , (ST_AsGeoJson(ST_Transform(segment_250k, 4326)))::json as segment_250k
           , (ST_AsGeoJson(ST_Transform(segment_500k, 4326)))::json as segment_500k
           , (ST_AsGeoJson(ST_Transform(segment_1000k, 4326)))::json as segment_1000k
           , array[stationid] as linked_list_path
           , 0 as linked_list_position  -- Start the positions at zero
    FROM stations
    WHERE upstream = 0
          AND stationid >= 1000
          AND stationid < 4000
  )
  UNION ALL  -- Needs to be union ALL because you can't compare the JSON datatypes, but this shouldn't be a problem.
  (
    SELECT   s.stationid
           , s.upstream
           , s.downstream
           , s.highwayid
           , s.milepost
           , s.length
           , s.locationtext
           , s.opposite_stationid
           , (ST_AsGeoJson(ST_Transform(s.segment_raw, 4326)))::json as segment_raw
           , (ST_AsGeoJson(ST_Transform(s.segment_50k, 4326)))::json as segment_50k
           , (ST_AsGeoJson(ST_Transform(s.segment_100k, 4326)))::json as segment_100k
           , (ST_AsGeoJson(ST_Transform(s.segment_250k, 4326)))::json as segment_250k
           , (ST_AsGeoJson(ST_Transform(s.segment_500k, 4326)))::json as segment_500k
           , (ST_AsGeoJson(ST_Transform(s.segment_1000k, 4326)))::json as segment_1000k
           , (hs.linked_list_path || s.stationid)
           , array_length(linked_list_path, 1)
    FROM   stations_by_highway hs -- The above created initial table, i.e. our starting point
         , stations s
    WHERE s.stationid = hs.downstream
  )
)
SELECT * from stations_by_highway WHERE stationid IN (SELECT stationid FROM detectors WHERE enabledflag=1) ORDER BY linked_list_position;


-- VIEW: highwaysWithStations
-- This view allows us to scope only stations which actually have stations attached to them.
-- This is useful because a large number of the highways have no stations somehow, and are thus
-- useless for our purposes.
-- This also filters out any stations that don't have a segment_raw. We won't be able to draw
-- them, so the client app doesn't want them.
CREATE OR REPLACE VIEW routelandia.highwaysHavingStations AS
  SELECT highways.*
  FROM highways
    JOIN stations ON stations.highwayid=highways.highwayid
      AND stations.segment_raw != ''
  GROUP BY highways.highwayid
  HAVING count(distinct stations.stationid)>0;


-- FUNCTION: agg_15_minute_for
-- This function encapsulates the statistics query to get the 15-minute statistics for the given day-of-week for
-- the given start/end times.
--
-- This query will return the 15-minute average for a list of stations.
-- In order to build it we need the following:
--  * The list of stations that we're interested in.
--  * The start and end times we're interested in during those days, both in 24 hour '17:00' format.
--    These times should be on 15 minute increments, as the query will be grouped into the 15 minute blocks. (:00, :15, :30, :45)
--
-- The returned "accuracy" column gives some prediction about the accuracy of the data source itself by using the 'countreadings' generated in the
-- loopdata_5min_raw table and assuming that every countreading SHOULD be 15 if the detector was firing 100%. (5 minutes, every 20 seconds)
--
-- Example for calling this function: SELECT * FROM routelandia.agg_15_minute_for('{100002, 100003, 100004, 100005}'::integer[], 5, '14:00'::time, '18:00'::time);
CREATE OR REPLACE FUNCTION routelandia.agg_15_minute_for(_station_list integer[], _day_of_week numeric, _start_time time, _end_time time)
  RETURNS TABLE (hour double precision, minute numeric, distance double precision, speed numeric, traveltime numeric, accuracy numeric) AS
$func$
DECLARE
  -- These will hold the results of some pre-queries that we'll use. (Better than nested queries.)
  expected_readings_per_15_minute integer;
  segment_length float;

BEGIN

-- How many expected readings should we have for the entire route?
-- Every detector, in every station, should fire every 20 seconds, which is 3 times a minute,
-- and 45 times for every 15 minute chunk of time.
SELECT INTO expected_readings_per_15_minute (SELECT sum(numberlanes) FROM stations WHERE stationid = ANY($1))*45;

-- How long is the entire route?
-- Necessary for later calculations because the sum(length) in the main query won't be accurate
-- if there are stations with no loopdata at all in the selected time range.
SELECT INTO segment_length sum(length) FROM stations WHERE stationid = ANY($1);

-- MAIN QUERY
--
-- The outer query aggregates the pile of 15 minute intervals for each day into JUST the 15 minute intervals.
-- i.e. "2015-03-01 15:00" and "2015-03-08 15:00" (And the 4 other weeks) get collapsed into just "15:00".
-- This is also the point at which we calculate the traveltime and accuracy of the entire rout.
RETURN QUERY SELECT
       hour_i AS "hour",                                                                                        -- We're grouping the inner query by hour.
       minute_i AS "minute",                                                                                    -- ... and minute ...
       segment_length as "distance",                                                                            -- We collect this in each result, even though it's a constant, so that we can get this value in the app without needing a second query.
       round2(avg(avg_speed)) as "speed",                                                                       -- The average speed across all stations during this 15 minute interval
       round2( (segment_length / avg(avg_speed))*60) AS "traveltime",                                           -- The average speed over the entire length is a reasonable approximation of the travel time.
       round2(100*(sum(total_readings)/(expected_readings_per_15_minute*count(total_readings)))) as "accuracy"  -- We know we should get a certain number of readings per detector for a 15 minute period, so multiply that by the number of 15 minute increments, and that's how many we should get total. Compare against how many we actually got.
  FROM
  (
    -- This inner query is designed to strip out the stations and aggregate down to the data for the entire route
    -- over this 15 minute interval for each day.
    SELECT year_i,month_i,day_i,hour_i,minute_i,
           avg(avg_speed) as "avg_speed",           -- The average speed of the averages of all the stations
           sum(total_readings) as "total_readings"  -- The total readings from all detectors in all stations for this route
      FROM
      (
        -- Collect readings into 15 minute blocks for each station.
        -- Results will be for every 15 minute interval between the given start and end times, on the given
        -- day of the week, for the last 6 weeks.
        -- The reason we give some of the inner variables an _i is to keep postgres happy, it seemed to think there were conflicts. :-)
        SELECT s.stationid,
               extract('year' from starttime) as "year_i",
               extract('month' from starttime) as "month_i",
               extract('day' from starttime) as "day_i",
               extract('hour' from starttime) as "hour_i",
               15*div(extract('minute' from starttime)::int, 15) as "minute_i",
               round2(avg(l.speed)) as "avg_speed",
               sum(l.countreadings) as "total_readings"
          FROM stations s
          JOIN detectors d ON s.stationid = d.stationid
                            AND (d.end_date >= (now()::date-'6 weeks'::interval) OR end_date IS NULL) -- Make sure to only include detectors which were "live" sometime in our desired interval
          -- We use an inner query here because if we just join on it and then filter rows, we filter out the rows where 
          -- there's a valid station, but it doesn't have any loopdata records in the requested interval.
          -- This keeps every requested station in the next outer query.
          -- Although, to be honest, the query was restructured somewhat since we did this, so it may be that we can 
          -- change this back to a regular JOIN now, with the WHERE on the outside.
          JOIN (SELECT * FROM loopdata_5min_raw
                            WHERE starttime >= (now()::date - '6 weeks'::interval)
                            AND extract('dow' from starttime) = $2
                            AND starttime::time >= $3
                            AND starttime::time < $4
                          ) as l
                          ON d.detectorid = l.detectorid
          WHERE s.stationid = ANY($1)
          GROUP BY s.stationid, year_i, month_i, day_i, hour_i, minute_i
      ) AS fifteen_minute_agg
      GROUP BY year_i,month_i,day_i,hour_i,minute_i
  ) AS by_full_day_and_time_agg
  GROUP BY hour_i, minute_i
  ORDER BY hour, minute;
END;
$func$ LANGUAGE plpgsql;


-------------------
-- DEVELOPER NOTE: You'll need this function in your local database, which is predefined in the production database
-- CREATE OR REPLACE FUNCTION public.round2(double precision)
--  RETURNS numeric
--  LANGUAGE sql
-- AS $function$select round(cast($1 as numeric), 2)$function$
