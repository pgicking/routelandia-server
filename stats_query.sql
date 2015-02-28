-- This query will return the 15-minute average for a list of detectors.
-- The detector list should be pre-built to be inserted into this query.
-- In order to build it we need the following:
--  * The list of detectors that we're interested in. !! NOTE: could make this a subquery rather than external?
--  * The start and end times we're interested in during those days, both in 24 hour '17:00' format.
--    These times should be on 15 minute increments, as the query will be grouped into the 15 minute blocks. (:00, :15, :30, :45)
SELECT hour,
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
      FROM loopdata_5min_raw as D
      JOIN detectors dt ON D.detectorid = dt.detectorid
      JOIN stations S on dt.stationid = S.stationid
      WHERE D.speed IS NOT NULL
        AND D.speed != 0
        AND D.detectorid IN (100002, 100003, 100004, 100005)
        AND D.starttime >= (now() - '6 weeks'::interval)
        AND extract('dow' from D.starttime) = 5
        AND D.starttime::time >= '14:00'
        AND D.starttime::time <= '18:00'
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
