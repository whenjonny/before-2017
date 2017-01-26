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
    Psgod\Models\Count,
    Psgod\Models\Comment,
    Psgod\Models\Message,
    Psgod\Models\Label,
    Psgod\Models\Master,
    Psgod\Models\SysMsg,
    Psgod\Models\Invitation,
    Psgod\Models\Device,
    Psgod\Models\UserDevice,
    Psgod\Models\Focus;


class UserController extends ControllerBase
{
    public $_allow = array(
        'login',
        'get_mobile_code',
        'save',
        'device_token',
        'check_token',
        'check_mobile',
        'reset_password'
    );

    public function testAction(){
        header("HTTP/1.1 400 test error", TRUE, 400);

        return ajax_return(0, 'error', array(
            'data' => array(
                'debug' => true
            )
        ));
    }

    public function device_tokenAction() {
        $uid    = $this->_uid;
        $token  = $this->post("device_token", 'string', "AttX5mWRx1rPy0Xo6iqBuBpH61p-3qKeoNAfEa-pltxG");
        $mac    = $this->post("device_mac", 'string', '');
        $name   = $this->post("device_name", 'string', 'MI 2S');
        $os     = $this->post("device_os", 'string', '4.4.4');
        $options = $this->post("options", 'string', '');

        $platform = $this->post('platform','int', 0);

        //if( empty($mac) ){
            //return ajax_return(0, '请获取mac地址');
        //}
        //if( empty($platform) ){
            //return ajax_return(0, '客户端类型必填');
        //}
        $deviceInfo = Device::findFirst("token='$token'" );

        if( $deviceInfo ){
            $deviceInfo -> update_time = time();
            $deviceInfo = $deviceInfo -> save_and_return($deviceInfo, false);
        }
        else{
            if( $platform == 0 ){
                $platform = Device::TYPE_ANDROID;
            }
            else if( $platform == 1){
                $platform = Device::TYPE_IOS;
            }
            else{
                $platform = Device::TYPE_UNKNOWN;
            }

            $deviceInfo = Device::newToken( $uid, $mac, $name, $platform, $platform,  $token, $options );
            ActionLog::log(ActionLog::TYPE_NEW_DEVICE, array(), $deviceInfo);
        }

        $device_id = $deviceInfo->id;

        $prevDevice = UserDevice::findFirst('uid='.$uid.' AND device_id='.$device_id);
        if( $prevDevice ){
            $prevDevice->status = UserDevice::STATUS_NORMAL;
            $prevDevice->update_time = time();
            $save = $prevDevice->save();
        }
        else{
            //自己以前用过的，需复制settings
            $prevDevice = UserDevice::findFirst(array("uid=".$uid, "order" => "update_time DESC"));
            //被其他人用过 需设置成删除状态
            $usedDevice = UserDevice::find(array('uid != '.$uid.' AND device_id='.$device_id.' AND status='.UserDevice::STATUS_NORMAL));

            $settings = array();
            if( $prevDevice ){
                $prevDevice->status = UserDevice::STATUS_DELETED;
                $prevDevice->save_and_return($prevDevice, false);
                $settings = json_decode($prevDevice->settings);
            }
            if( $usedDevice ){
                $usedDevice->delete();
            }
            $save = UserDevice::newToken( $uid, $device_id, $settings );
            ActionLog::log(ActionLog::TYPE_USER_CHANGE_DEVICE, array(), $save);
        }

        return ajax_return(1, 'okay');
    }

    public function infoAction(){
        $user = User::findUserByUID($this->_uid);
        $data = $user->format_login_info();

        return ajax_return(1, 'okay', $data);
    }

