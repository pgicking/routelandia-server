-- This is the example query offered by @kat --
-----------------------------------------------

--- Query to get average speed for October 2014 for roadway segments
--- in 1-minute bins 
--- all days, all times

--- Use corridorid 10003 - corridor created for this usage 
--- You can create your own corridors, I can update db or
--- you can make tables that mirror corridorstationlengths and
--- corridors

create temp table tt_dec_2014_jan_2015_1min (
        year integer, month integer, day integer, hour integer, minute integer, 
        starttime timestamp with time zone,
        avgspeed double precision, 
        traveltime double precision, 
        countreadings integer);

--- query for all 1 min speeds and tts aggregated over all stations 
insert into tt_dec_2014_jan_2015_1min(

SELECT year, month, day, hour, minute, st as starttime, 
       round2(avg(speed)) as avgspeed, 
       sum(traveltime) as traveltime, 
       sum(cr) as countreadings
FROM ( 
      --- convert speed to traveltime
      SELECT D.stationid,
             extract('year' from starttime) as year, 
             extract('month' from starttime) as month,
             extract('day' from starttime) as day,
             extract('hour' from starttime) as hour,
             extract('minute' from starttime) as minute,
             date_trunc('minute', starttime) as st,
             round2((C.corridorlength/(avg(speed)))*60) as traveltime,
             avg(speed) as speed,
             count(*) as cr 
      FROM ( 
         
         --- I was using just freeway.data, but this union
         --- will grab both types of data
         --- I'm starting with 20-sec data, but you can use 5min data

         --- combine DAC and ATMS data 
         --- SELECT detectorid, starttime, speed, volume FROM loopdata_2014_12 
         --- UNION ALL
         SELECT detectorid, starttime, speed, volume 
         FROM freeway.data 
         WHERE starttime BETWEEN 
         '2014-12-01 00:00'::timestamptz AND '2015-01-08 00:00'::timestamptz 
          ) as L, --- subquery
          detectors D, stations S, corridorstationlengths C
      WHERE 
      
      L.detectorid = D.detectorid 
      AND D.stationid = S.stationid
      AND S.stationid = C.stationid

      AND C.corridorid = 10003
      
      AND speed > 0
      
      GROUP BY D.stationid, year, month, day, hour, minute, st, C.corridorlength --- grouping by segment
     ) as Q
GROUP BY year, month, hour, day, minute, st  
ORDER BY year, month, hour, minute 
);


