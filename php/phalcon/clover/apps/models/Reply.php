<?php

namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset,
    \Psgod\Models\Record,
    \Psgod\Models\Count,
    \Psgod\Models\Usermeta,
    \Psgod\Models\Label;
use Psgod\Models\Label as LabelBase;

class Reply extends ModelBase
{
    use \Psgod\Traits\CountOpt;
    const TYPE_NORMAL = 1;
    const STATUS_BLOCKED = 4;

    public function afterFetch()
    {
    }

    public function getSource()
    {
        return 'replies';
    }

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->belongsTo("uid", "Psgod\Models\User", "uid", array(
            'alias' => 'replyer',
        ));
        $this->hasOne('upload_id', 'Psgod\Models\Upload', 'id', array(
            'alias' => 'upload'
        ));
    }

    /**
     * 添加新回复
     *
     * @param integer $uid        用户ID
     * @param string  $desc       大神带话
     * @param integer $ask_id     求PSID
     * @param \Psgod\Models\Upload $upload_obj 上传对象
     */
    public static function addNewReply($uid, $desc, $ask_id, $upload_obj, $download_type = null, $download_target_id = null)
    {
        $ask = Ask::findFirst($ask_id);
        if($ask) {
            $reply                  = new self();
            $reply->uid             = $uid;
            $reply->desc            = $desc;
            $reply->ask_id          = $ask_id;
            $reply->upload_id       = $upload_obj->id;
            $reply->is_best         = 0;
            $reply->up_count        = 0;
            $reply->down_count      = 0;
            $reply->inform_count    = 0;
            $reply->useless_count   = 0;
            $reply->share_count     = 0;
            $reply->weixin_share_count= 0;
            $reply->comment_count   = 0;
            $reply->click_count     = 0;
            $reply->del_by          = 0;
            $reply->del_time        = 0;
            $reply->create_time     = time();
            $reply->update_time     = time();
            $reply->status          = self::STATUS_NORMAL;

            // 兼职逻辑
            if(UserRole::check_authentication($uid, Role::TYPE_PARTTIME)){
                $reply->status = self::STATUS_READY;
            }
            else {
                $ask->reply_count++;
                $ask->update_time = time();
                $ask->save_and_return($ask);
            }
            $reply->type            = self::TYPE_NORMAL;
            $reply->ip              = get_client_ip();


            $image_url = get_cloudcdn_url($upload_obj->savename);
            self::modify_download_status($uid, $download_type, $download_target_id, $image_url);

            return $reply->save_and_return($reply, true);
        }
        return false;
    }

    /**
     * 新建一个定时回复
     *
     * @param integer $uid        用户ID
     * @param string  $desc       大神带话
     * @param integer $ask_id     求PSID
     * @param \Psgod\Models\Upload $upload_obj 上传对象
     * @param string  $time       定的时间 Y-m-d H:i:s
     * @param integer $status     状态
     */
    public static function addNewTimingReply($uid, $desc, $ask_id, $upload_obj, $time, $status=self::STATUS_NORMAL)
    {
        $reply = self::addNewReply($uid, $desc, $ask_id, $upload_obj);
        if ($reply) {
            $reply->status  = $status;
            $reply->create_time = $time;
            $reply->update_time = $time;
            Replymeta::writeMeta($reply->id, Replymeta::KEY_TIMING, $time);
            return $reply->save_and_return($reply, true);
        } else {
            return false;
        }
    }

    /**
     *
     * Reply Model's Common Method
     *
     */
    public static function get_reply_by_id($id) {
        return self::findFirst($id);
    }

    public static function replies_page($page=1, $limit=10, $type='new', $keys=array()) {
        if(gettype($keys)=='array') {
            $builder = self::query_builder();
            $conditions = 'TRUE';
            foreach ($keys as $k => $v) {
                if(isset($v)) {
                    switch ($k) {
                        case 'created_before':
                            $conditions .= " AND create_time <= :created_before:";
                            break;
                        case 'created_after':
                            $conditions .= " AND update_time >= :created_after:";
                            break;
                        default:
                            $conditions .= " AND $k = :$k:";
                            break;
                    }
                }
            }
            $conditions .= " AND status != ". self::STATUS_DELETED;
            $builder->where($conditions, $keys);

            if($type == 'new')
                $builder->orderBy('create_time DESC');
            else
                $builder->orderBy('click_count DESC');

            return self::query_page($builder, $page, $limit);
        } else
            return false;
    }

    /**
     * [get_user_scores 获取评分]
     * @return [type] [description]
     */
    public function get_user_scores()
    {
        $result = UserScore::findFirst(array("type = ".UserScore::TYPE_REPLY." AND uid = {$this->uid} AND item_id = {$this->id}"));
        if ($result){
            return $result->toArray();
        }else{
            return array();
        }
    }

    public static function fellow_replies_page($uid, $page=1, $limit=10)
    {
        $builder = self::query_builder();
        $urela = 'Psgod\Models\Follow';
        $builder->join($urela, "ur.fellow = uid", "ur", 'LEFT')
            ->where("ur.fans = {$uid} AND ur.status = ".Follow::STATUS_NORMAL);
        return self::query_page($builder, $page, $limit);
    }

    public function get_comments()
    {
        $conditions = "type = :comment_type:"
                    ." AND target_id = :comment_target_id:"
                    ." AND status = :status:";
        $parameters = array(
            "comment_type"      => Comment::TYPE_REPLY,
            "comment_target_id" => $this->id,
            "status" => $this->STATUS_NORMAL
        );

        return Comment::find(array($conditions, "bind" => $parameters));
    }

    public function get_comments_array() {
        $comments =  $this->get_comments();
        $arr = array();
        foreach($comments as $comment) {
            $arr[] = $comment->to_simple_array();
        }
        return $arr;
    }

    /**
     * [collection_page 我的收藏分页]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public static function collection_page($cond, $page = 1, $limit = 15)
    {
        $builder = self::query_builder('r');
        $coll   = 'Psgod\Models\Collection';
        $reply  = 'Psgod\Models\Reply';

        $uid    = isset($cond['uid'])?$cond['uid']: '1';
        $time   = isset($cond['last_update_time'])?$cond['last_update_time']: time();

        $builder->join($coll, "cl.reply_id = r.id", "cl", 'RIGHT')
                ->where("cl.uid = {$uid} AND cl.status = ".Collection::STATUS_NORMAL);
        return self::query_page($builder, $page, $limit);
    }

    public function to_simple_array() {
        $replyer = $this->replyer;

        return $arr = array(
            "id"                 => $this->id,
            "avatar"             => $replyer->avatar,
            "sex"                => $replyer->sex,
            "uid"                => $replyer->uid,
            "nickname"           => $replyer->nickname,
            "create_time"        => $this->create_time,
            "desc"               => $this->desc,
            "click_count"        => $this->click_count,
            "up_count"           => $this->up_count,
            "down_count"         => $this->down_count,
            "comment_count"      => $this->comment_count,
            "share_count"        => $this->share_count,
            "weixin_share_count" => $this->weixin_share_count,
            "useless_count"      => $this->useless_count,
            "inform_count"       => $this->inform_count,
            "status"             => $this->status,
            "type"               => Label::TYPE_REPLY
        );
    }

    public function toSimpleArray() {

        return array(
            'id'                 => $this->id,
            'uid'                => $this->uid,
            'ask_id'             => $this->ask_id,
            'desc'               => $this->desc,
            'click_count'        => $this->click_count,
            'share_count'        => $this->share_count,
            'weixin_share_count' => $this->weixin_share_count,
            'up_count'           => $this->up_count,
            'comment_count'      => $this->comment_count,
            'inform_count'       => $this->inform_count,
            'create_time'        => $this->create_time,
            'update_time'        => $this->update_time,
            'status'             => $this->status,
            'nickname'           => $this->replyer->nickname,
            'avatar'             => $this->replyer->avatar,
            'sex'                => $this->replyer->sex,
            //'image_url'          => $this->image_url,
            // 'ratio'              => $ratio,
            // 'scale'              => $scale
            //'ratio'              => $this->upload->ratio,
            //'scale'              => $this->upload->scale,
        );
    }

    public function toStandardArray( $uid = 0, $width = 480) {
        $data = $this->toSimpleArray();
        //todo: change to ask id for client side
        $data['id']         = $this->id;
        $data['ask_id']     = $this->ask_id;

        $data['hot_comments'] = $this->getHotCommentRows();
        $data['new_comments'] = $this->getHotCommentRows();
        $data['labels']       = $this->get_labels_array();
        $data['type'] = 2;

        $upload = $this->upload;
        $data['image_width']    = $width;
        $data['image_height']   = ($upload&&$upload->ratio)?intval($width*($upload->ratio)):intval($width*1.333);
        $data['image_url']      = get_cloudcdn_url($upload->savename, $width);

        $data['is_download']    = $this->be_downloaded_by($uid);
        $data['uped']           = Count::has_uped_reply( $this->id, $uid );
        $data['collected']      = Collection::has_collected_reply( $this->id, $uid );

        return $data;
    }

    public function getLabelRows() {
        $builder = LabelBase::query_builder('l');
        return $builder->columns('id, content, x, y, direction')
                       ->where("l.target_id = {$this->id} and ".' l.type = '.LabelBase::TYPE_REPLY.' and l.status = '.LabelBase::STATUS_NORMAL)
                       ->getQuery()
                       ->execute()
                       ->toArray();
    }

    public function getHotCommentRows($limit=5) {
        $builder = Comment::query_builder('c');
        $users   = 'Psgod\Models\User';
        return $builder->join($users, 'c.uid = u.uid', 'u')
                       ->where("c.target_id = {$this->id} and ".' c.type = '.Comment::TYPE_REPLY.' and c.status = '.Comment::STATUS_NORMAL)
                       ->columns('u.uid, u.avatar, u.sex, c.id comment_id, u.nickname, c.content, c.up_count, c.down_count, c.create_time')
                       ->orderBy('c.up_count')
                       ->limit($limit)
                       ->getQuery()
                       ->execute()
                       ->toArray();
    }


    public function get_labels()
    {
        $conditions = "type = :type:"
                    ."AND target_id= :target_id:";
        $parameters = array(
            "type" => Label::TYPE_REPLY,
            "target_id"  => $this->id
        );
        return Label::find(array($conditions, "bind" => $parameters));
    }

    public function get_labels_array()
    {
        $labels =  $this->get_labels();
        $arr = array();
        foreach($labels as $label) {
            $arr[] = $label->to_simple_array();
        }
        return $arr;
    }

    public static function update_status($reply, $status, $data="", $oper_by=0)
    {
        $reply->status = $status;
        $uid        = $reply->uid;
        $reply_id   = $reply->id;

        switch($status){
        case self::STATUS_NORMAL:
            UserScore::update_score($uid, UserScore::TYPE_REPLY, $reply_id, $data, $oper_by);
            Ask::set_reply_count($reply->ask_id);
            break;
        case self::STATUS_READY:
            break;
        case self::STATUS_REJECT:
            $reply->del_by = $oper_by;
            $reply->del_time = time();
            UserScore::update_content($uid, UserScore::TYPE_REPLY, $reply_id, $data, $oper_by);
            break;
        case self::STATUS_BLOCKED:
            break;
        case self::STATUS_DELETED:
            $reply->del_by = $oper_by;
            $reply->del_time = time();
            break;
        }

        return $reply->save_and_return($reply, true);
    }

    /**
     * [get_reply_by_ask_id 获取求p相关作品]
     * @return [type] [description]
     */
    public static function get_reply_by_ask_id($ask_id, $page, $limit){
        $reply = self::query_builder('r');
        $reply->where("r.ask_id = {$ask_id} AND r.status = " . self::STATUS_NORMAL);

        return self::query_page($reply, $page, $limit)->items;
    }

    /**
     * 获取求P相关作品总数
     * @return [type] [description]
     */
    public static function get_reply_by_ask_id_count($ask_id)
    {
        return self::count(array("ask_id = {$ask_id} and status = " . self::STATUS_NORMAL));
    }

    /**
     * [modify_download_status 下载过后修改下载状态]
     * @param  [type] $uid                [用户ID]
     * @param  [type] $download_type      [下载类别{ask 还是 reply}]
     * @param  [type] $download_target_id [实体ID]
     * @param  [type] $image_url          [图片地址]
     * @return [type]                     [description]
     */
    public static function modify_download_status($uid, $download_type, $download_target_id, $image_url){
         // 修改下载状态 (回复ask的)
        if ($download_type == Download::TYPE_ASK){
            $d = Download::findFirst(array("uid = $uid AND type= ".Download::TYPE_ASK." AND target_id = $download_target_id and status = " . Download::STATUS_INITIAL));
            if ($d){
                $d->status = Download::STATUS_REPLIED;
                $d->save_and_return($d);
            }else{
                Download::addNewDownload($uid, Download::TYPE_ASK, $download_target_id, get_cloudcdn_url($image_url), Download::STATUS_INITIAL);
            }
        }else if ($download_type == Download::TYPE_REPLY){        // (回复回复的)
            $d = Download::findFirst(array("uid = $uid AND type= ".Download::TYPE_REPLY." AND target_id = $download_target_id and status = " . Download::STATUS_INITIAL));
            if ($d){
                $d->status = Download::STATUS_REPLIED;
                $d->save_and_return($d);
            }else{
                Download::addNewDownload($uid, Download::TYPE_REPLY, $download_target_id, get_cloudcdn_url($image_url), Download::STATUS_INITIAL);
            }
        }
    }

    public static function user_get_reply_page($uid, $page=1, $limit=15){
        $builder = self::query_builder('r');
        $upload  = 'Psgod\Models\Upload';
        $builder->join($upload, 'up.id = r.upload_id', 'up')
                ->where("r.uid = {$uid} and r.status = ".self::STATUS_NORMAL)
                ->columns(array('r.id', 'r.ask_id',
                    'up.savename', 'up.ratio', 'up.scale'
                ));
        return self::query_page($builder, $page, $limit);
    }

    public static function get_user_reply($uid, $page, $limit){
        $offset = ($page - 1) * $limit ;
        $sql = 'SELECT r.*, u.ratio, u.scale, u.savename, u.pathname
                FROM replies AS r
                LEFT JOIN uploads u ON r.upload_id = u.id
                WHERE r.uid = ' . $uid . ' AND r.status = '.self::STATUS_NORMAL .
                ' ORDER BY r.id DESC'.
                " LIMIT $offset , $limit";

        $reply = new self();
        return new Resultset(null, $reply, $reply->getReadConnection()->query($sql));
    }

    public function be_downloaded_by($uid) {
        return Download::count("uid = {$uid} AND type = "
            .Download::TYPE_REPLY." AND target_id = {$this->id} AND status = "
            .Download::STATUS_INITIAL);
    }

    public static function userReplyCount($uid) {
        return self::count(array("uid = {$uid} AND status = ".self::STATUS_NORMAL));
    }

    public static function updateMsg( $uid, $last_updated ){

        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_REPLY );
        $lasttime = $lasttime?$lasttime[Usermeta::KEY_LAST_READ_REPLY]: 0;

        $builder = Reply::query_builder('r');
        $where = array(
            'r.create_time < '.$last_updated,
            'r.create_time > '.$lasttime,
            'r.status='.Reply::STATUS_NORMAL,
            'a.uid='.$uid
        );

		$ask = 'Psgod\Models\Ask';
        $res = $builder -> where( implode(' AND ',$where) )
                        -> join($ask, 'a.id=r.ask_id', 'a', 'left')
                        -> getQuery()
                        -> execute();
        $replies = self::query_page($builder)->items;
        foreach( $replies as $row){
            Message::newReply(
                $row->uid,
                $uid,
                'uid:'.$row->uid.' huifu le ni de qiuzhu.',
                $row->ask_id
            );
        }

        if(isset($row)){
            Usermeta::refresh_read_notify(
                $uid,
                Usermeta::KEY_LAST_READ_REPLY,
                $row->create_time
            );
        }

        return $replies;
    }

    public static function count_unread_reply( $uid){
        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_REPLY );
        if( $lasttime ){
            $lasttime = $lasttime[Usermeta::KEY_LAST_READ_REPLY];
        }
        else{
            $lasttime = 0;
        }

        $builder = Reply::query_builder('r');
        $where = array(
            'r.create_time>'.$lasttime,
            'r.status='.Reply::STATUS_NORMAL,
            'a.uid='.$uid
        );
        $ask = 'Psgod\Models\Ask';

        $res = $builder -> where( implode(' AND ',$where) )
                        -> join($ask, 'a.id=r.ask_id', 'a', 'left')
                        -> columns('count(r.id) as c')
                        -> getQuery()
                        -> execute();
        return $res['c']->toArray()['c'];
    }

    public static function list_unread_replies( $lasttime, $page = 1, $size = 500 ){

        $reply = new self;
        $sql = 'select a.uid, count(1) as num'.
            ' FROM replies r'.
            ' LEFT JOIN asks a ON r.ask_id = a.id'.
            ' WHERE r.status='.self::STATUS_NORMAL.
            ' AND a.status='.self::STATUS_NORMAL.
            ' AND r.create_time>'.$lasttime.
            ' GROUP BY a.uid';
        return new Resultset(null, $reply, $reply->getReadConnection()->query($sql));
    }

    public static function list_replies($reply_ids){
        if(empty($reply_ids)) return array();

        $reply = new self;
        $sql = 'select uid, count(1) as num'.
            ' FROM replies r'.
            ' WHERE r.status='.self::STATUS_NORMAL.
            ' AND r.id in ( '.implode(',', $reply_ids). ' )'.
            ' GROUP BY r.uid';

        // Execute the query
        return new Resultset(null, $reply, $reply->getReadConnection()->query($sql));
    }

}
