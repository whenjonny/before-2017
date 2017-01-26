<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\User;
use Psgod\Models\Usermeta;

class IndexController extends ControllerBase
{

    public function indexAction() {
        //$this->output_table(User::find());
    }
    public function aaaActin(){
        $this->noview();
        echo 'asdfadsf';
        exit;
    }
}

