(function() {
	'use strict';

	var angular = require('./angular');
	var servicesDefinitions = [
		require('./services/authorization'),
		require('./services/authorization/interceptor'),
		// require('./services/session'),
		require('./services/repositories')
	];
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

			for (var i = 0; i < servicesDefinitions.length; i++) {
				var serviceDefinition = servicesDefinitions[i];

				if (serviceDefinition.config !== undefined) {
					services.config(serviceDefinition.config);
				}

				if (serviceDefinition.provider !== undefined) {
					services.factory(serviceDefinition.name, serviceDefinition.provider);
				}
			}
		}
	};
}) ();
