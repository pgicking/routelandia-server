Feature: Stations Controller

  Scenario: request index
    When I request "stations"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"

  Scenario: request specific station
    When I request "stations/1"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"

  Scenario: request detectors for station
    When I request "stations/1/detectors"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"

  Scenario: request related onramp for station
    When I request "stations/1/relatedonramps"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"
    # And the response object properties are...

  Scenario: request detectors for station
    When I request "stations/1/detectors"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"
