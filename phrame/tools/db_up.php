<?php
include '../../config/enviroment.php';
include '../../config/database.php';

function call_phrame_exception ($exception) {
  global $conf_env;
  global $error;
  $error = $exception;
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

include '../adapter/'.$db_conf['adapter'].'.php';

db::setup($db_conf);

print '<p>database '.$db_conf['database'].' created</p>';

require_once '../model.php';

foreach (glob('../../models/*.php') as $file) {
    print '<p>'.$file.'</p>';
    require_once $file;
    eval(basename($file, '.php').'::setup("");');
}
?>
