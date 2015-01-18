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
DROP VIEW orderedStations;
CREATE VIEW orderedStations AS WITH RECURSIVE stations_by_highway AS
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
           , array[stationid] as path
           , 0 as stationorder  -- Start the "order" at zero
    FROM stations
    WHERE     upstream = 0
          AND stationid >= 1000
          AND stationid < 3000
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
           , (hs.path || s.stationid)
           , array_length(path, 1)
    FROM   stations_by_highway hs -- The above created initial table, i.e. our starting point
         , stations s
    WHERE s.stationid = hs.downstream
  )
)
SELECT * from stations_by_highway ORDER BY stationorder;
