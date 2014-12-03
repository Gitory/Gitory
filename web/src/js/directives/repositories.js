module.exports = function(module) {
	'use strict';

	return module.directive('gitoryRepositories', function() {
		return {
			scope: {
				'collectionName': '='
			},
			controller: 'RepositoriesCtrl'
		};
	})
	.controller('RepositoriesCtrl', ['Repositories', '$scope', function(Repositories, $scope) {
		$scope.collectionName = Repositories.$collection();
		$scope.collectionName.$refresh();
	}]);
};
