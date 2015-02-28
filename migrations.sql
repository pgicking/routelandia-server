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


-- AGGREGATE: median
-- Taken from https://wiki.postgresql.org/wiki/Aggregate_Median
CREATE OR REPLACE FUNCTION routelandia._final_median(numeric[])
   RETURNS numeric AS
$$
   SELECT AVG(val)
   FROM (
     SELECT val
     FROM unnest($1) val
     ORDER BY 1
     LIMIT  2 - MOD(array_upper($1, 1), 2)
     OFFSET CEIL(array_upper($1, 1) / 2.0) - 1
   ) sub;
$$
LANGUAGE 'sql' IMMUTABLE;
CREATE AGGREGATE routelandia.median(numeric) (
  SFUNC=array_append,
  STYPE=numeric[],
  FINALFUNC=routelandia._final_median,
  INITCOND='{}'
);


-- FUNCTION: agg_15_minute_for
-- This function encapsulates the statistics query to get the 15-minute statistics for the given day-of-week for
-- the given start/end times.
--
-- This query will return the 15-minute average for a list of detectors.
-- The detector list should be pre-built to be inserted into this query. (Subquery to get detectors extremely detrimental to performance)
-- In order to build it we need the following:
--  * The list of detectors that we're interested in.
--  * The start and end times we're interested in during those days, both in 24 hour '17:00' format.
--    These times should be on 15 minute increments, as the query will be grouped into the 15 minute blocks. (:00, :15, :30, :45)
--
-- The returned "accuracy" column gives some prediction about the accuracy of the data source itself by using the 'countreadings' generated in the
-- loopdata_5min_raw table and assuming that every countreading SHOULD be 15 if the detector was firing 100%. (5 minutes, every 20 seconds)
--
-- Example for calling this function: SELECT * FROM routelandia.agg_15_minute_for('{100002, 100003, 100004, 100005}'::integer[], 5, '14:00', '18:00');
CREATE OR REPLACE FUNCTION routelandia.agg_15_minute_for(_detector_list integer[], _day_of_week numeric, _start_time time, _end_time time)
  RETURNS TABLE (hour double precision, minute numeric, speed numeric, traveltime numeric, accuracy numeric) AS
$func$
SELECT hour,
       minute,
       round2(median(avg_speed)) as "speed",
       round2(median(avg_traveltime)) as "traveltime",
       round2(sum(accuracy)::float/(count(accuracy)::float)) as "accuracy"
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
           round2((S.length/avg(D.speed))*60) as "avg_traveltime",
           round2(100*(sum(countreadings)::float/(15*count(countreadings)::float))) as "accuracy"
      FROM loopdata_5min_raw as D
      JOIN detectors dt ON D.detectorid = dt.detectorid
      JOIN stations S on dt.stationid = S.stationid
      WHERE D.speed IS NOT NULL
        AND D.speed != 0
        AND D.detectorid =ANY($1)
        AND D.starttime >= (now()::date - '6 weeks'::interval)
        AND extract('dow' from D.starttime) = $2
        AND D.starttime::time >= $3::time
        AND D.starttime::time <= $4::time
      GROUP BY S.stationid,
               extract('year' from D.starttime),
               extract('month' from D.starttime),
               extract('day' from D.starttime),
               extract('hour' from D.starttime),
               minute
      ORDER BY S.stationid, year, month, day, hour, minute
  ) AS fifteen_minute_agg
  GROUP BY hour, minute
  ORDER BY hour, minute;
$func$ LANGUAGE sql;


-------------------
-- DEVELOPER NOTE: You'll need this function in your local database, which is predefined in the production database
-- CREATE OR REPLACE FUNCTION public.round2(double precision)
--  RETURNS numeric
--  LANGUAGE sql
-- AS $function$select round(cast($1 as numeric), 2)$function$
