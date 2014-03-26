<?php
namespace Instcar\Server\Controllers;

class ErrorController extends ControllerBase
{
    public function show404Action()
    {
      $this->flashJson(404, array(), "404, Not Found!");
    }
}

