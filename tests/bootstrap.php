<?php

$tests_dir = getenv('WP_TESTS_DIR');
if (!$tests_dir) {
    $tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $tests_dir . '/includes/functions.php';

function _manually_load_formuel(): void
{
    require dirname(__DIR__) . '/formuel.php';
}

tests_add_filter('muplugins_loaded', '_manually_load_formuel');

require $tests_dir . '/includes/bootstrap.php';
