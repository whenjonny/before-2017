<?php
namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class UserLanding extends ModelBase
{
    const TYPE_WEIXIN = 1;
    const TYPE_WEIBO  = 2;
    const TYPE_QQ     = 3;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->hasMany("uid", "Psgod\Models\User", "uid", array(
            'alias' => 'users'
        ));
    }

    public function getSource()
    {
        return 'user_landings';
    }

    /**
     * 新添加用户
     * 
     * @param string $username 用户名
     * @param string $password 密码
     * @param string $nickname 昵称
     * @param integer$phone    手机号码
     * @param string $email    邮箱地址
     * @param array  $options  其它。暂不支持
     */
    public static function addNewUser($username, $password, $nickname, $phone, $location='', $email='', $avatar='', $sex = self::SEX_MAN, $options=array())
    {
        $u = new \Psgod\Models\User();
        $u->phone         = $phone;
        $u->username      = $username;
        $u->password      = \Psgod\Models\User::hash($password);
        $u->nickname      = $nickname;
        $u->email         = $email;
        $u->userurl       = '';
        $u->activate_key  = '';
        $u->status        = self::STATUS_NORMAL;
        $u->avatar        = $avatar;
        $u->user_score    = 0;
        $u->ps_score      = 0;
        $u->is_god        = 0;
        $u->discribe      = '';
        $u->sex           = $sex;
        $u->asks_count    = 0;
        $u->replies_count = 0;
        $u->uped_count    = 0;
        $u->location      = $location;
        $u->update_time   = time();
        $u->create_time   = time();
        $u->login_ip      = get_client_ip();

        return $u->save_and_return($u, true);
    }

    /**
     * 绑定/解绑用户
     */
    public static function setUserLanding($uid, $openid, $type = self::TYPE_WEIXIN, $status = self::STATUS_NORMAL) {

        $type = self::get_landing_type($type);
        $o = new self();
        $o->uid         = $uid;
        $o->openid      = $openid;
        $o->type        = $type;
        $o->status      = $status;

        return $o->save_and_return($o, true);
    }

    /**
     * 添加微信用户
     * 
     * @param string  $openid  openid
     * @param integer $type    平台类型
     * @param integer $phone   手机号码
     * @param string  $password 密码
     * @param integer $location 城市代码
     * @param string  $nick     昵称
     * @param string  $avatar   性别
     * @param integer $sex      头像
     * @param string  $auth     微信用户信息
     */
    public static function addAuthUser($openid, $type = self::TYPE_WEIXIN, $phone, $password = '', $location, $nick, $avatar, $sex, $auth = array())
    {
        $u = self::addNewUser('', $password, $nick, $phone, $location, '', $avatar, $sex, $auth);
        if ($u) {
            $uid        = $u->uid;
            
            $o = new self();
            $o->uid         = $uid;
            $o->openid      = $openid;
            $o->type        = $type;
            $o->status      = self::STATUS_NORMAL;

            $o->save_and_return($o, true);
            return $u;
        } else {
            return false;
        }
    }
    
    public static function updateAuthUser($user, $openid, $type = self::TYPE_WEIXIN, $phone, $password = '', $location, $nick, $avatar, $sex, $auth = array())
    {
        $user->phone        = $phone;
        $user->password     = \Psgod\Models\User::hash($password);
        $user->nickname     = $nick;
        $user->avatar       = $avatar;
        $user->location     = $location;
        $user->sex          = $sex;
        $user->save();

        if ($user) {
            $uid            = $user->uid;
            
            $o = new self();
            $o->uid         = $uid;
            $o->openid      = $openid;
            $o->type        = $type;
            $o->status      = self::STATUS_NORMAL;

            $o->save_and_return($o, true);
            return $user;
        } else {
            return false;
        }
    }
    
    /**
     * 根据uid查找用户
     * 
     * @param  integer $uid    用户uid
     * @param  integer $type   平台类型type
     * @return \Psgod\Models\User
     */
    public static function findUserByUid($uid, $type = self::TYPE_WEIXIN)
    {
        $type = self::get_landing_type($type);
        $user_landing = self::findFirst("uid='{$uid}' and type='{$type}' and status = ".self::STATUS_NORMAL);

        return $user_landing;
    }
 
    /**
     * 根据openid查找用户
     * 
     * @param  integer $openid 用户openid
     * @param  integer $type   平台类型type
     * @return \Psgod\Models\User
     */
    public static function findUserByOpenid($openid, $type = self::TYPE_WEIXIN)
    {
        $type = self::get_landing_type($type);
        $user_landing = self::findFirst("openid='{$openid}' and type='{$type}' and status = ".self::STATUS_NORMAL);

        return $user_landing;
    }

    private static function get_landing_type($type){
        if(is_numeric($type)){
            return $type;
        }
        $type_int = self::TYPE_WEIXIN;
        switch($type){
        case 'weixin':
            $type_int = self::TYPE_WEIXIN;
            break;
        case 'weibo':
            $type_int = self::TYPE_WEIBO;
            break;
        case 'qq':
            $type_int = self::TYPE_QQ;
            break;
        default:
            break;
        }
        return $type_int;
    }

}
