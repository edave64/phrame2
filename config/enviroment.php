<?php
global $conf_env;
$conf_env = array();

// Defines the file that will be shown on an internal server error
$conf_env['error page'] = '/phrame/error_handler.php';

// Limit for internal redirects (to prevent infinite-loops)
$conf_env['max internal redirects'] = 10;

// Controller to use if none is specified
$conf_env['root controller'] = 'Default';
?>