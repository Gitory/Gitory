@wip
Feature: OAuth2 authentification
    In order to access the API
    As an api client
    I need be able to act on behalf of the user

    Scenario: Anonymous request to protected resource
         When I make a "get" request to "/repositories"
         Then the response status code should be 401
          And the response header "WWW-Authenticate" should be 'Bearer realm="Gitory"'

    Scenario: Authenticated request to protected resource
        Given "The doctor" got an OAuth2 Access Token: "SONIC_SCREWDRIVER"
         When I make a "get" request to "/repositories"
         Then the response status code should be 200

    Scenario: The authorize endpoint is protected
         When I make a "get" request to "/auth/authorize?response_type=token"
         Then the response status code should be 401

    Scenario: An Authorize request without a client_id should fail
         When "admin:foo" make a "get" request to "/auth/authorize?response_type=token"
         Then the response status code should be 400
          And the response should contain "invalid_client"
          And the response should contain "No client id supplied"
          And the response should not contain "You have been sent here by"

    Scenario: An Authorize request without a valid client_id should fail
        Given "NICE_TEST_CLIENT" is a valid OAuth2 client id
         When "admin:foo" make a "get" request to "/auth/authorize?client_id=EVIL_CLIENT&response_type=token"
         Then the response status code should be 400
          And the response should contain "invalid_client"
          And the response should contain "The client id supplied is invalid"
          And the response should not contain "You have been sent here by"

    Scenario: An Authorize request without a response_type should fail
         When "admin:foo" make a "get" request to "/auth/authorize?client_id=NICE_TEST_CLIENT"
         Then the response status code should be 302
          And the response header "Location" should be "http://nice.test.client/oauth2_callback?error=invalid_request&error_description=Invalid+or+missing+response+type"

    Scenario: An Authorize request without a valid response_type should fail
         When "admin:foo" make a "get" request to "/auth/authorize?client_id=NICE_TEST_CLIENT&response_type=invalid_response_type"
         Then the response status code should be 302
          And the response header "Location" should be "http://nice.test.client/oauth2_callback?error=invalid_request&error_description=Invalid+or+missing+response+type"

    Scenario: A valid Authorize request should asks the user whether he wants to authorize the app
        Given "NICE_TEST_CLIENT" is a valid OAuth2 client id
         When "admin:foo" make a "get" request to "/auth/authorize?client_id=NICE_TEST_CLIENT&response_type=token"
         Then the response status code should be 200
          And the response should contain "You have been sent here by <strong>NICE_TEST_CLIENT</strong>"

    Scenario: A user can cancel an authorization request
        Given "NICE_TEST_CLIENT" is a valid OAuth2 client id
         When "admin:foo" make a "form" request to "/auth/authorize?client_id=NICE_TEST_CLIENT&response_type=token"
            """
            authorize=0
            """
         Then the response status code should be 302
          And the response header "Location" should be "http://nice.test.client/oauth2_callback?error=access_denied&error_description=The+user+denied+access+to+your+application"

    Scenario: A user can accept an authorization request
        Given "NICE_TEST_CLIENT" is a valid OAuth2 client id
         When "admin:foo" make a "form" request to "/auth/authorize?client_id=NICE_TEST_CLIENT&response_type=token"
            """
            authorize=1
            """
         Then the response status code should be 302
          And the response header "Location" should match "nice.test.client\/oauth2_callback#access_token=[0-9a-f]+&expires_in=3600&token_type=Bearer$"
