<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/helpers.php';

session_unset();
session_destroy();
json_ok([], 'Logged out');
