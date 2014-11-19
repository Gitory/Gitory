(function() {
	'use strict';

	var angular = require('./angular');
	if (process.env.ENV === 'test') {
		require('angular-mocks');
	}

	require('./directives');
	require('./services').config(process.env.API_BASE_URI.replace(/\/$/, ''));
	angular.module('gitory', ['gitory.directives']);
	require('../../tmp/templates.js');
}) ();
