Feature: Detectors Controller

  Scenario: request index
	When I request "detectors"
	Then the response status code should be 200
    And the response is JSON
    And the type is "array"

  Scenario: request a valid detector
	When I request "detectors/100059"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"

  Scenario: request an invalid detector
    When I request "detectors/666"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    # And the error object properties are..

  Scenario: request related station for a valid detector
    When I request "stations/1064/detectors"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"

  Scenario: request related station for a invalid detector
    When I request "stations/666/detectors"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"

  Scenario: request the active status of a detector that is still active
    When I request "detectors/100059/stillactive"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"
