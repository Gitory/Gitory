(function() {
	'use strict';

	var authorizationInterceptor = function($q, Authorization, $injector) {
		return {
			request: function(request) {
				var token = Authorization.token();
				if (token !== null) {
					request.headers.Authorization = 'Bearer ' + token;
				}
				return request;
			},
			responseError: function(response) {
				if (response.status === 401) {
					return Authorization.request().then(function() {
						return $injector.get('$http')(response.config);
					});
				}

				return $q.reject(response);
			}
		};
	};

	authorizationInterceptor.$inject = ['$q', 'Authorization', '$injector'];

	module.exports = {
		name: 'authorizationInterceptor',
		provider: authorizationInterceptor
	};
}) ();
