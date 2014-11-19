var RepositoriesPage = require('../pages/repositories.js');
var e2e = require('../e2e');
var expect = e2e.expect;
var http = require('../e2e').http;

describe('add repository', function () {
	beforeEach(function() {
		http.onLoad
			.whenGET(browser.params.API_BASE_URI + '/repositories')
			.respond(200, [{identifier: 'galifrey'}]);
	});

	it('list repositories', function() {
		var repositoriesPage = new RepositoriesPage();

		repositoriesPage.get();

		expect(repositoriesPage.list()).to.eventually.have.length(1);
	});

	xit('create a repository', function() {
		var repositoriesPage = new RepositoriesPage();

		repositoriesPage.get();

		http.whenPUT(browser.params.API_BASE_URI + '/repositories/rose').respond(201, {identifier: 'rose'});

		repositoriesPage.createRepository('rose');

		expect(repositoriesPage.repositoryIdentifier()).to.eventually.equal('rose');

		expect(repositoriesPage.list()).to.eventually.have.length(2);

		http.flush();

		expect(repositoriesPage.repositoryIdentifier()).to.eventually.equal('');
		expect(repositoriesPage.list()).to.eventually.have.length(2);
	});

	xit('handle repository creation faillure', function() {
		var repositoriesPage = new RepositoriesPage();

		repositoriesPage.get();

		http.whenPUT(browser.params.API_BASE_URI + '/repositories/gallifrey').respond(409, {
			error: {
				id: "existing-repository-identifier-exception",
				message: "A repository with identifier gallifrey already exists."
			}
		});

		repositoriesPage.createRepository('gallifrey');

		expect(repositoriesPage.repositoryIdentifier()).to.eventually.equal('gallifrey');
		expect(repositoriesPage.list()).to.eventually.have.length(2);

		http.flush();

		expect(repositoriesPage.repositoryIdentifier()).to.eventually.equal('gallifrey');
		expect(repositoriesPage.list()).to.eventually.have.length(1);
	});
});
