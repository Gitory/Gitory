Feature: Cross Origin API
    In order to access the API
    As an api client
    I need be able to make xhr request from any domain

    Scenario: List repositories
         When I make a "options" request to "/repositories"
         Then the response header "Access-Control-Allow-Origin" should be "*"
          And the response header "Access-Control-Allow-Methods" should be "GET, DELETE, POST, PUT"
          And the response header "Access-Control-Allow-Headers" should be "Authorization, Content-Type"
