<?php
namespace Psgod\Android\Controllers;

use Psgod\Android\Models\User,
    Psgod\Models\ActionLog,
    Psgod\Models\Collection,
    Psgod\Models\UserLanding,
    Psgod\Models\Usermeta,
    Psgod\Android\Models\Reply,
    Psgod\Android\Models\Ask,
    Psgod\Models\Download,
    Psgod\Models\Follow,
    Psgod\Models\Comment,
    Psgod\Models\Label,
    Psgod\Models\Master,
    Psgod\Models\Invitation,
    Psgod\Models\Device,
    Psgod\Models\UserDevice,
    Psgod\Models\Focus;

class AuthController extends ControllerBase {

    public $_allow = array(
        'weixin',
        'weibo',
        'qq',
        'login'
    );

    public function http_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents() ;
        ob_end_clean();

        return $result;
    }

    public function unbindAction() {
        $type   = $this->post('type', 'string', 'weixin');
        $status = UserLanding::STATUS_DELETED;

        if(!$type) {
            return ajax_return(0, '请选择绑定类型');
        }

        $uid = $this->_uid;
        $user_landing = UserLanding::findUserByUid($uid, $type);

        if($user_landing) {
            $old = ActionLog::clone_obj( $user_landing );
            if($user_landing->status == UserLanding::STATUS_DELETED
                && $status == UserLanding::STATUS_DELETED) {
                return ajax_return(1, '该账号已经解绑', false);
            }

            $user_landing->status = $status;
            $new = $user_landing->save_and_return( $user_landing, true);
            if( $new ){
                ActionLog::log(ActionLog::TYPE_UNBIND_ACCOUNT, $old, $new, $type );
            }
            return ajax_return(1, '解绑成功', true);
        }
        return ajax_return(1, '用户不存在', false);
    }

    public function bindAction() {
        $openid = $this->post('openid', 'string', "2692601623");
        $type   = $this->post('type', 'string', 'weibo');
        $status = UserLanding::STATUS_NORMAL;

        if(!$openid) {
            return ajax_return(0, '请重新进行授权');
        }
        if(!$type) {
            return ajax_return(0, '请选择绑定类型');
        }

        $uid = $this->_uid;
        $user_landing = UserLanding::findUserByUid($uid, $type);

        if($user_landing) {
            if($user_landing->status == UserLanding::STATUS_NORMAL
                && $status == UserLanding::STATUS_NORMAL) {
                return ajax_return(1, '该账号已经绑定', false);
            }

            $user_landing->status = $status;
            $new = $user_landing->save();
        }
        else {
            $user_landing = UserLanding::setUserLanding(
                $uid,
                $openid,
                $type,
                $status
            );
        }
        if( $user_landing ){
            ActionLog::log(ActionLog::TYPE_BIND_ACCOUNT, array(), $user_landing, $type );
        }
        return ajax_return(1, '操作成功', true);
    }

    public function weixinAction(){
        $openid = $this->post('openid', 'string', '');
        $type   = 'weixin';
        $this->debug_log->log(json_encode($_REQUEST));

        if(!$openid) {
            return ajax_return(0, '登录失败');
        }

        $user_landing = UserLanding::findUserByOpenid($openid, $type);
        if(!$user_landing) {
            // 走注册流程
            return ajax_return(1, '未注册', array(
                'is_register'=>0
            ));
        }

        $user = User::findUserByUID($user_landing->uid);
        if(!$user) {
            return ajax_return(0, '账户不存在');
        }
        $data = $user->format_login_info();
        $this->session->set('uid', $user->uid);
        ActionLog::log(ActionLog::TYPE_LOGIN, array(), $user, $type);

        $this->debug_log->log(json_encode($data));

        return ajax_return(1, 'okay', array(
            'user_obj'=>$data,
            'is_register'=>1
        ));
    }


    public function weiboAction(){
        $openid = $this->post('openid', 'string', '2692601623');
        $type   = 'weibo';

        if(!$openid) {
            return ajax_return(0, '登录失败');
        }

        $user_landing = UserLanding::findUserByOpenid($openid, $type);
        if(!$user_landing) {
            // 走注册流程
            return ajax_return(1, '未注册', array(
                'is_register'=>0
            ));
        }

        $user = User::findUserByUID($user_landing->uid);
        if(!$user) {
            return ajax_return(0, '账户不存在');
        }
        $data = $user->format_login_info();
        $this->session->set('uid', $user->uid);
        ActionLog::log(ActionLog::TYPE_LOGIN, array(), $user, $type);

        return ajax_return(1, 'okay', array(
            'user_obj'=>$data,
            'is_register'=>1
        ));
    }

    public function qqAction(){
        $openid = $this->post("openid", 'string');
        $type   = 'qq';

        if(!$openid) {
            return ajax_return(0, '登录失败');
        }

        $user_landing = UserLanding::findUserByOpenid($openid, $type);
        if(!$user_landing) {
            // 走注册流程
            return ajax_return(1, '未注册', array(
                'is_register'=>0
            ));
        }

        $user = User::findUserByUID($user_landing->uid);
        if(!$user) {
            return ajax_return(0, '账户不存在');
        }
        $data = $user->format_login_info();
        $this->session->set('uid', $user->uid);
        ActionLog::log(ActionLog::TYPE_LOGIN, array(), $user, $type);

        return ajax_return(1, 'okay', array(
            'user_obj'=>$data,
            'is_register'=>1
        ));
    }
}
