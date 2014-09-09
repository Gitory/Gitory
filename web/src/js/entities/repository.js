(function() {
	'use strict';

	module.exports = ['$resource', 'apiBaseUri', function($resource, apiBaseUri) {
		return $resource(
			apiBaseUri + '/repository/:identifier', {
				identifier: '@identifier'
			}, {
			query:  {
				method: 'GET',
				url: apiBaseUri + '/repositories',
				isArray: true,
				responseType: 'json'
			}
		});
	}];
}) ();
