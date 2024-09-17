/**
 * Gulp v4 Configuration
 *
 * @version 	2024.1.26
 * @name    	CSlice\Gulp
 */

// Compression settings
let environment = 'prod';
if ('undefined' !== typeof process.env.NODE_ENV && 'development' === process.env.NODE_ENV) {
	environment = 'dev';
}

// Gulp modules
const gulp =         require('gulp');
const sass =         require('gulp-sass')(require('sass'));
const autoprefixer = require('gulp-autoprefixer');
const cleanCSS =     require('gulp-clean-css');
const stripDebug =   require('gulp-strip-debug');
const jsHint =       require('gulp-jshint');
const notify =       require('gulp-notify');
const plumber =      require('gulp-plumber');
const babel =        require('gulp-babel');
const terser =       require('gulp-terser');
const gulpif =       require('gulp-if');


/**
 * Error handler
 */
const onError = function (error) {
	notify.onError({
		title: 'Gulp',
		subtitle: 'Failure!',
		message: 'Error: <%= error.message %>',
	})(error);
	this.emit('end');
};


/**
 * CSS frontend
 */
gulp.task('styles', function () {
	return gulp
		.src('src/cslice-video-in-modal.scss')
		.pipe(plumber({ errorHandler: onError }))
		.pipe(sass())
		.pipe(autoprefixer())
		.pipe(gulpif('prod' == environment, cleanCSS()))
		.pipe(gulp.dest('assets'));
});


/**
 * JAVASCRIPT frontend
 */
gulp.task('scripts', function () {
	return gulp
		.src('src/cslice-video-in-modal.js')
		.pipe(plumber({ errorHandler: onError }))
		.pipe(jsHint())
		.pipe(babel({ presets: ['@babel/preset-env'] }))
		.pipe(gulpif('prod' == environment, stripDebug()))
		.pipe(gulpif('prod' == environment, terser()))
		.pipe(gulp.dest('assets'))
});


/**
 * GULP Task 'gulp'
 */
gulp.task('default', gulp.series(
	'styles',
	'scripts'
));


/**
 * GULP WATCH Task 'gulp watch'
 */
gulp.task('watch', function () {
	// Styles
	gulp.watch('src/*.scss', gulp.series('styles'));

	// Scripts
	gulp.watch('src/*.js', gulp.series('scripts'));
});
