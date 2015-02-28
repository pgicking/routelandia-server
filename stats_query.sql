-- This query will return the 15-minute average for a list of detectors.
-- The detector list should be pre-built to be inserted into this query.
-- In order to build it we need the following:
--  * The list of detectors that we're interested in. !! NOTE: could make this a subquery rather than external?
--  * The table names for the days we want. (i.e. loopdata_yyyy_mm_dd and freeway.data_yyyymmdd for every day we're interested in)
--    We rely on these to be formatted strings with the correct table names. Generation of this handled in application logic.
--  * The start and end times we're interested in during those days, both in 24 hour '17:00' format.
--    These times should be on 15 minute increments, as the query will be grouped into the 15 minute blocks. (:00, :15, :30, :45)
SELECT hour,
       minute,
       round2(median(avg_speed)) as "speed",
       round(median(avg_traveltime)) as "traveltime"
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
            (
              -- For getting raw data we'll need to union two tables for EVERY DAY we want, for every detector we want
              -- However, for this example we're just getting the two, with just 4 detectors.
              SELECT detectorid,
                     starttime,
                     speed,
                     volume
                FROM loopdata_2015_02_19
                WHERE starttime::time >= '14:00'
                  AND starttime::time <= '19:00'
                  AND detectorid IN (100002, 100003, 100004, 100005)
              UNION ALL
              SELECT detectorid,
                     starttime,
                     speed,
                     volume
                FROM freeway.data_20150219
                WHERE starttime::time >= '14:00'
                AND starttime::time <= '19:00'
                AND detectorid IN (100002, 100003, 100004, 100005)
              UNION ALL
              SELECT detectorid,
                     starttime,
                     speed,
                     volume
                FROM loopdata_2015_02_12
                WHERE starttime::time >= '14:00'
                  AND starttime::time <= '19:00'
                  AND detectorid IN (100002, 100003, 100004, 100005)
              UNION ALL
              SELECT detectorid,
                     starttime,
                     speed,
                     volume
                FROM freeway.data_20150212
                WHERE starttime::time >= '14:00'
                AND starttime::time <= '19:00'
                AND detectorid IN (100002, 100003, 100004, 100005)
              ORDER BY starttime,detectorid
            ) as D
            JOIN detectors dt ON d.detectorid = dt.detectorid
            JOIN stations S on dt.stationid = S.stationid
          WHERE starttime::time >= '14:00'
            AND starttime::time <= '19:00'
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