    public function loginAction(){
        $username   = $this->post('username', 'string');
        $phone      = $this->post('phone', 'string', "13580504992");
        $password   = $this->post('password', 'string', 'wwwwww');

        if ( (is_null($phone) and is_null($username)) or is_null($password) ) {
            return ajax_return(0, '请输入用户名或密码');
        }
        if($phone) {
            $user = User::findUserByPhone($phone);
        }
        else  {
            $user = User::findUserByUsername($username);
        }

        if(!$user) {
            return ajax_return(1, '用户未注册', array(
                'status'=>3
            ));
        }
        // if( !password_verify($password, $user->password) ){//!User::verify($user->password, $password)){
        //     return ajax_return(1, '密码错误', array(
        //         'status'=>2
        //     ));
        // }

        $data = $user->format_login_info();
        $this->session->set('uid', $user->uid);
        ActionLog::log(ActionLog::TYPE_LOGIN, array(), $user);

        return ajax_return(1, 'okay', $data);
    }

    public function saveAction()
    {
        //get platform
        $type     = $this->post('type', 'string', 'weixin');
        //todo: 验证码
        $code     = $this->post('code', 'int');
        //post param
        $mobile   = $this->post('mobile', 'string', "15018749411");
        $password = $this->post('password', 'string', '123123');
        $nickname = $this->post('nickname', 'string', 'nickname');
        $avatar   = $this->post('avatar', 'string', 'http://7u2spr.com1.z0.glb.clouddn.com/20150605-15425755715301a7625.jpg');
        $location = $this->post('location', 'string', '');
        $city     = $this->post('city', 'int', 10);
        $province = $this->post('province', 'int', 32);
        $location = $this->encode_location($province, $city, $location);

        $sex      = $this->post('sex', 'string', '0');
        $openid   = $this->post('openid', 'string');
        $auth     = $this->post('auth', 'string', '');
        $avatar_url = $this->post('avatar_url', 'string', '');


        if(!$mobile) {
            return ajax_return(0, '请输入手机号码');
        }
        if(!$password) {
            return ajax_return(0, '请输入密码');
        }
        $user   = User::findFirst("phone='$mobile'");
        if($user && !$openid){
            return ajax_return(0, '手机已注册');
        }

        switch($type){
        case 'mobile':
            if(!$avatar) {
                return ajax_return(0, '请上传头像');
            }
            $username = '';
            $email    = '';
            $options  = [];

            $user = User::addNewUser($username, $password, $nickname, $mobile, $location, $email, $avatar, $sex, $options);
            if($user) {
                $data = $user->format_login_info();
                $this->session->set('uid', $user->uid);
                ActionLog::log(ActionLog::TYPE_REGISTER, array(), $user, $type);
                return ajax_return(1, '手机注册成功！', $data);
            } else{
                return ajax_return(0, '手机注册失败！');
            }
        case 'weixin':
            if(!$avatar_url && !$avatar) {
                return ajax_return(0, '请上传头像');
            }
            if(!$openid) {
                return ajax_return(0, '请重新微信授权！');
            }

            //todo check mobile code
            if(UserLanding::findUserByOpenid($openid, UserLanding::TYPE_WEIXIN)){
                return ajax_return(0, '微信注册失败！用户已存在');
            }
            if($user){
                $user = UserLanding::updateAuthUser(
                    $user,
                    $openid,
                    UserLanding::TYPE_WEIXIN,
                    $mobile,
                    $password,
                    $location,
                    $nickname,
                    $avatar_url,
                    $sex
                );

            }
            else {
                $user = UserLanding::addAuthUser(
                    $openid,
                    UserLanding::TYPE_WEIXIN,
                    $mobile,
                    $password,
                    $location,
                    $nickname,
                    $avatar_url,
                    $sex
                );
            }

            if($user) {
                $data = $user->format_login_info();
                $this->session->set('uid', $user->uid);
                ActionLog::log(ActionLog::TYPE_REGISTER, array(), $user, $type);
                return ajax_return(1, '微信注册成功！', $data);
            }
            return ajax_return(0, '微信注册失败！');
        case 'weibo':
            if(!$avatar_url && !$avatar) {
                return ajax_return(0, '请上传头像');
            }
            if(!$openid) {
                return ajax_return(0, '请重新微信授权！');
            }

            if(UserLanding::findUserByOpenid($openid, UserLanding::TYPE_WEIBO)){
                return ajax_return(0, '微博注册失败！用户已存在');
            }

            if($user){
                $user = UserLanding::updateAuthUser(
                    $user,
                    $openid,
                    UserLanding::TYPE_WEIXIN,
                    $mobile,
                    $password,
                    $location,
                    $nickname,
                    $avatar_url,
                    $sex
                );

            }
            else {
                $user = UserLanding::addAuthUser(
                    $openid,
                    UserLanding::TYPE_WEIBO,
                    $mobile,
                    $password,
                    $location,
                    $nickname,
                    $avatar_url,
                    $sex
                );
            }

            if($user) {
                $data = $user->format_login_info();
                $this->session->set('uid', $user->uid);
                ActionLog::log(ActionLog::TYPE_REGISTER, array(), $user, $type);
                return ajax_return(1, '微博注册成功！', $data);
            }
            return ajax_return(0, '微博注册失败！');
            break;
        default:
            return ajax_return(0, '注册类型出错！');
        }
    }

