module.exports = function(module) {
	'use strict';

	return module.directive('gitoryRepositoriesList', function() {
		return {
			scope: {
				'repositories': '=repositoriesCollection'
			},
			templateUrl: 'directives/repositories/list.html',
			controller: 'RepositoriesListCtrl',
			controllerAs: 'ctrl',
			bindToController: true
		};
	})
	.controller('RepositoriesListCtrl', function() {});
};
