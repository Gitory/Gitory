(function() {
	'use strict';

	var angular = require('./angular');
	var directives = angular.module('gitory.directives', ['gitory.services']);
	require('./directives/repositories')(directives);
	require('./directives/repositories/list')(directives);
	require('./directives/repositories/form')(directives);
	require('./directives/oauth2/login-button')(directives);
	module.exports = directives;
}) ();
