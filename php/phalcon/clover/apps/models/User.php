<?php
namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
class User extends ModelBase
{
    const SEX_MAN = 1;
    const SEX_FEMALE=0;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->hasMany("uid", "Psgod\Models\Usermeta", "uid", array(
            'alias' => 'usermeta'
        ));
        $this->hasMany("uid", "Psgod\Models\Platform", "uid", array(
            'alias' => 'platforms'
        ));
        $this->hasMany("uid", "Psgod\Models\Ask", "uid", array(
            'alias' => 'asks'
        ));
        $this->hasMany("uid", "Psgod\Models\Reply", "uid", array(
            'alias' => 'replies'
        ));
        $this->hasMany("uid", "Psgod\Models\Follow", "uid", array(
            'alias' => 'fans_list'
        ));
        $this->hasMany("uid", "Psgod\Models\Follow", "follow_who", array(
            'alias' => 'fellow_list'
        ));
    }

    public function getSource()
    {
        return 'users';
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
        $u = new self();
        $u->phone         = $phone;
        $u->username      = $username;
        $u->password      = self::hash($password);
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
        //$u->location      = '0';
        $u->is_god        = 0;

        return $u->save_and_return($u, true);
    }

    /**
     * 添加微信用户
     *
     * @param integer $phone   手机号码
     * @param string  $password 密码
     * @param integer $location 城市代码
     * @param string  $nick     昵称
     * @param string  $avatar   头像
     * @param string  $auth     微信用户信息
     */

    public static function addAuthUser($openid, $type, $mobile, $password, $location, $nickname, $avatar, $sex, $auth) {
    //public static function addAuthUser($phone, $password, $location, $nick, $avatar, $sex, $auth) {
        $wx_info = json_decode($auth, true);
        $u = self::addNewUser('', $password, $nick, $phone, $location, '', $avatar, $sex, array());
        if ($u) {
            $uid        = $u->uid;

            $pf = \Psgod\Models\Platform::addWeiXinPF($uid, $wx_info);
            if (!$pf) {
                $pf->getDI()->getLogger()->error("微信数据保存错误：{$auth}");
            }

            return self::findUserByUID($uid);
        } else {
            return false;
        }
    }

    /**
     * 根据UID查找用户
     *
     * @param  integer $uid   用户ID
     * @param  boolean $cache 是否使用缓存
     * @return \Psgod\Models\User
     */
    public static function findUserByUID($uid, $cache=true)
    {
        $user = self::findFirst(array(
            'conditions' => "uid= '$uid'",
        ));
        return $user;

    }

    public static function findUserArrByUID($uid){

        $sql = "SELECT u.uid, u.username, u.phone, u.nickname, u.email, u.avatar, u.is_god, u.ps_score, u.sex, u.login_ip, u.last_login_time, ur.role_id as role_id".
            " FROM users u".
            " LEFT JOIN user_roles ur ON u.uid=ur.uid".
            " WHERE u.uid = $uid";
        $user = new self();

        // Execute the query
        return new Resultset(null, $user, $user->getReadConnection()->query($sql));
    }


    /**
     * 根据openid查找用户
     *
     * @param  integer $openid 用户openid
     * @param  integer $type   平台类型type
     * @return \Psgod\Models\User
     */
    public static function findUserByOpenid($openid, $type = Psgod\Models\UserLanding::TYPE_WEIXIN)
    {
        $user_landing = Psgod\Models\UserLanding::findFirst(array(
            'conditions' => "openid={$openid} and type = {$type}",
        ));

        return $user_landing;
    }

    public static function findUserByUsername($username)
    {
        $user = self::findFirst(array(
            'conditions' => "username= '$username'",
        ));
        return $user;
    }

    public static function findUserByEmail($email)
    {
        $user = self::findFirst(array(
            'conditions' => "email= '$email'",
        ));
        return $user;
    }

    public static function getUserByUIDArray($uid_arr)
    {
        if(count($uid_arr)) {
            $str = "(".implode(",", $uid_arr).")";
            $conditions = "uid in $str";
            return self::find(array($conditions));
        }
        return NULL;
    }

    /**
     * 根据phone查找用户
     *
     * @param  integer $uid   用户ID
     * @return \Psgod\Models\User
     */
    public static function findUserByPhone($phone)
    {
        $user = self::findFirst(array(
            'conditions' => "phone='{$phone}'",
        ));
        return $user;
    }

    /**
     * 根据nickname查找用户
     *
     * @param  string $nickname 用户昵称
     * @return \Psgod\Models\User
     */
    public static function findUserByNickname($nickname)
    {
        $user = self::findFirst(array(
            'conditions' => "nickname='{$nickname}'",
        ));
        return $user;
    }


    public function is_fans_to($uid) {
        $result = Follow::findFirst(array(
            "uid = {$uid} AND follow_who = {$this->uid} AND status = ".Follow::STATUS_NORMAL." "
        ));
        if($result)
            return 1;
        return 0;
    }

    public function is_fellow_to($uid) {
        $result = Follow::findFirst(array(
            "uid = {$this->uid} AND follow_who = {$uid} AND status = ".Follow::STATUS_NORMAL." "
        ));
        if($result)
            return 1;
        return 0;
    }

    public function to_simple_array()
    {
        $data = array(
            'uid'          => $this->uid,
            'nickname'     => $this->nickname,
            'sex'          => $this->sex,
            'avatar'       => $this->avatar,
            'fans_count'   => $this->fans_count(),
            'fellow_count' => $this->fellow_count(),
            'ask_count'    => $this->ask_count(),
            'reply_count'  => $this->reply_count(),
            'uped_count'   => $this->get_uped_count(),
            'current_score'=> isset($this->current_score)?$this->current_score: 0,
            'paid_score'   => isset($this->paid_score)?$this->paid_score: 0,
            'total_praise' => isset($this->total_praise)?$this->total_praise: 0,
            'location'     => $this->location,
            'bg_image'     => $this->bg_image
        );
        //todo: after_fetch
        $data = $this->get_location($data);
        return $data;
    }


    public function to_fans_array()
    {
        return $arr = array(
            'uid'             => $this->uid,
            'nickname'        => $this->nickname,
            'sex'             => $this->sex,
            'avatar_url'      => $this->avatar,
            'fellows_count'   => $this->fellow_count(),
            'fans_count'      => $this->fans_count(),
            'uped_count'      => $this->uped_count,
            'asks_count'      => $this->asks_count,
            'replies_count'   => $this->replies_count
        );
    }

    private function user_landings($uid, &$data) {
        $data['is_bound_weixin']  = 0;
        $data['is_bound_qq']      = 0;
        $data['is_bound_weibo']   = 0;

        $landings = UserLanding::find("uid=$uid and status=".self::STATUS_NORMAL);
        foreach($landings as $landing){
            switch($landing->type){
            case UserLanding::TYPE_WEIXIN:
                $data['is_bound_weixin']  = 1;
                break;
            case UserLanding::TYPE_WEIBO:
                $data['is_bound_weibo']   = 1;
                break;
            case UserLanding::TYPE_QQ:
                $data['is_bound_qq']      = 1;
                break;
            }
        }
        return $data;
    }

    public function format_login_info(){
        $data = array();
        $data['uid']              = $this->uid;
        $data['nickname']         = $this->nickname;
        $data['sex']              = intval($this->sex);
        $data['avatar']           = $this->avatar;//todo:thumb url
        //$data['bg_image']         = $this->bg_image;
        $data['fellow_count']     = $this->fellow_count();
        $data['fans_count']       = $this->fans_count();
        $data['uped_count']       = $this->get_uped_count();
        $data['ask_count']        = $this->ask_count();
        $data['reply_count']      = $this->reply_count();
        $data['inprogress_count'] = $this->inprogress_count();
        $data['collection_count'] = $this->collection_count();
        $data['phone']            = $this->phone;

        $data['is_bound_mobile']  = $this->phone?$this->phone:-1;
        $data['location']         = $this->location;

        $this->user_landings($this->uid, $data);

        $data['status']           = 1; //登录成功
        //todo: after_fetch
        $data = $this->get_location($data);

        return $data;
    }

    private function get_location($data){
        $location = explode('|', $data['location']);
        if(sizeof($location) < 3){
            $data['province'] = 0;
            $data['city']     = 0;
            $data['location'] = $data['location'];
        }
        else {
            $data['province'] = intval($location[0]);
            $data['city']     = intval($location[1]);
            $data['location'] = $location[2];
        }
        return $data;
    }


    /**
     * 粉丝分页
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function fans_page($page=1, $limit=10)
    {
        $builder = self::query_builder();
        $urela = 'Psgod\Models\Follow';
        $builder->join($urela, "ur.follow_who = uid", "ur", 'RIGHT')
            ->where("ur.uid = {$this->uid} AND ur.status = ".Follow::STATUS_NORMAL);
        return self::query_page($builder, $page, $limit);
    }

    /**
     * 同伴分页
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function fellow_page($page=1, $limit=10)
    {
        $builder = self::query_builder();
        $urela = 'Psgod\Models\Follow';
        $builder->join($urela, "ur.uid = uid", "ur", 'RIGHT')
            ->where("ur.follow_who = {$this->uid} AND ur.status = ".Follow::STATUS_NORMAL);
        return self::query_page($builder, $page, $limit);
    }


    /**
     * 粉丝总数
     * @return [type] [description]
     */
    public function fans_count()
    {
        return Follow::count(array("follow_who = {$this->uid} AND status = ".Follow::STATUS_NORMAL));
    }

    /**
     * 同伴总数
     * @return [type] [description]
     */
    public function fellow_count()
    {
        return Follow::count(array("uid = {$this->uid} AND status = ".Follow::STATUS_NORMAL));
    }

    /**
     * 求P总数
     * @return [type] [description]
     */
    public function ask_count()
    {
        return Ask::count(array("uid = {$this->uid} AND status = ".Ask::STATUS_NORMAL));
    }

    /**
     * 回复作品数
     * @return [type] [description]
     */
    public function reply_count()
    {
        return Reply::count(array("uid = {$this->uid} AND status = ".Reply::STATUS_NORMAL));
    }

    /**
     * 获取被举报总数
     * @return [type] [description]
     */
    public function ask_inform_count()
    {
        return Ask::sum(array(
            'column'     =>'inform_count',
            'conditions' =>"uid = {$this->uid}"
        ));
    }
    public function reply_inform_count()
    {
        return Reply::sum(array(
            'column'     =>'inform_count',
            'conditions' =>"uid = {$this->uid}"
        ));
    }
    public function all_inform_count()
    {
        return $this->ask_inform_count() + $this->reply_inform_count();
    }

    /**
     * 收藏总数
     * @return [type] [description]
     */
    public function collection_count()
    {
        return Collection::count(array("uid = {$this->uid} AND status = ".Collection::STATUS_NORMAL));
    }

    /**
     * 关注总数
     * @return [type] [description]
     */
    public function focus_count()
    {
        return Focus::count(array("uid = {$this->uid} AND status = ".Focus::STATUS_NORMAL));
    }

    /**
     * 进行中总数
     * @return [type] [description]
     */
    public function inprogress_count()
    {
        return Download::count(array("uid = {$this->uid}")) - Reply::count(array("uid = {$this->uid}"));
    }

    /**
     * [get_user_score 获取用户分数]
     * @return [type] [description]
     */
    public function get_user_score(){
        // 获取给的分数
        return UserScore::sum(array( "column" => "score", "conditions" => " uid = {$this->uid} and status = " . UserScore::STATUS_PAID));
    }

    /**
     * 静态获取被举报总数
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public static function get_ask_inform_count($uid)
    {
        return Ask::sum(array(
            'column'     =>'inform_count',
            'conditions' =>"uid = {$uid}"
        ));
    }

    public static function get_reply_inform_count($uid)
    {
        return Reply::sum(array(
            'column'     =>'inform_count',
            'conditions' =>"uid = {$uid}"
        ));
    }

    public static function get_all_inform_count($uid)
    {
        return self::get_ask_inform_count($uid) + self::get_reply_inform_count($uid);
    }



    /**
     * 密码加密
     * @param  [type] $password [description]
     * @return [type]           [description]
     */
    public static function hash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 密码验证
     * @param  [type] $password [description]
     * @param  [type] $hash     [description]
     * @return [type]           [description]
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * [get_roles 获取用户身份]
     * @return [type] [description]
     */
    public function get_roles(){
        return UserRole::find(array("uid = {$this->uid}"));
    }

    public static function get_fans_page($uid, $page=1, $limit=15) {
        $status = Follow::STATUS_NORMAL;
        $sql = "SELECT r.id rid, u.uid, u.username, u.nickname, u.sex, u.avatar, u.asks_count, u.replies_count, u.uped_count,
                EXISTS(SELECT 'X' FROM follows r1 WHERE r1.uid = u.uid AND r1.follow_who = {$uid} AND r1.status = {$status}) is_fellow,
                (SELECT COUNT(*) FROM follows r2 WHERE r2.uid = u.uid AND r2.status = {$status}) fans_count,
                (SELECT COUNT(*) FROM follows r3 WHERE r3.follow_who = u.uid AND r3.status = {$status}) fellow_count
                FROM users u JOIN follows r ON r.follow_who = u.uid AND r.uid = {$uid} AND r.status = {$status}
                order by r.create_time desc limit ".($page - 1)*$limit.", ".$limit;
        $user = new self();

        // Execute the query
        return new Resultset(null, $user, $user->getReadConnection()->query($sql));
    }

    public static function get_fellow_page($uid, $page=1, $limit=15) {
        $status = Follow::STATUS_NORMAL;
        $sql = "SELECT r.id rid, u.uid, u.username, u.nickname, u.sex, u.avatar, u.asks_count, u.replies_count, u.uped_count,
                EXISTS(SELECT 'X' FROM follows r1 WHERE r1.follow_who = u.uid AND r1.uid = {$uid} AND r1.status = {$status}) is_fans,
                (SELECT COUNT(*) FROM follows r2 WHERE r2.uid = u.uid AND r2.status = {$status}) fans_count,
                (SELECT COUNT(*) FROM follows r3 WHERE r3.follow_who = u.uid AND r3.status = {$status}) fellow_count
                FROM users u JOIN follows r ON r.uid = u.uid AND r.follow_who = {$uid} AND r.status = {$status}
                order by r.create_time desc limit ".($page - 1)*$limit.", ".$limit;
        $user = new self();

        // Execute the query
        return new Resultset(null, $user, $user->getReadConnection()->query($sql));
    }

    public function get_ask_count($uid)
    {
        return Ask::count(array("uid = {$uid} AND status = ".Ask::STATUS_NORMAL));
    }

    public function get_reply_count($uid)
    {
        return Reply::count(array("uid = {$uid} AND status = ".Reply::STATUS_NORMAL));
    }

    public function get_uped_count( ){
        $uid = $this->uid;
        $uped_count = array();
        $uped_count['ask']     =     Ask::sum(array('column'=>'up_count', 'conditions'=>'uid='.$uid.' AND status='.Ask::STATUS_NORMAL));
        $uped_count['reply']   =   Reply::sum(array('column'=>'up_count', 'conditions'=>'uid='.$uid.' AND status='.Reply::STATUS_NORMAL));
        $uped_count['comment'] = Comment::sum(array('column'=>'up_count', 'conditions'=>'uid='.$uid.' AND status='.Comment::STATUS_NORMAL));
        return array_sum($uped_count);
    }

    public static function get_inprogress_count($uid)
    {
        return Download::count(array("uid = {$uid}")) - Reply::count(array("uid = {$uid}"));
    }

    public function get_focus_count($uid)
    {
        return Focus::count(array("uid = {$uid} AND status = ".Focus::STATUS_NORMAL));
    }

     public function get_fellow_count($uid)
    {
        return Follow::count(array("uid = {$uid} AND status = ".Follow::STATUS_NORMAL));
    }

    public function get_fans_count($uid)
    {
        return Follow::count(array("follow_who = {$uid} AND status = ".Follow::STATUS_NORMAL));
    }

    public function get_download_count($uid){
        return Download::count(array("uid={$uid}"));
    }

    public function get_upload_count($uid){
        return Upload::count(array("uid={$uid}"));
    }

    public function get_comment_count($uid){
        return Comment::count(array("uid={$uid} AND inform_count = 0"));
    }

    public static function set_master($uid){
        $user = User::findFirst($uid);
        if( !$user ){
            return false;
        }
        $user->is_god = (int)!$user->is_god;

        //hacking.. location is required
        User::setup(['notNullValidations' => false]);

        return $user->save_and_return($user, true);
    }

    public static function set_password($uid, $new_pwd) {
        $user = User::findUserByUID($uid);
        $user->password = User::hash($new_pwd);

        return $user->save_and_return($user);
    }

}
