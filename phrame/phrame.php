<?php
# message stored in the $_SESSION
function set_flash ($type, $msg) {
    if (!$_SESSION[$type]) $_SESSION[$type] = array($msg);
    else array_push ($_SESSION[$type], $msg);
}

# get all messages in $_SESSION
function get_flash ($type) {
    return $_SESSION[$type];
}

# flush all messages in $_SESSION
function flush_flash ($type) {
    $tmp = $_SESSION[$type];
    unset($_SESSION[$type]);
    return $tmp;
}

# The main object
# ---
# It's catches and processes the request, 
class Phrame {
  var $controller;
  var $startTime;
  var $page;

  function  __construct() {
    global $conf_env;
    global $phrame;
    $phrame = $this;
    
    // Get request time
    $this->startTime = microtime(true);
    
    // Select export format
    $_REQUEST['format'] = $_REQUEST['format'] ? $_REQUEST['format'] : 'html';
    
    $internal_redirect = false;
    $redirect_count = 0;
    $call = phrame_routing();
    do {
      try {
        $internal_redirect = false;
        $controller = new $call['controller'];
        $controller->action($call['action'], $call);
      } catch (Internal_Redirect $e) {
        $internal_redirect = true;
        $redirect_count++;
        $call = array('controller' => $e->controller,
                      'action'     => $e->action);
        $call = array_merge($call, $e->data);
      }
    } while ($internal_redirect && $redirect_count <= $conf_env['max internal redirects']);
  }
}
?>