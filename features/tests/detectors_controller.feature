Feature: Detectors Controller

  Scenario: request index
	When I request "detectors"
	Then the response status code should be 200
    And the response is JSON
    And the type is "array"
    And all of the detectors in the array are detectors

  Scenario: request a valid detector
	When I request "detectors/100059"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"
    And the detector is a detector
    And the "detectorid" property in the array equals 100059
    And the "stationid" property in the array equals 1064
    And the "locationtext" property in the array equals 'TV Hwy NB'
    And the "lanenumber" property in the array equals 1
    And the "end_date" property in the array equals null
    And the "start_date" property in the array equals '2014-05-01 00:00:00-07'
    And the "highwayid" property in the array equals 9

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
    And the type is "array"

  
  Scenario: request a related station from a detector id
  	When I request "detectors/100059/relatedstation"
  	Then the response status code should be 200
  	And the response is JSON
  	And the type is "array"
  	And the station is a station
  	And the "stationid" property in the array equals 1064 
    And the "upstream" property in the array equals 3155
    And the "downstream" property in the array equals 1063
    And the "highwayid" property in the array equals 9
    And the "opposite_stationid" property in the array equals null
    And the "milepost" property in the array equals 1.34
    And the "length" property in the array equals 0.42
    And the "locationtext" property in the array equals 'TV Hwy NB'
    And the "linked_list_position" property in the array equals 9
    And the "geojson_raw" and "type" property in the array equals 'LineString'
