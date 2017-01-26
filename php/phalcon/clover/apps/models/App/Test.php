<?php
namespace Psgod\Models\App;

class Test extends \Psgod\Models\ModelBase{
    public function getSource(){
        return 'recommend_apps';
    }

    public static function test(){
        pr(1);
    }
}
