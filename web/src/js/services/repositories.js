(function() {
	'use strict';

	var repositories = function(restmod) {
		return restmod.model('/repositories');
	};

	repositories.$inject = ['restmod'];

	module.exports = {
		name: 'Repositories',
		provider: repositories
	};
}) ();
