Feature: Job basic CLI tools
    In order to execute jobs
    As an cli client
    I need be able to consume them from the cli

    Scenario: There is no job to consume
         When I execute "job:consume"
         Then the output should contains
            """
            Job queue is empty
            """

    Scenario: Pop a job
        Given there is a pending job "repository:creation" with payload
            """
            {
                "identifier": "rose"
            }
            """
         When I execute "job:consume"
         Then the output should contains
            """
            Job #1 "repository:creation" has been consumed
            """
         Then there is no pending job
          And Job "#1" status is not pending

    Scenario: Pop a the oldest job
        Given there is a pending job "repository:creation" with payload
            """
            {
                "identifier": "badwolf"
            }
            """
        Given there is a pending job "repository:creation" with payload
            """
            {
                "identifier": "rose"
            }
            """
         When I execute "job:consume"
         Then the output should contains
            """
            Job #1 "repository:creation" has been consumed
            """
         Then there is one pending job
