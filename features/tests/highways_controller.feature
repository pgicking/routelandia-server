Feature: Highways Controller

  Scenario: request index
    When I request "highways"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"
    # Check it's length? (Should be 2 with our testing database)

  Scenario: request valid highwayid
    When I request "highways/9"
    Then the response status code should be 200
    And the response is JSON
    And the type is "object"
    And its "highwayid" is 1
    And its "direction" is "NORTH "
    # Check to see if it's got properties that we want...
    # Make sure it doesn't have any EXTRA properties?

  Scenario: request invalid highwayid
    When I request "highways/666"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    # And the response object's properties should be...
    # (Make sure we're getting an error object that has an error and sensible messages)


  Scenario Outline: I get the proper HTTP status code
    When I request "highways/<id>"
    Then the response status code should be <res_code>
    And the response is JSON

    Examples:
    | id | res_code |
    | 8  | 404      |
    | 9  | 200      |
    | 10 | 200      |
    | 11 | 404      |


  Scenario: request stations for valid highwayid
    When I request "highways/10/stations"
    Then the response status code should be 200
    And the response is JSON
    And the type is "array"


  Scenario: request stations for invalid highwayid
    When I request "highways/666/stations"
    Then the response status code should be 404
    And the response is JSON
    And the type is "object"
    # And the response object's properties should be...
