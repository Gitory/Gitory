(function() {
	'use strict';

	var angular     = require('angular');
	var entities    = require('./entities');
	var controllers = require('./controllers');
	if (process.env.ENV === 'test') {
		require('angular-mocks');
	}

	var app = angular.module('gitory', ['gitory.entities']);

	entities.config(process.env.API_BASE_URI.replace(/\/$/, ''));

	angular.forEach(controllers, function(controller) {
		app.controller(controller.name, controller.constructor);
	});

	module.exports.start = function() {
		angular.element().ready(function() {
			angular.bootstrap(document, [app.name]);
		});
	};
}) ();
