(function() {
	'use strict';

	var angular = require('./angular');
	var directives = angular.module('gitory.directives', ['gitory.services']);
	require('./directives/repositories')(directives);
	require('./directives/repositories/list')(directives);
	require('./directives/repositories/form')(directives);
	module.exports = directives;
}) ();
