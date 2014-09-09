(function() {
	'use strict';

	module.exports = function() {
		this.get = function() {
			browser.get('/');
		};

		this.list = function() {
			return browser.findElements(by.repeater('repository in repositories'));
		};

		this.repositoryIdentifierInput = element(by.model('identifier'));
		this.createRepositorySubmit = element(by.css('input[type=submit]'));
		this.flashMessage = element(by.binding('flashMessage'));

		this.createRepository = function(identifier) {
			this.repositoryIdentifierInput.sendKeys(identifier);
			return this.createRepositorySubmit.click();
		};
	};
}) ();
