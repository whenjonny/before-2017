<?php

namespace Psgod\Models;

class Push extends ModelBase
{
    const TYPE_ASK      = 1;
    const TYPE_REPLY    = 2;
    const TYPE_COMMENT  = 4;


    public function getSource()
    {
        return 'pushes';
    }

    public static function addNewPush($type, $data)
    {
        $obj = new self();
        $obj->type      = $type;
        $obj->data      = $data;
        $obj->create_time   = time();
        
        return $obj->save_and_return($obj);
    }

    public static function lastPushTime($type){
        $push = self::findFirst(array(
            'type='.$type,
            'order'=>'create_time desc'
        ));
        if($push) {
            return $push->create_time;
        }
        return 0;
    }
}
