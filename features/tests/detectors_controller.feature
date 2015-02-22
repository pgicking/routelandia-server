Feature: Detectors Controller

  Scenario: request index
	When I request "detectors"
	Then the response status code should be 200
    And the response is JSON
    And the type is "array"
    And the size of the array is 58
    And all of the detectors in the array are detectors

  Scenario: request a valid detector
	When I request "detectors/100059"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"
    And the detector is a detector
    And the "detectorid" property equals 100059
    And the "stationid" property equals 1064
    And the "locationtext" property equals "TV Hwy NB"
    And the "lanenumber" property equals 1
    And the "end_date" property equals null
    And the "start_date" property equals "2014-05-01 00:00:00-07"
    And the "highwayid" property equals 9

  Scenario: request an invalid detector
    When I request "detectors/666"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    And the "error" and "message" property equals 'Not Found: Requested Detector ID not found'
    And the "debug" and "source" property equals 'Detector.php:66 at call stage'
    # And the error object properties are..

  Scenario: request the active status of a detector that is still active
    When I request "detectors/100059/stillactive"
    Then the response status code should be 200
    And the response is JSON
    And the type is "bool"
  
  Scenario: request a related station from a detector id
  	When I request "detectors/100059/relatedstation"
  	Then the response status code should be 200
  	And the response is JSON
  	And the station is a station
  	And the "stationid" property equals 1064
    And the "upstream" property equals 3155
    And the "downstream" property equals 1063
    And the "highwayid" property equals 9
    And the "opposite_stationid" property equals null
    And the "milepost" property equals 1.34
    And the "length" property equals 0.42
    And the "locationtext" property equals 'TV Hwy NB'
    And the "linked_list_position" property equals 9
    And the "geojson_raw" and "type" property equals 'LineString'
