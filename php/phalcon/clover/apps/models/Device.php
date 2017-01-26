<?php

namespace Psgod\Models;

class Device extends ModelBase
{
    const TYPE_UNKNOWN = -1;
    const TYPE_ANDROID = 0;
    const TYPE_IOS     = 1;


    public function getSource()
    {
        return 'devices';
    }


    public static function newToken( $uid, $device_name, $device_os, $platform, $device_mac, $token, $options = '' ){
        $device = new self();
        $device->uid        = $uid;
        $device->name       = $device_name;
        $device->mac        = $device_mac;
        $device->type       = 0;//Unknown
        $device->platform   = $platform;
        $device->os         = $device_os;
        $device->token      = $token;
        $device->options    = $options;

        $device->create_time = time();
        $device->update_time = time();
        return $device->save_and_return($device,false);
    }
}
