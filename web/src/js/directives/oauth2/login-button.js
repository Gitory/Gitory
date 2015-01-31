module.exports = function(module) {
	'use strict';

	var OAuth2LoginButtonCtrl = function(Authorization, $window, $interval, $q, $location) {
		var clientId = encodeURIComponent(this.clientId);
		var responseType = encodeURIComponent(this.responseType);
		var url = 'http://localhost:4001/auth/authorize?client_id=' + clientId + '&response_type=' + responseType;
		var oauth2AuthorizationResponseKey = 'oauth2-authorization-response';

		var readOAuth2AuthorizationResponse = function() {
			var oauth2AuthorizationResponse = $window.localStorage.getItem(oauth2AuthorizationResponseKey);

			if (oauth2AuthorizationResponse !== null) {
				$window.localStorage.removeItem(oauth2AuthorizationResponseKey);
				return JSON.parse(oauth2AuthorizationResponse);
			}
		};

		readOAuth2AuthorizationResponse();

		if ($location.path() === this.callbackPath) {
			var oauth2AuthorizationResponse = $location.search();
			var accessTokenMatch = /access_token=([a-f0-9]+)[^a-f0_9]/.exec($location.hash());
			if (accessTokenMatch !== null) {
				oauth2AuthorizationResponse = {
					token: accessTokenMatch[1]
				};
			}
			$window.localStorage.setItem(
				oauth2AuthorizationResponseKey,
				JSON.stringify(oauth2AuthorizationResponse)
			);
			$window.close();
		}

		var waitForAuthorization = function(authorizationWindow) {
			var authorizationRequest = $q.defer();
			var interval = $interval(function() {
				if (authorizationWindow.closed) {
					$interval.cancel(interval);
					var oauth2AuthorizationResponse = readOAuth2AuthorizationResponse();

					if (oauth2AuthorizationResponse.token !== undefined) {
						authorizationRequest.resolve(oauth2AuthorizationResponse.token);
					} else {
						authorizationRequest.reject(oauth2AuthorizationResponse);
					}
				}
			}, 100);
			return authorizationRequest.promise;
		};

		this.required = function() {
			return Authorization.required();
		};
		this.login = function() {
			return waitForAuthorization($window.open(url)).then(Authorization.authorize);
		};
	};
	OAuth2LoginButtonCtrl.$inject = ['Authorization', '$window', '$interval', '$q', '$location'];

	return module.directive('gitoryOAuth2LoginButton', function() {
		return {
			scope: {
				'clientId': '@',
				'responseType': '@',
				'callbackPath': '@'
			},
			templateUrl: 'directives/oauth2/login-button.html',
			controller: 'OAuth2LoginButtonCtrl',
			controllerAs: 'ctrl',
			bindToController: true
		};
	})
	.controller('OAuth2LoginButtonCtrl', OAuth2LoginButtonCtrl);
};
