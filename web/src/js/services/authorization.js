(function() {
	'use strict';

	var authorizationProvider = function($q, $window) {
		var requested = false;
		var authorize = $q.defer();
		var logout = $q.defer();
		var authorizationTokenStorageKey = 'authorization-token';
		var token = null;

		var Authorization = {
			request: function() {
				requested = true;
				return authorize.promise;
			},
			requested: function() {
				return requested;
			},
			authorize: function(newToken) {
				token = newToken;
				if (token === null) {
					$window.localStorage.removeItem(authorizationTokenStorageKey);
					logout.resolve();
					authorize = $q.defer();
				} else {
					$window.localStorage.setItem(authorizationTokenStorageKey, token);
					logout = $q.defer();
					authorize.resolve();
				}
			},
			token: function() {
				return token;
			},
			onlogout: function() {
				return logout.promise;
			},
			logout: function() {
				this.authorize(null);
			}
		};

		Authorization.authorize($window.localStorage.getItem(authorizationTokenStorageKey));

		return Authorization;
	};

	var authorizationConfig = function($httpProvider) {
		$httpProvider.interceptors.push('authorizationInterceptor');
	};

	authorizationConfig.$inject = ['$httpProvider'];
	authorizationProvider.$inject = ['$q', '$window'];

	module.exports = {
		name: 'Authorization',
		provider: authorizationProvider,
		config: authorizationConfig
	};
}) ();
