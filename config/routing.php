<?php
// This thing is pretty primitive right now. It's just a function that
// returns [controller => string, action => string, params...]

// Per default, this works like this:
//  GET    /controller/    => Controller#index
//  POST   /controller/    => Controller#create
//  GET    /controller/new => Controller#new
//  GET    /controller/id  => Controller#view
//  POST   /controller/id  => Controller#update
//  DELETE /controller/id  => Controller#destroy
//  GET    /controller/id/action => Controller#action
//
// All routes may be overwriten by the parameters ?controller= ?action=
//
// ToDo: Move action routing to controller
function phrame_routing () {
  global $conf_env;
  
  $segments = explode('/', $_SERVER['PATH_INFO']);
  $controller = $segments[1];
  $id         = $segments[2];
  $action     = $segments[3];
  
  // For Clients that don't support PUT and DELETE methodes
  if ($_REQUEST['_method']) $_SERVER['REQUEST_METHOD'] = $_REQUEST['_method'];
  
  if (!$action) {
    if (!$id) {
      if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $action = 'index';
      } else {
        $action = 'create';
      }
    } else {
      if ($id == 'new') {
        $action = $id;
        $id = null;
      } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $action = 'view';
      } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = 'update';
      } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
        $action = 'destroy';
      }
    }
  }
  
  return array(
    'controller' => ucfirst($_REQUEST['controller'] ? $_REQUEST['controller'] :
                      ($controller ? $controller : $conf_env['root controller'])).'_Controller',
    'action'     => $_REQUEST['action'] ? $_REQUEST['action'] : $action,
    'id'         => $_REQUEST['id'] ? $_REQUEST['id'] : $id
  );
}
?>