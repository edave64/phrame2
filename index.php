<?php
session_start(); //Initialize session

// Load all config file
foreach (glob('config/*.php') as $file)
  include $file;

global $conf_env;

if ($conf_env['error page']) {
  function call_phrame_exception ($exception) {
    global $conf_env;
    global $error;
    $error = $exception;
    header('HTTP/1.1 500 Internal Server Error');
    include $conf_env['error page'];
  }
  set_exception_handler('call_phrame_exception');
  
  function error_handler($errorType, $message) {
    if ($errorType != E_NOTICE) {
      call_phrame_exception($message);
    }
  }
  set_error_handler('error_handler');
}

include 'phrame/adapter/'.$db_conf['adapter'].'.php';
db::connect($db_conf);

// Automaticly load all classes needed
function __autoload ($class_name) {
  $file_name = strtolower($class_name);
  switch (true) {
  // --- Phrame system files ---
  case file_exists('phrame/'.$file_name.'.php'):
    include 'phrame/'.$file_name.'.php'; break;

  // --- Controllers ---
  case file_exists('controllers/'.$file_name.'.php'):
    include 'controllers/'.$file_name.'.php'; break;

  // --- Models ---
  case file_exists('models/'.$file_name.'.php'):
    include 'models/'.$file_name.'.php'; break;

  // --- Extensions ---
  case file_exists('config/library/'.$file_name.'.php'):
    include 'config/library/'.$file_name.'.php';
    // Initialize extension
    eval($class_name.'::initialize();');
    break;
  }
  
  // Crash if the class cannot be found
  if (!class_exists($class_name))
    throw new Exception('AutoloadError: Expected class '.$class_name.' not found!');
}

new Phrame();
?>