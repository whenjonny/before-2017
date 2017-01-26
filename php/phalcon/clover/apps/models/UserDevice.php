<?php

namespace Psgod\Models;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class UserDevice extends ModelBase
{
    const PUSH_TYPE_COMMENT = 'comment';
    const PUSH_TYPE_FOLLOW  = 'follow';
    const PUSH_TYPE_INVITE  = 'invite';
    const PUSH_TYPE_REPLY   = 'reply';
    const PUSH_TYPE_SYSTEM  = 'system';

    const VALUE_OFF  = '0';
    const VALUE_ON   = '1';

    public function getSource()
    {
        return 'users_use_devices';
    }

    public function initialize(){
        $this->addBehavior(new SoftDelete(
            array(
                'field' => 'status',
                'value' => UserDevice::STATUS_DELETED
            )
        ));
    }

    public static function newToken( $uid, $device_id, $settings = array() ){
        $device = new self();
        $device->uid = $uid;
        $device->device_id = $device_id;

        if( empty($settings) ){
            $settings = array( 'comment' => true, 'follow' => true, 'invite' => true, 'reply' => true);
        }

        $device->settings = json_encode($settings);
        $device->status = UserDevice::STATUS_NORMAL;
        $device->create_time = time();
        $device->update_time = time();
        return $device->save_and_return($device);
    }

    public static function getUsersDeviceToken($uids){
        $tokenLists = array('ios'=>array(), 'android'=>array() );
        $uidList = array_filter(explode(',', $uids));
        $where = '';
        if( empty($uidList) ){
            $where = 'TRUE';
        }
        else{
            $where = 'ud.uid IN('.$uids.') AND ud.status='.self::STATUS_NORMAL;
        }

        $builder = UserDevice::query_builder('ud')
                        ->where($where)
                        ->columns('d.platform, GROUP_CONCAT(d.token) as tokens')
                        ->join('\Psgod\Models\Device', 'd.id=ud.device_id','d','LEFT')
                        ->groupby('d.platform');
        $res = $builder->getQuery()->execute()->toArray();
        $res = array_combine(array_column($res, 'platform'), array_column($res, 'tokens') ) +array('','') ;

        $tokenLists['android']  = $res[Device::TYPE_ANDROID];
        $tokenLists['ios']      = $res[Device::TYPE_IOS];
        return $tokenLists;
    }

    public static function get_push_stgs( $uid ){
        $settings = array();
        if( empty( $uid ) ){
            return false;
        }

        $builder = UserDevice::query_builder()
                       ->where('uid='.$uid.' AND status='.UserDevice::STATUS_NORMAL);
        $res =  $builder->getQuery()
                        ->execute()
                        ->toArray();
        if($res)
            $settings = $res[0]['settings'];
        else
            return array();

        return json_decode($settings);
    }

    public static function set_push_stgs( $uid , $type, $value ){
        $settings = array();
        if( empty( $uid ) ){
            return false;
        }

        // 如果同一个用户在设备A登陆，断网，设备B登陆，在设备A修改推送设置，会修改到设备B的推送设置。
        // （前提：断网重连时，不会再验证token）
        $res = UserDevice::findFirst('uid='.$uid.' AND status='.UserDevice::STATUS_NORMAL);

        $settings = json_decode( $res->settings );
        $ret = false;
        switch( $type ){
            case UserDevice::PUSH_TYPE_COMMENT:
            case UserDevice::PUSH_TYPE_FOLLOW:
            case UserDevice::PUSH_TYPE_INVITE:
            case UserDevice::PUSH_TYPE_REPLY:
            case UserDevice::PUSH_TYPE_SYSTEM:
                $settings->$type = (bool)$value;
                $res->settings = json_encode($settings);
                $res->update_time = time();
                $res = $res->save_and_return($res);
                if( $res ){
                    $ret = json_decode($res->settings);
                }
                break;
            default:
                break;
        }

        return $ret;
    }
}
