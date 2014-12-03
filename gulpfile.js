(function() {
	'use strict';

	var prefs = {
		browser: 'Firefox',
		host: 'localhost',
		webPort: 4000,
		apiPort: 4001,
		livereload: true,
		'notify-log-level': 2
	};

	if (require('fs').existsSync('prefs.json')) {
		require('util')._extend(prefs, require('./prefs'));
		process.env.SAUCE_USERNAME = prefs.sauce.username;
		process.env.SAUCE_ACCESS_KEY = prefs.sauce.accessKey;

		if (prefs.CHROME_BIN) {
			process.env.CHROME_BIN = prefs.CHROME_BIN;
		}
	}

	var argv = require('yargs')
		.default('host', prefs.host)
		.default('web-port', prefs.webPort)
		.default('api-port', prefs.apiPort)
		.default('env', 'dev')
		.default('debug', false)
		.default('watch', false)
		.default('reload', prefs.livereload)
		.default('notify-log-level', prefs['notify-log-level'])
		.default('browser', prefs.browser)
		.argv;

	var gulp        = require('gulp');
	var notify      = require('gulp-notify');
	var source      = require('vinyl-source-stream');
	var watchify    = require('watchify');
	var plumber     = require('gulp-plumber');
	var browserify  = require('browserify');
	var envify      = require('envify/custom');
	var plugins     = require('gulp-load-plugins')();
	var livereload  = require('gulp-livereload');
	var connectLr   = require('connect-livereload');
	var express     = require('express');
	var PHPServer   = require('php-built-in-server');
	var pleeease    = require('gulp-pleeease');
	var protractor  = require('gulp-protractor');
	var templates   = require('gulp-angular-templatecache');
	var minifyHTML  = require('gulp-minify-html');
	var PHPRoot     = __dirname + '/api/';
	var debug       = argv.debug;
	var watch       = argv.watch;
	var reload      = argv.reload;
	var env         = argv.env;
	var exitCode    = 0;

	var staticServer = express();
	var apiServer = new PHPServer();

	notify.logLevel(argv.notifyLogLevel);

	var staticRoot = function() {
		return __dirname + '/web/public/' + env + '/';
	};

	gulp.task('server:api:start', function(done) {
		apiServer.on('listening', function (event) {
			plugins.util.log(
				'[server:api:' + env + ']',
				'Listening on http://' + event.host.address + ':' + event.host.port + '/'
			);
		});
		apiServer.on('error', function (event) {
			plugins.util.log('[server:api-' + env + ']', event.error.toString());
		});
		apiServer.listen(PHPRoot, argv.apiPort, argv.host, PHPRoot + env + '.php');

		done();
	});

	gulp.task('server:api:stop', function() {
		plugins.util.log('[server:api-' + env + ']', 'stop');
		apiServer.close();
	});

	gulp.task('server:static:start', function(done) {
		if (watch && reload) {
			staticServer.use(connectLr());
		}
		var expressRoot = staticRoot();
		staticServer.use(express.static(expressRoot));
		staticServer.use(function(req, res) {
			res.sendFile(expressRoot + 'index.html');
		});
		staticServer.instance = staticServer.listen(argv.webPort, argv.host, function() {
			plugins.util.log('[server:static:' + env + ']', 'Listening on http://' + argv.host + ':' + argv.webPort + '/');
			done();
		});
	});

	gulp.task('server:static:stop', function() {
		if (staticServer.instance === undefined) {
			return;
		}
		plugins.util.log('[server:static:' + env + ']', 'stop');
		staticServer.instance.close();
	});

	gulp.task('build:html', function() {
		var files = [
			__dirname + '/web/src/index.html',
		];

		if (watch && !this.watchingHTML) {
			this.watchingHTML = true;
			gulp.watch(files, ['build:html']);
		}

		var stream = gulp.src(files)
			.pipe(gulp.dest(staticRoot()));

		if (!watch || !reload) {
			return stream;
		}

		return stream.pipe(livereload());
	});

	gulp.task('servers:start', ['server:api:start', 'server:static:start']);

	gulp.task('servers:stop', ['server:api:stop', 'server:static:stop']);

	gulp.task('build:css', function() {
		if (watch && !this.watchingCSS) {
			this.watchingCSS = true;
			gulp.watch(__dirname + '/web/src/styles/**.css', ['build:css']);
		}

		var stream = gulp.src(__dirname + '/web/src/styles/gitory.css')
			.pipe(plumber());

		stream = stream
			.pipe(pleeease({
				optimizers: {
					minifier: true
				},
				next: {
					customProperties: true
				},
				import: {
					path: __dirname + '/web/src/styles/'
				},
				sourcemaps: debug
			}));

		stream = stream.pipe(gulp.dest(staticRoot()));

		if (!watch || !reload) {
			return stream;
		}

		return stream.pipe(livereload());
	});

	gulp.task('build:js:templates', function() {
		var files = [
			__dirname + '/web/src/views/**/*.html',
		];

		if (watch && !this.watchingTemplates) {
			this.watchingTemplates = true;
			gulp.watch(files, ['build:js:templates']);
		}

		return gulp.src(files)
			.pipe(minifyHTML({
				quotes: true
			}))
			.pipe(templates('templates.js', {
				module: 'gitory'
			}))
			.pipe(gulp.dest(__dirname + '/web/tmp/'));
	});

	gulp.task('build:js:app', function() {
		var args = {
			standalone: 'gitory',
			basedir: __dirname,
			debug: debug,
			cache: {}, // required for watchify
			packageCache: {}, // required for watchify
			fullPaths: watch // required to be true only for watchify
		};
		var bundler = browserify(__dirname + '/web/src/js/app.js', args);

		if (watch) {
			bundler = watchify(bundler);
		}

		function rebundle() {
			bundler.transform(envify({
				API_BASE_URI: 'http://' + argv.host + ':' + argv.apiPort + '/',
				ENV: env,
				DEBUG: argv.debug
			}));

			bundler.plugin('minifyify', debug ? {
				map: '/gitory.js.map.json',
				output: staticRoot() + 'gitory.js.map.json',
				compressPath: __dirname
			}: {
				map: false,
				output: false
			});

			var stream = bundler.bundle()
				.pipe(source('gitory.js'))
				.pipe(gulp.dest(staticRoot()));

			if (!watch || !reload) {
				return stream;
			}

			return stream.pipe(livereload());
		}
		bundler.on('update', rebundle);

		return rebundle();
	});

	gulp.task('build:js', ['build:js:templates', 'build:js:app']);

	gulp.on('err', function (err) {
		exitCode = 1;
	});

	process.on('exit', function () {
		process.exit(exitCode);
	});

	gulp.task('webdriver:update', protractor.webdriver_update);

	gulp.task('protractor', ['webdriver:update'], function() {
		var files = __dirname + '/web/tests/e2e/**/*.js';

		if (watch && !this.watchingE2E) {
			this.watchingE2E = true;
			gulp.watch([__dirname + '/web/public/test/**', __dirname + '/web/pages/**', files], ['protractor']);
		}

		var browsers = argv.browser;

		if (!(browsers instanceof Array)) {
			browsers = [debug && browsers === 'PhantomJS' ? 'Firefox' : browsers];
		}

		var multiCapabilities = browsers.map(function(browser) {
			return {
				browserName: browser.toLowerCase(),
				'webdriver.firefox.bin': '/home/mathieu/bin/firefox-nightly/firefo'
			};
		});

		var configFile = __dirname + '/web/tests/e2e-config.js';
		var config = {
			multiCapabilities : multiCapabilities,
			chromeOnly: argv.browser === 'Chrome',
			framework: 'mocha',
			mochaOpts: {
				timeout: 10000,
				slow: 6000
			},
			baseUrl: 'http://' + argv.host + ':' + argv.webPort + '/',
			params: {
				API_BASE_URI: 'http://' + argv.host + ':' + argv.apiPort
			}
		};
		config = 'module.exports.config = ' + JSON.stringify(config) + ';';
		require('fs').writeFile(configFile, config);

		return gulp.src(files)
			.pipe(plumber())
			.pipe(protractor.protractor( {
				configFile: configFile,
				debug: debug
			}));
	});

	gulp.task('test:js', ['set:test-env', 'build', 'server:static:start'], function() {
		var cb = function() {
			if (!watch) {
				gulp.start('servers:stop', function() {});
			}
		};

		gulp.start('protractor', cb);
	});


	gulp.task('build', ['build:html', 'build:js', 'build:css']);


	gulp.task('set:test-env', function() {
		env = 'test';
	});

	gulp.task('set:watch', function() {
		watch = true;
	});

	gulp.task('set:debug', function() {
		debug = true;
	});

	gulp.task('set:reload', function() {
		reload = true;
	});


	gulp.task('default', ['build']);

	gulp.task('run', ['build', 'servers:start']);

	gulp.task('dev', ['set:watch', 'set:debug', 'run']);

	gulp.task('debug', ['set:debug', 'run']);
})();
