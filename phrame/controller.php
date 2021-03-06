<?php
class controller {
  var $scripts = array();
  var $styles = array();
  var $page;
  var $action;
  var $data;

  // Calls an action $name with $data for the params function
  function action ($name, $data) {
    $this->action = $name;
    $this->data   = $data;
    $this->before();
    call_user_func(array($this, $this->action)); // PHP is weird
  }
  
  // Overwrite this method to define code that is executed before every action.
  function before () {}
  
  function params ($name) {
    if ($this->data[$name]) return $this->data[$name];
    if ($_REQUEST[$name]) return $this->data[$name];
    return null;
  }

  function scripts () {
    $return = "";
    foreach($this->scripts as $script)
      $return .= '<script type="application/javascript" src="'.ROOTDIR.$script.'"></script>';
    return $return;
  }

  function styles () {
    $return = "";
    foreach($this->styles as $style)
      $return .= '<link rel="stylesheet" type="text/css" href="'.ROOTDIR.$style.'" />'."\n        ";
    return $return;
  }

  // Executes another $action inside another $controller with $data for params, without using
  // HTTP redirect.
  // NOTE: This stops the current action!
  function internalRedirect ($controller, $action, $data) {
    throw new Internal_Redirect ($controller, $action, $data);
  }

  // Performs a HTTP redirect and stops execution imidiatly
  function redirect ($path) {
    Header('Location: '.$path);
    die(301);
  }

  // 
  function render ($view = false, $opt = array()) {
      //options
      if ($opt['view'])
        $view = $opt['view'];
      elseif (!$view)
        $view = $this->action;
      
      $layout_name = $opt['layout'] ? $opt['layout'] : $this->shortname();
      
      ob_start();
      include 'views/'.$this->shortname().'/'.$view.'.php';
      $yield = ob_get_contents();
      ob_clean();
      if (file_exists('views/layouts/'.$layout_name.'.php')) {
          include 'views/layouts/'.$layout_name.'.php';
          $layout = ob_get_contents();
      } else {
          $layout = $yield;
      }
      ob_end_clean();
      if (file_exists('views/global.php'))
          include 'views/global.php';
      else
          echo $layout;
  }

  function renderPartial ($view) {
      include 'views/partials/'.$view.'.php';
  }

  function renderJSON ($data) {
      echo json_encode($data);
  }

  function renderXML ($data) {
      $document = new SimpleXMLElement('<scoome></scoome>');
      $this->recXML($document, 'data', $data);
      echo $document->asXML();
  }

  function respondWith ($hashes) {
      switch ($_REQUEST['format'] == '') {
      case 'html': render();
      }
      foreach ($hashes as $hash) {

      }
  }

  private function recXML ($obj, $key, $value) {
      if(is_array($value)){
          $child = $obj->addChild($key);
          foreach($value as $k => $v) {
              $this->recXML($child, $k, $v);
          }
      } else
          $obj->addChild($key, $value);
  }

  private $shortname;

  private function shortname () {
      if (!$this->shortname) {
          $this->shortname = explode('_', get_class($this));
          array_pop($this->shortname);
          $this->shortname = join('_', $this->shortname);
      }
      return $this->shortname;
  }
  
  function response_time () {
    global $phrame;
    return round((microtime(true) - $phrame->startTime) * 1000, 2);
  }
  
  // Generates a new cross site request forgery protection token, stores it
  // inside of $_SESSION and prints a html sniplet for a hidden input field
  // to include inside of the view
  //
  // Example (view):
  //  <form action="/user/1" method="post">
  //    <input type="hidden" name="_method" value="delete">
  //    <?php $this->csrfToken() ? >
  //  </form>
  // 
  // Example (controller):
  //  function destroy () {
  //    $this->csrfCheck();
  //    // ... some code ...
  //  }
  //
  function csrfToken () {
    $token = $this->generateToken();
    $_SESSION['last_csrf_token'] = $token;
    echo '<input type="hidden" name="csrf_token" value="'.$token.'" />';
  }
  
  // Checks if a given Cross Site Request Forgery protection token valid. If not,
  // it raises an exception
  function csrfCheck () {
    if ($_SESSION['last_csrf_token'] && $_SESSION['last_csrf_token'] !== $_REQUEST['csrf_token'])
      new Exception ('CSRF-Error: last stored token does not match the one in the request');
  }
  
  // Generates token used in csrfToken
  private function generateToken () {
    return str_shuffle(md5(rand()));
  }
}
?>