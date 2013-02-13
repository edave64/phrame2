<?php
include '../../config/enviroment.php';
include '../../config/database.php';

function call_phrame_exception ($exception) {
  global $conf_env;
  global $error;
  $error = $exception;
  echo 'asfge';
  print_r($error);
  header('HTTP/1.1 500 Internal Server Error');
  include '../error_handler.php';
}
set_exception_handler('call_phrame_exception');

function error_handler($errorType, $message) {
  if ($errorType != E_NOTICE) {
    call_phrame_exception(new Exception($message));
  }
}
set_error_handler('error_handler');

include '../../adapter/'.$db_conf['adapter'].'.php';

db::connect($db_conf);

require_once 'system/model.php';

foreach (glob('../../models/*.php') as $file) {
  require_once $file;
  eval(ucfirst(basename($file, '.php')).'::drop();');
  print '<p>Table '.ucfirst(basename($file, '.php')).' dropped!</p>';
}

db::dropDB($db_conf['database']);
print '<p>Database '.$db_conf['database'].' droped!</p>';
?>
