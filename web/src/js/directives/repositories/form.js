module.exports = function(module) {
	'use strict';

	return module.directive('gitoryRepositoriesForm', function() {
		return {
			scope: {
				'repositories': '=repositoriesCollection'
			},
			templateUrl: 'directives/repositories/form.html',
			controller: 'RepositoriesFormCtrl',
			controllerAs: 'ctrl',
			bindToController: true
		};
	})
	.controller('RepositoriesFormCtrl', function() {
		this.createRepository = function() {
			var repository = this.repositories.$new(this.identifier);
			repository.identifier = this.identifier;
			repository.$reveal();

			repository.$save().$then(function(repository) {
				this.identifier = '';
			}.bind(this), function(repository) {
				repository.$scope.$remove(repository);
			});
		};
	});
};
