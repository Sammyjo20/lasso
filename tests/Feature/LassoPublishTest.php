<?php

use Illuminate\Support\Facades\Config;

test('you can publish assets to a filesystem with lasso', function () {
    Config::set('lasso', defaultConfig());

    $this->artisan('lasso:publish');
});
