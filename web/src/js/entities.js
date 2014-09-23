(function() {
	'use strict';

	var angular = require('angular');
	require('npm-angular-resource')(window, angular);
	var repositoryFactory = require('./entities/repository');

	module.exports = {
		config: function(apiBaseUri) {
			var entities = angular.module('gitory.entities', ['ngResource']);
			entities.constant('apiBaseUri', apiBaseUri);

			entities.factory('Repository', repositoryFactory);
		}
	};
}) ();
