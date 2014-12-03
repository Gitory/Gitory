var HttpBackend = require('http-backend-proxy');
var http = new HttpBackend(browser, {buffer: true});
var chai = require('chai');
var chaiAsPromised = require("chai-as-promised");

chai.use(chaiAsPromised);

module.exports.expect = chai.expect;
module.exports.http = http;
