var RepositoriesPage = require('../pages/repositories.js');
var e2e = require('../e2e');
var expect = e2e.expect;
var proxy = require('../e2e').proxy;

describe('add repository', function () {
	it('list repositories', function() {
		proxy.onLoad
			.whenGET(browser.params.API_BASE_URI + '/repositories')
			.respond(200, [{identifier: 'galifrey'}]);

		var repositoriesPage = new RepositoriesPage();

		repositoriesPage.get();

		repositoriesPage.list().then(function(repositories) {
			expect(repositories.length).to.equal(1);
		});
	});

	it('create a repository', function() {
		proxy.onLoad
			.whenGET(browser.params.API_BASE_URI + '/repositories')
			.respond(200, [{identifier: 'galifrey'}]);

		var repositoriesPage = new RepositoriesPage();

		repositoriesPage.get();

		proxy.whenPOST(browser.params.API_BASE_URI + '/repository/rose')
			.respond(201, {identifier: 'rose'});

		repositoriesPage.createRepository('rose');

		repositoriesPage.repositoryIdentifierInput.getAttribute('value').then(function(identifier) {
			expect(identifier).to.equal('');
		});

		repositoriesPage.list().then(function(repositories) {
			expect(repositories.length).to.equal(2);
		});

	});

	it('handle repository creation faillure', function() {
		proxy.onLoad
			.whenGET(browser.params.API_BASE_URI + '/repositories')
			.respond(200, [{identifier: 'galifrey'}]);

		var repositoriesPage = new RepositoriesPage();

		repositoriesPage.get();

		proxy.whenPOST(browser.params.API_BASE_URI + '/repository/gallifrey')
			.respond(409, {
				error: {
					id: "existing-repository-identifier-exception",
					message: "A repository with identifier gallifrey already exists."
				}
			});

		repositoriesPage.createRepository('gallifrey');

		repositoriesPage.list().then(function(repositories) {
			expect(repositories.length).to.equal(1);
		});

		repositoriesPage.repositoryIdentifierInput.getAttribute('value').then(function(identifier) {
			expect(identifier).to.equal('gallifrey');
		});

		repositoriesPage.flashMessage.getText().then(function(flashMessage) {
			expect(flashMessage).to.equal('A repository with identifier gallifrey already exists.');
		});
	});
});
