let mix = require('laravel-mix');

mix.copyDirectory('tests/Fixtures/Local', './tests/Fixtures/Public');
