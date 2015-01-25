Feature: Highways Controller

  Scenario: request index
    When I request "highways"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"

  Scenario: request specific highway
    When I request "highways/1"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"

  Scenario: request stations for highway
    When I request "highways/1/stations"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"
