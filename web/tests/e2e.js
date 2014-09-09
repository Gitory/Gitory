var HttpBackend = require('http-backend-proxy');
var proxy = new HttpBackend(browser);
var chai = require('chai');

module.exports.expect = chai.expect;
module.exports.proxy = proxy;
