var mock = require('../../spec').mock;

describe('defaultController', function() {
	var $scope;
	var createController;
	var repositories;
	var apiBaseUri;
	var $httpBackend;

	beforeEach(function () {
		mock.module('gitory');
	});

	beforeEach(mock.inject(function($injector) {
		var Repository = $injector.get('Repository');
		repositories = [new Repository({identifier: 'gallifrey'}), new Repository({identifier: 'rose'})];

		apiBaseUri = $injector.get('apiBaseUri');
		$httpBackend = $injector.get('$httpBackend');
		$httpBackend.whenGET(apiBaseUri + '/repositories').respond(repositories);

		$scope = $injector.get('$rootScope').$new();

		createController = function() {
			return $injector.get('$controller')('DefaultController', {
				$scope: $scope
			});
		};
	}));

	afterEach(function() {
		$httpBackend.verifyNoOutstandingExpectation();
		$httpBackend.verifyNoOutstandingRequest();
	});

	it('Initialy fetch the repository list', function() {
		createController();

		$httpBackend.flush();

		expect($scope.repositories).to.deep.include.members(repositories);
	});

	it('can create a repository', function() {
		var identifier = 'bad-wolf';

		createController();
		$httpBackend.flush();

		$httpBackend
			.expectPOST(apiBaseUri + '/repository/' + identifier, {identifier: identifier})
			.respond(201, {identifier: identifier});

		$scope.identifier = identifier;
		$scope.createRepository();

		$httpBackend.flush();

		expect($scope.repositories.length).to.equal(3);
		expect($scope.repositories[2].identifier).to.equal(identifier);
	});
});
