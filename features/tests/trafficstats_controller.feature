Feature: TrafficStats Controller
  
  Scenario: test JSON Post
  	Given I have the payload:
  	"""
  	{
    	"startpt": {
       		"lat": -122.00, 
       		"lng": 45.00 
       	}, 
    	"endpt": { 
       		"lat": -122.01, 
       		"lng": 45.00 
    	}, 
    	"time": { 
       		"midpoint": "17:30", 
       		"weekday": "Thursday" 
    }
    """
    When I request "POST /trafficstats.json"
    Then the response status code should be 200
    When I request "GET /trafficstats.json"
    Then the response status code should be 200
    
  # Examples of how to check values based on sending a specific payload shown here:
  # https://github.com/Luracast/Restler/blob/master/features/tests/param/type.feature#L314
