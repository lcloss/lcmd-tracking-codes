const gulp = require('gulp');
const readme = require('gulp-readme-to-markdown');

gulp.task('readme', function() {
  gulp.src([ 'README.txt' ])
  .pipe(readme({
    details: false,
    screenshot_ext: ['jpg', 'jpg', 'png'],
    extract: {
      'changelog': 'CHANGELOG',
      'Frequently Asked Questions': 'FAQ'
    }
  }))
  .pipe(gulp.dest('.'));
});