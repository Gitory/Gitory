(function() {
	'use strict';

	module.exports = {
		name: 'DefaultController',
		constructor: [
			'$scope', 'Repository', function($scope, Repository) {
				$scope.flashMessage = null;

				$scope.repositories = Repository.query();

				$scope.createRepository = function() {
					var repo = new Repository({identifier: $scope.identifier});

					$scope.repositories.push(repo);

					repo.$save().then(function(res) {
						$scope.identifier = '';
					}, function(res) {
						$scope.repositories = $scope.repositories.filter(function(repository) {
							return repository !== repo;
						});

						$scope.flashMessage = res.data.error.message;
					});
				};
			}
		]
	};
}) ();
