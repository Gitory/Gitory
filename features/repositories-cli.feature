Feature: Repositories basic CLI tools
    In order to manage repositories
    As an cli client
    I need be able to work on them from the cli

    Background:
        Given there is a repository named "gallifrey"
          And the "gallifrey" repository folder does not exists
          And there is a repository named "the-doctor"
          And there is a repository named "badwolf"

    Scenario: Create a new repository
         When I execute "repository:create badwolf"
         Then the output should contains
            """
            Repository "badwolf" has been created
            """
          And the git repository "badwolf" exists

    Scenario: Create a not found repository
         When I execute "repository:create dalek"
         Then the exception output should be
            """
            Repository "dalek" not found in database, git repository hasn't been created
            """
          And the "dalek" repository does not exists

    Scenario: Create a repository folder already exists
        Given the "gallifrey" repository folder exists
         When I execute "repository:create gallifrey"
         Then the exception output should be
            """
            Repository "gallifrey" folder already exists
            """