    public function count_unread_noticesAction( $type = '' ){
        $uid = $this->_uid;
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 15);

        $unread= array();
        $unread['comment'] = Comment::count_unread( $uid );
        $unread['follow'] = Follow::count_new_followers( $uid );
        $unread['invite'] =  Invitation::count_new_invitation( $uid );
        $unread['reply'] = Reply::count_unread_reply( $uid );
        $unread['system'] = SysMsg::count_unread_sysmsgs( $uid );

        return ajax_return(1, 'okay', $unread);
    }

    public function check_mobileAction() {
        $phone = $this->get('phone', 'string', '');
        if (!match_phone_format($phone)) {
            return ajax_return(0, '请输入正确的手机号码');
        }
        if ( User::findUserByPhone($phone) )  {
            return ajax_return(1, '该号码已经注册，请使用其它号码注册', array(
                'is_register' => 1
            ));
        }

        return ajax_return(1, 'ok', array(
            'is_register' => 0
        ));
    }

    public function get_mobile_codeAction() {
        $phone = $this->get('phone', 'string', '');
        if (match_phone_format($phone)) {
            //$active_code = mt_rand(100000, 9999999);    // 六位手机验证码
            $active_code  = '123456';

            /*
            $Msg = new \Msg();
            $send = $Msg -> phone( $phone )
                         -> content( str_replace('::code::', $active_code, VERIFY_MSG) )
                         -> send();

            if(!$send) {
                return ajax_return( 0, '验证码发送失败' );
            }
            */
           $this->session->set('code',$active_code);

            return ajax_return(1, 'okay', array(
                'code'=>$active_code
            ));
        } else {
            return ajax_return(1, '输入的手机号码不符合要求，请确认后重输');
        }
    }

    /**
     * [editAction 修改个人资料]
     * @return [type] [description]
     */
    public function editAction(){
        $uid = $this->_uid;

        $nickname = $this->post('nickname','string');
        $avatar   = $this->post('avatar','string');
        $sex      = $this->post('sex','int');
        $location = $this->post('location','string');
        $city     = $this->post('city','string');
        $province = $this->post('province','string');

        $user = User::findUserByUID($uid);
        if( !$user ){
            return ajax_return(1,'user doesn\'t exists',false);
        }
        $old = ActionLog::clone_obj( $user );

        if($nickname) {
            if(User::findUserByNickname($nickname)) {
                $data = array('result' => 2);
                return ajax_return(1, 'nickname be used', $data);
            }
            $user->nickname = $nickname;
        }

        if($avatar) {
            $user->avatar = $avatar;
        }

        if( $sex==='0' || $sex==='1' ) {
            $user->sex = $sex;
        }

        if($location || $city || $province) {
            $location = $this->encode_location($province, $city, $location);
            $user->location = $location;
        }

        $user->update_time = time();

        // 保存数据
        if ($user->save_and_return($user)) {
            $data = array('result' => 1);
            ActionLog::log(ActionLog::TYPE_MODIFY_USER_INFO, $old, $user);
            return ajax_return(1, 'ok', $data);
        }else{
            $data = array('result' => 0);
            return ajax_return(0, 'error', $data);
        }
    }

    private function encode_location($province, $city, $location){
        return $location = $province.'|'.$city.'|'.$location;
    }

    /**
     * [collecAction 收藏/取消收藏 回复]
     */
    public function collectAction(){
        $rid    = $this->post('rid', 'int');             // 回复ID
        $status = $this->post('status', 'int');       // 收藏或取消收藏 1收藏 0 取消收藏
        $uid    = $this->_uid;

        if (empty($rid) || empty($status)) {
            return ajax_return(1, '非法操作', array('result' => 0));
        }

        $result = Collection::collection($uid, $rid, $status);

        if ($result){
            return ajax_return(1, 'okay', array('result' => 1));
        }else{
            return ajax_return(0, 'error', array('result' => 0));
        }
    }

    /**
     * [focusAction 关注/取消关注 问题]
     */
    public function focusAction(){
        $aid    = $this->post('aid', 'int');          // 提问id
        $status = $this->post('status', 'int');       // 关注或取消关注 1 关注 0 取消关注
        $uid    = $this->_uid;

        if (empty($aid) || empty($status)) {
            return ajax_return(0, '非法操作', array('result' => 0));
        }

        $result = Focus::focus($uid, $aid, $status);

        if ($result){
            return ajax_return(1, 'okay', array('result' => 1));
        }else{
            return ajax_return(0, 'error', array('result' => 0));
        }
    }

    /**
     * 我的作品Reply
     */
    public function my_replyAction() {
        $uid            = $this->_uid;
        $page           = $this->get("page", "int", 1);
        $size           = $this->get("size", "int", 15);
        $width          = $this->get("width", "int", 480);
        $last_updated   = $this->get("last_updated", "int", time());

        //我的作品 Reply
        $reply_items    = Reply::userReplyList($uid, $last_updated, $page, $size);
        $data           = array();
        foreach ($reply_items as $reply) {
            $data[] = $reply->toStandardArray($uid, $width);
        }

        return ajax_return(1, "okay", $data);
    }

    /**
     * 我的求P
     */
    public function my_askAction() {
        $uid            = $this->_uid;
        $page           = $this->get("page", "int", 1);
        $size           = $this->get("size", "int", 15);
        $width          = $this->get("width", "int", 480);
        $last_updated   = $this->get("last_updated", "int", time());

        //我的求P
        $ask_items      = Ask::userAskList($uid, $last_updated, $page, $size);
        $data = array();
        foreach ($ask_items as $ask) {
            $data[]  = $ask->toStandardArray($uid, $width);
        }

        return ajax_return(1, "okay", $data);
    }

    /**
     * [my_collectionAction 我的收藏]
     * @return [type] [description]
     */
    public function my_collectionAction(){
        $uid          = $this->_uid;

        $page         = $this->get('page', 'int', 1);       // 页码
        $size         = $this->get('size', 'int', 15);   // 每页显示数量
        $width        = $this->get('width', 'int', 480);
        $last_updated = $this->post('last_updated', 'int', time());

        // 我的收藏
        $reply_items  = Reply::collectionList($uid, $page, $size);
        $data = array();
        foreach ($reply_items as $reply) {
            $data[] = $reply->toStandardArray($uid, $width);
        }
        return ajax_return(1, "okay", $data);
    }

    /**
     * [my_focusAction 我的关注]
     * @return [type] [description]
     */
    public function my_focusAction(){
        $uid = $this->_uid;

        $page  = $this->get('page', 'int', 1);           // 页码
        $size  = $this->get('size', 'int', 15);       // 每页显示数量
        $width = $this->get('width', 'int', 480);     // 屏幕宽度
        $last_updated = $this->get('last_updated', 'int', time());

        // 我的关注
        $ask_items    = Ask::focusList($uid, $page, $size);
        $data = array();
        foreach ($ask_items as $ask) {
            $data[] = $ask->toStandardArray($uid, $width);
        }

        return ajax_return(1, "okay", $data);
    }

    /**
     * [my_focusAction 我的关注]
     * @return [type] [description]
     */
    public function my_collectionfocusAction(){
        $uid = $this->_uid;

        $page  = $this->get('page', 'int', 1);           // 页码
        $size  = $this->get('size', 'int', 15);       // 每页显示数量
        $width = $this->get('width', 'int', 480);     // 屏幕宽度
        $last_updated = $this->get('last_updated', 'int', time());

        $items = User::getCollectionFocus($this->_uid, $last_updated, $page, $width);
        $data  = array();
        foreach($items as $item) {
            if($item['type'] == Label::TYPE_ASK)
                $model = new Ask();
            else
                $model = new Reply();

            foreach($item as $key=>$val){
                $model->$key = $val;
            }
            $data[] = $model->toStandardArray($uid, $width);
        }

        return ajax_return(1, "okay", $data);
    }

    /**
     * 获取我的粉丝列表
     * @return [type] [description]
     */
    public function myFansAction(){
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 15);

        $data = array();
        $data = User::myFansList($this->_uid, $page, $size);
        return ajax_return(1, 'okay', $data);
    }

    /**
     * 获取我的fellow列表
     * @return [type] [description]
     */
    public function myFellowAction(){
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 15);
        $uid  = $this->get('uid', 'int', $this->_uid);
        $ask_id  = $this->get('ask_id', 'int', '0');

        $data = array();
        $recommends = array();
        //$data = User::myFellowList($this->_uid, $page, $size);
        $data = User::myFellowList($uid,$page,$size);
        foreach( $data as $key => $fellow){
                $data[$key]['is_fellow']  = Follow::is_follower_of($uid, $fellow['uid'])? 1: 0;
                $data[$key]['is_fans']    = Follow::is_follower_of($fellow['uid'], $uid)? 1: 0;
                $data[$key]['has_invited']  = Invitation::getInvitation( $ask_id, $fellow['uid'])? true : false;
                $data[$key]['fellow_count'] = Follow::userFellowCount($fellow['uid']);
                $data[$key]['fans_count']   = Follow::userFansCount($fellow['uid']);
        }

        $recommends = User::recommendFellows($this->_uid);
        return ajax_return(1, 'okay', array(
            'recommends'=>$recommends,
            'fellows'=>$data
        ));
    }

    /**
     * 检查token是否有效
     */
    public function check_tokenAction()
    {
        $token = $this->post('token','string');
        if(!$token || $token == '') {
            return ajax_return(0, 'err');
        }

        if($this->check_token($token)) {
            return ajax_return(1,'okay');
        }
        return ajax_return(0, 'err');
    }

    public function othersAction() {
        $uid  = $this->get('uid',  'int');
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 15);
        $width= $this->get('width', 'int', 480);
        $type = $this->get('type', 'int', 0);
        $last_updated = $this->get('last_updated', 'int', time());
        if( !$uid ){
            return ajax_return(0,'请选择用户');
        }
        $user = User::findFirst($uid);
        if(!$user) {
            return ajax_return(0,'请选择用户');
        }

        $data = array();
        $data = $user->to_simple_array();
        $data['is_fans'] = $user->is_fans_to($this->_uid);
        $data['is_fellow'] = $user->is_fellow_to($this->_uid);

        $data['asks'] = array();
        if($page == 1  || $type == Label::TYPE_ASK) {
            $asks = Ask::userAskList($uid, $last_updated, $page, $size);
            foreach ($asks as $ask) {
                $data['asks'][] = $ask->toStandardArray($uid, $width);
            }
        }
        $data['replies'] = array();
        if($page == 1 || $type == Label::TYPE_REPLY) {
            $replies = Reply::userReplyList($uid, $last_updated, $page, $size);
            foreach ($replies as $reply) {
                $data['replies'][] = $reply->toStandardArray($uid, $width);
            }
        }
        return ajax_return(1, 'okay', $data);
    }

    public function othersFansAction(){
        $uid  = $this->get('uid',  'int', 3);
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 15);

        $data = array();
        $data = User::othersFansList($uid, $this->_uid, $page, $size);

        return ajax_return(1, 'okay', $data);
    }

    public function othersFellowAction(){
        $uid  = $this->get('uid',  'int', 3);
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 15);

        $data = array();
        $data = User::othersFellowList($uid, $this->_uid, $page, $size);
        return ajax_return(1, 'okay', $data);
    }

    public function my_proceedingAction() {

        $uid = $this->_uid;
        $page = $this->get('page','int',1);
        $size = $this->get('size','int',10);
        $width = $this->get('width', 'int', '480');
        $last_updated = $this->get('last_updated', 'int', time());

        $items = Download::get_progressing($uid, $last_updated, $page, $size)->items;
        $data = array();
        foreach ($items as $item) {
            if($item->type == Download::TYPE_ASK) {
                $ask = Ask::findFirst($item->target_id);
                $data[] = $ask->toStandardArray($uid, $width);
            } else {
                $reply = Reply::findFirst($item->target_id);
                $data[] = $reply->toStandardArray($uid, $width);
            }
        }

        return ajax_return(1, 'okay', $data);
    }

    public function followAction() {
        $uid = $this->post('uid');
        if(!$uid)
            return ajax_return(0, '请选择关注的账号');

        $me  = $this->_uid;

        $ret = Follow::setUserRelation($uid, $me, Follow::STATUS_NORMAL);
        if($ret){
            if( $ret instanceof Follow ){
                ActionLog::log(ActionLog::TYPE_FOLLOW_USER, array(), $ret);
            }
            return ajax_return(1, 'okay');
        }
        else
            return ajax_return(0, 'error');
    }

    public function unfollowAction() {
        $uid = $this->post('uid');
        $me  = $this->_uid;
        $ret = Follow::setUserRelation($uid, $me, Follow::STATUS_DELETED);

        if($ret){
            if( $ret instanceof Follow ){
                ActionLog::log(ActionLog::TYPE_UNFOLLOW_USER, array(), $ret);
            }
            return ajax_return(1, 'okay');
        }
        else
            return ajax_return(0, 'error');
    }

    public function fellowsDynamicAction() {
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 15);
        $width= $this->get("width", "int", 480);
        $last_updated = $this->get('last_updated', 'int', time());
        $uid  = $this->_uid;

        $data = array();
        $items = User::getFellowsDynamicID($uid, $page, $size);

        foreach ($items as $item) {
            switch ($item['type']) {
                case Label::TYPE_ASK:
                    $ask = Ask::findFirst($item['id']);
                    if($ask) {
                        $data[] = $ask->toStandardArray($uid, $width);
                    }
                    break;
                case Label::TYPE_REPLY:
                    $reply = Reply::findFirst($item['id']);
                    if($reply) {
                        $data[] = $reply->toStandardArray($uid, $width);
                    }
                    break;
                default:
                    break;
            }
        }

        return ajax_return(1, 'okay', $data);
    }


    public function get_recommend_usersAction(){
        $uid = $this->_uid;
        $ask_id = $this->get('ask_id', 'int',0);
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 15);
        $recom_user = array();
        $masters = Master::get_master_list(1,2);
        foreach( $masters as $key => $master){
                $masters[$key]['is_fans']      = Follow::is_follower_of($uid, $master['uid'])? 1: 0;
                $masters[$key]['is_fellow']    = Follow::is_follower_of($master['uid'], $uid)? 1: 0;
                $masters[$key]['has_invited']  = Invitation::getInvitation( $ask_id, $master['uid']) ? true: false;
                $masters[$key]['fellow_count'] = Follow::userFellowCount($master['uid']);
                $masters[$key]['fans_count']   = Follow::userFansCount($master['uid']);
        }
        $recom_user['recommends'] = $masters;

        $fellows = User::myFellowList($uid,$page,$size);
        foreach( $fellows as $key => $fellow){
                $fellows[$key]['is_fans']      = Follow::is_follower_of($uid, $fellow['uid'])? 1: 0;
                $fellows[$key]['is_fellow']    = Follow::is_follower_of($fellow['uid'], $uid)? 1: 0;
                $fellows[$key]['has_invited']  = Invitation::getInvitation( $ask_id, $fellow['uid'])? true : false;
                $fellows[$key]['fellow_count'] = Follow::userFellowCount($fellow['uid']);
                $fellows[$key]['fans_count']   = Follow::userFansCount($fellow['uid']);
        }
        $recom_user['fellows'] = $fellows;
        return ajax_return(1,'okay', $recom_user);
    }

    public function get_mastersAction(){
        $uid = $this->_uid;
        $ask_id = $this->get('ask_id', 'int',0);
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 15);
        $masters = Master::get_master_list($page,$size);
        foreach( $masters as $key => $master){
                $masters[$key]['is_fans']      = Follow::is_follower_of($uid, $master['uid'])? 1: 0;
                $masters[$key]['is_fellow']    = Follow::is_follower_of($master['uid'], $uid)? 1: 0;
                $masters[$key]['has_invited']  = Invitation::getInvitation( $ask_id, $uid)? true : false;
                $masters[$key]['fellow_count'] = Follow::userFellowCount($master['uid']);
                $masters[$key]['fans_count']   = Follow::userFansCount($master['uid']);
        }

        return ajax_return(1,'okay', $masters);
    }

    //通过手机修改密码
    public function reset_passwordAction(){
        //todo 验证验证码
        $phone    = $this->post('phone', 'int');
        $code    = $this->post('code', 'int');
        $new_pwd = $this->post('new_pwd', 'int');
        if(!$code) {
            return ajax_return(1,'短信验证码为空', false);
        }
        if(!$new_pwd) {
            return ajax_return(1,'密码不能为空', false);
        }
        if(!$phone) {
            return ajax_return(1,'手机号不能为空', false);
        }
        $user = User::findUserByPhone($phone);
        $old = ActionLog::clone_obj($user);
        if( !$user ){
            return ajax_return(1,'用户不存在', false);
        }

        //todo: 验证码有效期
        if( $code != $this->session->get('code') ){
            return ajax_return(1, '验证码不正确', false);
        }

        $reset = User::set_password( $user->uid, $new_pwd );
        if( $reset instanceof User ){
            ActionLog::log(ActionLog::TYPE_RESET_PASSWORD, $old, $reset);
        }
        return ajax_return(1, 'ok', array('status'=>(bool)$reset));
    }

    //通过原密码修改密码
    public function chg_passwordAction(){
        $old_pwd = $this->post('old_pwd', 'string', 'wwwwww');
        $new_pwd = $this->post('new_pwd', 'string', '123123');
        $uid = $this->_uid;

        if( $old_pwd == $new_pwd ) {
            return ajax_return(1, '新密码不能与原密码相同', false);
        }
        $user = User::findFirst($uid);
        if( !$user ){
            return ajax_return(1,'user not exist', false);
        }

        $old = ActionLog::clone_obj( $user );
        if( !User::verify( $old_pwd, $user->password ) ){
            return ajax_return(1, '原密码校验失败', false);
        }

        $user = User::set_password( $uid, $new_pwd );
        //坑！$user instanceof User 居然是flase！因为$user 是Android\User
        if( $user ){
            ActionLog::log(ActionLog::TYPE_CHANGE_PASSWORD, $old, $user);
            return ajax_return( 1, 'okay', true );
        }
        else{
            return ajax_return( 1, 'error', false );
        }

    }


    /**
     * [recordAction 记录下载]
     * @param type 求助or回复
     * @param target 目标id
     * @return [json]
     */
    public function recordAction() {
        $type       = $this->get('type');
        $target_id  = $this->get('target');
        $width      = $this->get('width', 'int', 480);
        $uid = $this->_uid;

        $url = '';
        if($type=='ask') {
            $type = Download::TYPE_ASK;
            if($ask = Ask::findFirst($target_id)) {
                $image  = $ask->upload->resize($width);
                $url    = $image['image_url'];
            }
        }
        else if($type=='reply') {
            $type = Download::TYPE_REPLY;
            if($reply = Reply::get_reply_by_id($target_id)) {
                $image  = $reply->upload->resize($width);
                $url    = $image['image_url'];
            }
        }
        else{
            return ajax_return(0, '未定义类型。');
        }

        if($url==''){
            return ajax_return(0, '访问出错');
        }

        //$ext = substr($url, strrpos($url, '.'));
        //todo: watermark
        //$url = watermark2($url, '来自PSGOD', '宋体', '1000', 'white');
        //echo $uid.":".$type.":".$target_id.":".$url;exit();

        //$d = Download::has_downloaded($type, $uid, $target_id);
        if($d = Download::has_downloaded($type, $uid, $target_id)){
            $d->url = $url;
            $d->save_and_return($d);
        } else {
            $dl = Download::addNewDownload($uid, $type, $target_id, $url, 0);
            if( $dl instanceof Download ){
                ActionLog::log(ActionLog::TYPE_USER_DOWNLOAD, array(), $dl);
            }
        }

        return ajax_return(1, 'okay', array(
            'type'=>$type,
            'target_id'=>$target_id,
            'url'=>$url
        ));
    }

    public function get_push_settingsAction(){
        $type = $this->get('type','string','');

        $uid = $this->_uid;
        $settings = UserDevice::get_push_stgs( $uid );

        switch( $type ){
            case UserDevice::PUSH_TYPE_COMMENT:
            case UserDevice::PUSH_TYPE_FOLLOW:
            case UserDevice::PUSH_TYPE_INVITE:
            case UserDevice::PUSH_TYPE_REPLY:
                $ret = array($type=>$settings->$type);
                break;
            default:
                $ret = $settings;
        }

        return ajax_return(1,'okay', $ret);
    }

    public function set_push_settingsAction(){
        $this->noview();
        $type = $this->post('type','string');
        $value = $this->post('value','string');

        $uid = $this->_uid;
        if( !in_array($type, array(
            UserDevice::PUSH_TYPE_COMMENT,
            UserDevice::PUSH_TYPE_FOLLOW,
            UserDevice::PUSH_TYPE_INVITE,
            UserDevice::PUSH_TYPE_REPLY,
            UserDevice::PUSH_TYPE_SYSTEM))
        ){
            return ajax_return(1, '设置类型错误', false);
        }
        if( $value!=UserDevice::VALUE_ON && $value!=UserDevice::VALUE_OFF ){
            return ajax_return(1, '设置参数错误', false);
        }

        $settings = UserDevice::get_push_stgs( $uid );
        $old = ActionLog::clone_obj( $settings );
        switch( $type ){
            case UserDevice::PUSH_TYPE_COMMENT:
            case UserDevice::PUSH_TYPE_FOLLOW:
            case UserDevice::PUSH_TYPE_INVITE:
            case UserDevice::PUSH_TYPE_REPLY:
            case UserDevice::PUSH_TYPE_SYSTEM:
                $ret = UserDevice::set_push_stgs( $uid, $type, $value );
                ActionLog::log(ActionLog::TYPE_USER_MODIFY_PUSH_SETTING, $old, $ret);
                break;
            default:
                $ret = false;
        }

        return ajax_return(1,'okay', (bool)$ret);
    }

    public function delete_progressAction() {
        $type = $this->post("type", "int", Label::TYPE_ASK);
        $id   = $this->post("id", "int");

        if(!$id){
            return ajax_return(1, '请选择删除的记录', false);
        }

        $uid = $this->_uid;
        $download = Download::findFirst('uid='.$uid.' AND type='.$type.' AND target_id='.$id.' AND status='.Download::STATUS_INITIAL);
        if(!$download){
            return ajax_return(1, '请选择删除的记录', false);
        }

        if($download->uid != $this->_uid){
            return ajax_return(1, '未下载', false);
        }
        $old = ActionLog::clone_obj( $download );

        $download->status = Download::STATUS_DELETED;
        $new = $download->save_and_return($download);
        if( $new instanceof Download ){
            ActionLog::log(ActionLog::TYPE_DELETE_DOWNLOAD, $old, $new);
        }

        return ajax_return(1, 'okay', true);
    }
}
