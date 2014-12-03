(function() {
	'use strict';

	module.exports = function() {
		this.get = function() {
			return browser.get('/');
		};

		this.list = function() {
			return browser.findElements(by.repeater('repository in ctrl.repositories'));
		};

		var repositoryIdentifierInput = function() {
			return element(by.model('ctrl.identifier'));
		};
		var createRepositorySubmit = element(by.css('input[type=submit]'));

		this.createRepository = function(identifier) {
			repositoryIdentifierInput().sendKeys(identifier);
			return createRepositorySubmit.click();
		};

		this.repositoryIdentifier = function() {
			return repositoryIdentifierInput().getAttribute('value');
		};
	};
}) ();
