Feature: Stations Controller

  Scenario: request index
    When I request "stations"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"
    And all of the stations in the array are stations

  Scenario: request a valid stationid
    When I request "stations/1118"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"
    And the station is a station
    And the "stationid" property equals 1118
    And the "upstream" property equals 0
    And the "downstream" property equals 3154
    And the "highwayid" property equals 10
    And the "opposite_stationid" property equals null
    And the "milepost" property equals 0.08
    And the "length" property equals 0.17
    And the "locationtext" property equals 'Barnes SB'
    And the "linked_list_position" property equals 0
    And the "geojson_raw" and "type" property equals 'LineString'

  Scenario: request an invalid stationid
    When I request "stations/666"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    And the "error" and "message" property equals 'Not Found: Could not find the stationID requested'
    And the "debug" and "source" property equals 'OrderedStation.php:122 at call stage'
    # And the error object properties are..

  Scenario: request detectors for a valid stationid
    When I request "stations/1064/detectors"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"
    And the size of the array is 4
    And all of the detectors in the array are detectors

  Scenario: request detectors for an invalid stationid
    When I request "stations/666/detectors"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    And the "error" and "message" property equals 'Not Found: No detectors for the requested Station ID could be found'
    And the "debug" and "source" property equals 'Detector.php:84 at call stage'
    # And the error object properties are..

  Scenario: request related onramp for valid stationid
    When I request "stations/1071/relatedonramp"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"
    And the "stationid" property equals 1071
    And the "relatedOnrampId" property equals 5071
    And the "relatedOnrampInfo" property is an object
    And the "relatedOnrampInfo" property contains an onramp
    # And the response object properties are...

  Scenario: request related onramp for an invalid stationid
    When I request "stations/666/relatedonramps"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    And the "error" and "message" property equals 'Not Found'
    And the "debug" and "source" property equals 'Routes.php:436 at route stage'
    # And the error object properties are..
    
  Scenario: request detectors for a valid stationid
    When I request "stations/1064/detectors"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"

  Scenario: request detectors for an invalid stationid
    When I request "stations/666/detectors"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    # And the error object properties are..
