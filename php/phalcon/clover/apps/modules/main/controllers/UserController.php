<?php
namespace Psgod\Main\Controllers;

use \Psgod\Models\User,
    \Psgod\Models\UserScore,
    \Psgod\Models\ActionLog,
    \Psgod\Models\Ask,
    \Psgod\Models\Collection,
    \Psgod\Models\Reply,
    \Psgod\Models\Follow,
    \Psgod\Models\Label,
    \Psgod\Models\Count,
    \Psgod\Models\Upload,
    \Psgod\Models\Role,
    \Psgod\Models\Download;

class UserController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->assets->addCss('css/icomoon/style.css'); // 通用公共样式
    }

    public function getUserStatus(){
        $self_uid = $this->_uid;
        $request_user = false;

        $url = explode('/', $_REQUEST['_url']);
        $request_uid = (int)$url[3];

        if( empty($request_uid) || !is_integer($request_uid)){
            $request_uid = $self_uid;
        }
        //没登陆，也没传值
        if( empty($request_uid) ){
            $this->response->redirect('ask/hot');
            return false;
        }

        $is_owner    = 0;     // 是否是当前用户
        $is_fellow   = 0;     //是否关注
        $is_parttime = 0;     //是否兼职账号

        $request_user = User::findUserByUID($request_uid);
        if( !$request_user){
            return false;
        }

        if($request_uid == $self_uid){
            $is_owner     = 1;
        }
        else if( $self_uid ){
            $is_fellow    = $request_user->is_fellow_to($self_uid);
        }

        //获取点赞数
        $totalCount = Count::get_uped_reply_counts_by_uid($request_uid);
        $request_user->total_praise = $totalCount;

        $user_roles = $request_user->get_roles();               //获取用户身份ID

        if ($user_roles){                               // 用户身份ID
            $role_id = array_column($user_roles->toArray(), 'role_id');
            if (in_array(Role::TYPE_PARTTIME, $role_id) && $is_owner){
                $is_parttime = 1;                      // 是否兼职人员
                //获取结算额
                $balance = UserScore::get_balance($request_uid);
                $current_score = $balance[UserScore::STATUS_NORMAL];
                $paid_score    = $balance[UserScore::STATUS_PAID];
                $request_user->current_score = $current_score;
                $request_user->paid_score = $paid_score;
            }else{
                $is_parttime = 0;                      // 是否兼职人员
            }
            $request_user->score = $request_user->get_user_score();   // 总分
        }


        $this->set('is_owner', $is_owner);
        $this->set('is_fellow', $is_fellow);
        $this->set('is_parttime', $is_parttime);

        return $request_user;
    }
    /**
     * 个人中心页面 我的求p
     *
     * @return void
     */
    public function profileAction($id = null)
    {

        $user = $this->getUserStatus();
        $this->tag->prependTitle('我的求P');
        $uid = $id ? intval($id) : $this->_uid;

        if (!$uid){
            exit('<script>alert(\'请登录后再操作\');location.href=\'/ask/hot\'</script>');
        }

        // 没有当前用户默认跳转到最热
        if (!$user) return $this->dispatcher->forward(array('controller' => 'ask', 'action' => 'hot'));

        $user->fans_count   = $user->fans_count();      // 关注
        $user->fellow_count = $user->fellow_count();    // 粉丝数


        $page  = $this->get('page', 'int', 1);
        $width = $this->get('width', 'int', 300);
        $limit = 9;

        $asks = Ask::get_user_ask($uid, $page, $limit);
        $data = array();
        foreach($asks as $ask){
            $temp   = $ask->to_simple_array();
            $id     = $temp['id'];
            $temp['labels'] = Label::find("target_id=$id and type=1")->toArray();

            $image= $ask->upload->resize($width);
            $data[] = array_merge($temp, $image);
        }

        $asks_count = Ask::count(array("uid = {$uid} AND status=".Ask::STATUS_NORMAL));
        $Page = new \Page($asks_count, $limit);
        $show = $Page->show();

        $this->set('user', $user);
        $this->set('userInfo', $user->to_simple_array());
        $this->set('page', $show);
        $this->set('uid', $uid);
        $this->set('asks', $data);
    }

    /**
     * [my_works 我的作品]
     * @return [type] [description]
     */
    public function my_worksAction($id = null){
        $user = $this->getUserStatus();
        $this->tag->prependTitle('我的作品');
        $uid = $id ? intval($id) : $this->_uid;

        if (!$uid){
            exit('<script>alert(\'请登录后再操作\');location.href=\'/ask/hot\'</script>');
        }

        // 没有当前用户默认跳转到最热
        if (!$user) return $this->response->redirect('ask/hot');

        $user->fans_count   = $user->fans_count();      // 关注
        $user->fellow_count = $user->fellow_count();    // 粉丝数
        $page  = $this->get('page', 'int', 1);
        $width = $this->get('width', 'int', 300);
        $limit = 9;


        if ($uid == $this->_uid){     // 判断是否是当前用户来确定是否显示所有状态
            $replies = Reply::replies_page($page, $limit, 'new', array('uid' => $uid))->items;
            $replies_count = Reply::count(array("uid = {$uid}"));
        }else{                // 只显示正常的Reply
            $replies = Reply::replies_page($page, $limit, 'new', array('uid' => $uid, 'status' => Reply::STATUS_NORMAL))->items;
            $replies_count = Reply::count(array("uid = {$uid} and status = " . Reply::STATUS_NORMAL));
        }

        $data = array();
        $userInfo = $user->to_simple_array();

        foreach($replies as $reply){
            $id = $reply->id;
            $labels = Label::find("target_id=$id and type=" . Label::TYPE_REPLY)->toArray();
            $user_scores= $reply->get_user_scores();       // 用户评分
            $image = $reply->upload->resize($width);

            $reply_arr  = $reply->toArray();
            $reply_arr['labels']        = $labels;
            $reply_arr['has_reply']   = Reply::count("ask_id=$id and status = ".Reply::STATUS_READY);
            $reply_arr['user_scores']   = $user_scores;

            $data[] = array_merge($reply_arr, $image, $userInfo);
        }

        $Page = new \Page($replies_count, $limit);
        $show = $Page->show();

        $this->set('user', $user);
        $this->set('userInfo', $userInfo);
        $this->set('page', $show);
        $this->set('replies', $data);
        $this->set('uid', $uid);
    }

    /**
     * [inprogressAction 进行中]
     * @return [type] [description]
     */
    public function inprogressAction($id = null){
        $user = $this->getUserStatus();
        $this->tag->prependTitle('进行中');
        $uid = $id ? intval($id) : $this->_uid;
        $is_owner = $uid == $this->_uid ? 1 : 0;        // 是否是当前用户

        if (!$uid){
            exit('<script>alert(\'请登录后再操作\');location.href=\'/ask/hot\'</script>');
        }


        // 没有当前用户默认跳转到最热
        if (!$user) return $this->response->redirect('ask/hot');

        $user->fans_count   = $user->fans_count();      // 关注
        $user->fellow_count = $user->fellow_count();    // 粉丝数

        $page  = $this->get('page', 'int', 1);
        $width = $this->get('width', 'int', 300);
        $last_updated = $this->get('last_updated', 'int', time());
        $limit = 9;

        $inprogress = Download::get_inprogress($uid, $last_updated, $page, $limit);
        $inprogress = $inprogress->toArray();

        $data = array();
        foreach($inprogress as $row){
            //todo: bug 为什么会有id为空的存在
            if(!isset($row['id']))
                continue;
            $id     = $row['id'];
            $reply_id = $row['target_id'];
            $type   = $row['type'];
            $row['labels']      = Label::find("target_id=$reply_id and type=$type")->toArray();
            $row['has_reply']   = Reply::count("ask_id=$id and status = ".Reply::STATUS_READY);
            $row['image_url']  = get_cloudcdn_url($row['savename']);

            $image = Upload::upload_resize($row['ratio'], $row['scale'], $row['savename'], $width);
            $data[] = array_merge($row, $image);
        }

        $inprogress_count = Download::count(array("uid = {$uid} and status is null"));
        $Page             = new \Page($inprogress_count, $limit);
        $show             = $Page->show();

        $this->set('user'      , $user);
        $this->set('userInfo'  , $user->to_simple_array());
        $this->set('page'      , $show);
        $this->set('worksInfo', $data);
        $this->set('uid'       , $uid);
    }

    /**
     * [my_collectionsAction 我的收藏]
     * @return [type] [description]
     */
    public function my_collectionsAction($id = null){
        $this->getUserStatus();
        $this->tag->prependTitle('我的作品');
        $uid = $id ? intval($id) : $this->_uid;

        if (!$uid){
            exit('<script>alert(\'请登录后再操作\');location.href=\'/ask/hot\'</script>');
        }

        $user = User::findUserByUID($uid);

        // 没有当前用户默认跳转到最热
        if (!$user) return $this->dispatcher->forward(array('controller' => 'ask', 'action' => 'hot'));

        $user->fans_count   = $user->fans_count();      // 关注
        $user->fellow_count = $user->fellow_count();    // 粉丝数
        $page  = $this->get('page', 'int', 1);
        $limit = 9;

        $collections = Collection::get_user_collection($uid, $page, $limit);
        $collection_count = Collection::count(array("uid = {$uid}"));
        $Page = new \Page($collection_count, $limit);
        $show = $Page->show();

        $this->set('user', $user);
        $this->set('page', $show);
        $this->set('collections', $collections);
        $this->set('uid', $uid);
    }

    public function registerAction()
    {
        $username = '';
        $password = '';
        $confirm_password = '';
        $email = '';
        $this->check_form_token();
        if ($this->request->isPost()) {
            $username = $this->post('username', 'string');
            $password = $this->post('password');
            $confirm_password = $this->post('confirm_password');
            $email = $this->post('email', 'email');
            $username = strtolower(trim($username));

            if (!match_username_format($username)) {
                $this->flash->error("{$username} 不是有效的用户名");

            } else if(User::findUserByUsername($username)) {
                $this->flash->error("{$username} 用户名已存在");

            } else if (strlen($password) < 6) {
                $this->flash->error("密码至少8位");

            } else if ($confirm_password != $password) {
                $this->flash->error("确认密码不一致");

            } else if(!match_email_format($email)) {
                $this->flash->error("{$email}邮箱格式错误");

            } else if(User::findUserByEmail($email)) {
                $this->flash->error("{$email} 用户名已存在");

            } else{
                $default_avatar = 'http://7u2spr.com1.z0.glb.clouddn.com/20150326-1451205513ac68292ea.jpg';
                $ret = User::addNewUser($username, $password, $username, '', '0', $email, $default_avatar);
                if($ret) {
                    ActionLog::log(ActionLog::TYPE_REGISTER, array(), $ret);
                    $this->flash->success('注册成功');
                    $this->session->set('uid', $ret->uid);

                    // $this->_user = $ret;
                    return $this->response->redirect( 'user/information' );
                } else {
                    $this->flash->error('注册失败，请重试');
                }
            }

        }
        $this->set("username", $username);
        $this->set("password", $password);
        $this->set("confirm_password", $confirm_password);
        $this->set("email", $email);
    }


    public function informationAction()
    {
        $this->tag->prependTitle('完善页面');
        //if($this->session->get('uid'))
        //$this->dispatcher->forward(array(
        //     'controller' => 'index',
        //     'action' => 'index'
        // ));
        if($this->request->isPost()){
            $nickname   = $this->post('nickname');
            $sex        = $this->post('sex', 'int');
            $upload_id  = $this->post('upload_id', 'int');
            if(is_null($upload_id)){
                return $this->flash->error("请上传图片");
            }
            if(is_null($nickname) || is_null($sex)){
                return $this->flash->error("请输入昵称和性别成功");
            }
            $uid = $this->_uid;
            $user = User::findUserByUID($uid);

            if($upload_id){
                $upload = Upload::findFirst("id=$upload_id");
                $user->avatar = get_cloudcdn_url($upload->savename);
            } else {
                $user->avatar = 'http://7u2spr.com1.z0.glb.clouddn.com/20150326-1451205513ac68292ea.jpg';
            }
            $user->sex = $sex;
            $user->nickname = $nickname;
            $user->save_and_return($user);

            return $this->response->redirect('ask/hot');
        }
    }


    public function loginAction()
    {
        $this->noview();

        $username   = $this->post('username');
        $password   = $this->post('password');

        if (is_null($username) and is_null($password)) {
            return ajax_return(0, '请输入用户名或密码');
        }

        $user = null;
        if( match_username_format($username) )
            $user = User::findUserByUsername($username);
        else if ( match_phone_format($username) )
            $user = User::findUserByPhone($username);
        else if( match_email_format($username) )
            $user = User::findUserByEmail($username);

        if ($user) {
            if (User::verify($password, $user->password)) {
                $this->session->set('uid', $user->uid);
                ActionLog::log(ActionLog::TYPE_LOGIN, array(), $user);
                //\Psgod\Services\AuthSrv::login($user);
                ajax_return(1, '登录成功');
            } else {
                ajax_return(0, '帐号或密码错误');
            }
        } else {
            ajax_return(0, '该用户不存在');
        }
    }

    /**
     * [logoutAction 登出]
     * @return [type] [description]
     */
    public function logoutAction(){
        $this->session->destroy();
        return $this->response->redirect('ask/latest');
    }

    /**
     * [recordAction 登出]
     * @param type 求助or回复
     * @param target 目标id
     * @return [json]
     */
    public function recordAction() {
        $this->noview();
        $type   = $this->get('type');
        $target_id = $this->get('target');
        $uid = $this->_uid;

        $url = '';
        if($type=='ask') {
            $type = Download::TYPE_ASK;
            if($ask = Ask::findFirst($target_id)) {
                $image= $ask->upload->resize();
                $url = $image['image_url'];
            }
        } else if($type=='reply') {
            $type = Download::TYPE_REPLY;
            if($reply = Reply::get_reply_by_id($target_id)) {
                $image= $reply->upload->resize();
                $url = $image['image_url'];
            }
        }
        if($url=='' || $uid=='')
            return ajax_return(0, '访问出错||未登录');

        $ext = substr($url, strrpos($url, '.'));
        //todo: watermark
        //$url = watermark2($url, '来自PSGOD', '宋体', '1000', 'white');
        //echo $uid.":".$type.":".$target_id.":".$url;exit();
        if($d = Download::has_downloaded($type, $uid, $target_id)){
            $d->url = $url; $d->save_and_return($d);
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

    /**
     * [downloadAction 下载原图]
     * @param type 求助or回复
     * @param target 目标id
     * @return [json]
     */
    public function downloadAction(){
        $this->noview();
        $url    = $this->get("url");
        $type   = $this->get("type");
        $target_id = $this->get("target_id");
        $ext = get_ext_from_url($url);

        // todo: 后续将名字替换成label里面的内容
        $filename = 'psgod'.$ext;
        $contents = file_get_contents(url_cut_tail($url));
        // 输入文件标签
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: ".strlen($contents));
        Header("Content-Disposition: attachment; filename=" . $filename);
        // 输出文件内容
        echo $contents;
    }

    public function has_downloadedAction() {
        $this->noview();
        $type = $this->get('type');
        $target = $this->get('target');

        if($type == 'ask')$type = Download::TYPE_ASK;
        else if($type == 'reply')$type = Download::TYPE_REPLY;

        $uid = $this->_uid;

        if(Download::has_downloaded($type, $uid,$target_id))
            ajax_return(1,'okay');
        else
            ajax_return(0,'请先下载原图才能上传作品');

    }

    /**
     * [saveAction 修改个人资料(有传值就修改)]
     * @return [type] [description]
     */
    public function saveAction(){
        $this->noview();
        $uid  = $this->_uid;
        $user = User::findUserByUID($uid);
        if( !$user ){
            return ajax_return(0, 'error', false);
        }
        $old = ActionLog::clone_obj( $user );

        if ($this->request->isAjax()) {
            $nickname   = $this->post('nickname');
            $sex        = $this->post('sex', 'int');
            $upload_id  = $this->post('upload_id', 'int');

            if($upload_id){
                $upload = Upload::findFirst("id=$upload_id");
                $user->avatar = get_cloudcdn_url($upload->savename);
            }

            if ($nickname) $user->nickname = $nickname;

            // 只能为 1 或 0
            if ($sex == User::SEX_MAN || $sex == User::SEX_FEMALE){
                $user->sex = $sex;
            }

            $user->save_and_return($user);
            if( $user ){
                ActionLog::log(ActionLog::TYPE_MODIFY_USER_INFO, $old, $user);
            }

            return ajax_return(1, '修改成功');
        }else{
            // 返回默认值
            $data = array(
                'avatar'   => $user->avatar,
                'nickname' => $user->nickname,
                'sex'      => $user->sex,
            );
            return ajax_return(1, 'ok', array($data));
        }
    }
}
