Feature: Repositories basic API
	In order to browse through repositories
	As an api client
	I need be able to list them and get basic informations

	Background:
		Given there is a repository named "gallifrey"
		  And there is a repository named "the-doctor"

	Scenario: List repositories
		 When I make a "get" request to "/repositories"
		 Then the response should be
		 	"""
		 	{
		 		response: {
		 			repositories: [
		 				"gallifrey",
		 				"the-doctor"
		 			]
		 		}
		 	}
		 	"""
