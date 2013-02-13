<?php
// This is not an error, but it is used to instantly executes a new query
// throw Internal_Redirect ('newController', 'newAction', array('id' => 'newID'))
class Internal_Redirect extends Exception {
    var $controller;
    var $action;
    var $data;
    
    function  __construct($controller, $action, $data) {
        $this->controller = $controller;
        $this->action = $action;
        $this->data = $data;
    }
}
?>