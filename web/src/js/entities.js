(function() {
	'use strict';

	require('angular');
	var angular = window.angular;
	require('angular-resource');
	var repositoryFactory = require('./entities/repository');

	module.exports = {
		config: function(apiBaseUri) {
			var entities = angular.module('gitory.entities', ['ngResource']);
			entities.constant('apiBaseUri', apiBaseUri);

			entities.factory('Repository', repositoryFactory);
		}
	};
}) ();
