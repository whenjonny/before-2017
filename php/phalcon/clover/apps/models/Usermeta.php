<?php
namespace Psgod\Models;

class Usermeta extends ModelBase
{

    const KEY_REMARK = 'remark';
    const KEY_FORBID = 'forbid_speech'; //禁言

    const KEY_LAST_READ_COMMENT = 'last_read_comment';
    const KEY_LAST_READ_FOLLOW  = 'last_read_fellow';
    const KEY_LAST_READ_INVITE  = 'last_read_invite';
    const KEY_LAST_READ_REPLY   = 'last_read_reply';
    const KEY_LAST_READ_NOTICE  = 'last_read_notice';

    const KEY_STAFF_TIME_PRICE_RATE = 'staff_time_price_rate';

    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $uid;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $str_value;

    /**
     * @var integer
     */
    public $int_value;

    public function columnMap()
    {
        return array(
            'umeta_id'          => 'id', 
            'uid'               => 'uid', 
            'umeta_key'         => 'key', 
            'umeta_str_value'   => 'str_value',
            'umeta_int_value'   => 'int_value',
        );
    }

    public function initialize()
    {
        $this->belongsTo("uid", "Psgod\Models\User", "uid", array(
            'alias' => 'User'
        ));
    }

    /**
     * 添加用户 key value 类型数据
     * 
     * @param integer $uid   用户ID
     * @param string  $key   键
     * @param string  $value 值
     * @param boolean $is_int值是否是数字
     */
    public static function writeUserMeta($uid, $key, $value, $is_int=false)
    {
        $meta = self::findFirst("uid='{$uid}' and key='{$key}'");

        $umeta = $meta ? $meta : new self();
        $umeta->uid = $uid;
        $umeta->key = $key;
        if ($is_int) {
            $umeta->int_value = $value;
        } else {
            $umeta->str_value = $value;
        }

        return $umeta->save_and_return($umeta, true);
    }

    /**
     * 获取用户相关数据
     *
     * @param string $uid 用户ID
     * @param string $key 可选。键
     * @param string $is_int 可选。值是否为整型
     */
    public static function readUserMeta($uid, $key='', $is_int=false)
    {
        if (!empty($key)) { // 有指定键，就只找出这个键的值
            $result = self::findFirst(array(
                'conditions' => "uid='{$uid}' and key='{$key}'",
            ));
            if ($result) {
                return array( 
                    $key => $is_int ? (int) $result->int_value : $result->str_value
                );
            } else {
                return array();
            }
        } else {    // 没指定键就去找这个用户所有的值
            $result = array();
            $metas = self::find(array(
                'conditions' => "uid='{$uid}'"
            ));
            if ($metas) {
                foreach ($metas as $m) {
                    $result[$m->key] = ( !empty($m->int_value)||($m->int_value===0) ? $m->int_value : $m->str_value);
                }
            }

            return $result;
        }
    }

    /**
     * 添加用户备注
     * @param  [int]    $uid    [用户id]
     * @param  [string] $remark [用户备注]
     * @return [model]  $umeta  [用户扩展模型]
     */
    public static function write_user_remark($uid, $remark) {
        return self::writeUserMeta($uid, self::KEY_REMARK, $remark, false);
    }

    /**
     * 获取用户备注
     * @param  [int]    $uid    [用户id]
     * @return [string] $remark [用户备注]
     */
    public static function read_user_remark($uid) {
        $result = self::readUserMeta($uid, self::KEY_REMARK, false);
        if($result)
            return $result[self::KEY_REMARK];
        else
            return '';
    }

    /**
     * [read_user_forbid 获取用户禁言状态]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public static function read_user_forbid($uid){
        $result = self::readUserMeta($uid, self::KEY_FORBID, true);
        if($result)
            return $result[self::KEY_FORBID];
        else{
            return '';
        }
    }

    /**
     * 添加用户禁言状态
     * @param  [int]    $uid    [用户id]
     * @param  [string] $value  [禁言值(-1永久禁言,0或者过去的时间为不禁言,将来的时间则为禁言)]
     */
    public static function write_user_forbid($uid, $value) {
        return self::writeUserMeta($uid, self::KEY_FORBID, $value, true);
    }

    public static function refresh_read_notify( $uid, $type, $time = -1 ){
        $last_modified = self::readUserMeta( $uid, $type );
        if( !array_key_exists($type, $last_modified ) ){
            $last_modified = 0;
        }
        else{
            $last_modified = $last_modified[$type];
        }
        if( $time == -1 ){
            $time = time();
        }
        self::writeUserMeta( $uid, $type, $time );

        return $last_modified;
    }
}
