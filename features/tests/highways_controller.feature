Feature: Highways Controller

  Scenario: request index
    When I request "highways"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"

  Scenario: request valid highwayid
    When I request "highways/1"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"

  Scenario: request invalid highwayid
    When I request "highways/666"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    # And the response object's properties should be...

  Scenario: request stations for valid highwayid
    When I request "highways/1/stations"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"

  Scenario: request stations for invalid highwayid
    When I request "highways/666/stations"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    # And the response object's properties should be...
