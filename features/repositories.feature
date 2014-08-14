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
                "meta": {
                    "status": "success"
                },
                "response": {
                    "repositories": [
                        "gallifrey",
                        "the-doctor"
                    ]
                }
            }
            """

    Scenario: Add a repository with an existing identifier
         When I make a "post" request to "/repository"
            """
            {
                "identifier": "gallifrey"
            }
            """
         Then the response status code should be 409
         Then the response should be
            """
            {
                "meta": {
                    "status": "failure",
                    "error": {
                        "id": "existing-repository-identifier-exception",
                        "message": "A repository with identifier gallifrey already exists."
                    }
                },
                "response": {}
            }
            """

    Scenario: Add a repository
         When I make a "post" request to "/repository"
            """
            {
                "identifier": "rose"
            }
            """
         Then the response status code should be 201
         Then the response should be
            """
            {
                "meta": {
                    "status": "success"
                },
                "response": {
                    "repository": {
                        "identifier": "rose"
                    }
                }
            }
            """
