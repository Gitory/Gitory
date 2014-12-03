(function() {
	'use strict';

	var angular = require('./angular');
	var repositoriesFactory = require('./services/repositories');
	require('./restmod');

	module.exports = {
		config: function(apiBaseUri) {
			var services = angular.module('gitory.services', ['restmod']);
			services.config(['restmodProvider', function(restmodProvider) {
				restmodProvider.rebase({
					$config: {
						style: 'AMSApi',
						urlPrefix: apiBaseUri,
						primaryKey: 'identifier'
					}
				});
			}]);

			services.factory('Repositories', repositoriesFactory);
		}
	};
}) ();
