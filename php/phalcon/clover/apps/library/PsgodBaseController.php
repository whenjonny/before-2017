<?php
namespace Psgod;

use Phalcon\Mvc\Controller;

class PsgodBaseController extends Controller
{
    public function set($var, $value)
    {
        $this->view->setVar($var, $value);
    }

    public function noview()
    {
        $this->view->disable();
    }
}
