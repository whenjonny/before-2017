<?php

namespace Psgod\Models;

use Phalcon\Mvc\Model\Resultset\Simple as Resultset,
    \Psgod\Models\Reply,
    \Psgod\Models\User,
    \Psgod\Models\Record,
    \Psgod\Models\Label,
    \Psgod\Models\UserRole;


class Ask extends ModelBase
{
    use \Psgod\Traits\CountOpt;

    const TYPE_NORMAL = 1;

    public function afterFetch() {
    }

    public function beforeSave() {
        $this->update_time  = time();
    }

    /**
     * 绑定映射关系
     */
    public function initialize() {
        $this->useDynamicUpdate(true);
        $this->belongsTo('uid', 'Psgod\Models\User', 'uid', array(
            'alias' => 'asker',
        ));
        $this->hasMany('id', 'Psgod\Models\Reply', 'ask_id', array(
            'alias' => 'replies',
        ));
        $this->hasOne('upload_id', 'Psgod\Models\Upload', 'id', array(
            'alias' => 'upload',
        ));
    }

    /**
    * 分页方法
    *
    * @param int 加数
    * @param int 被加数
    * @return integer
    */
    public static function page($keys, $page=1, $limit=10, $type='new')
    {
        if(!is_array($keys)){
        }

        $builder = self::query_builder();
        $conditions = 'TRUE';
        foreach ($keys as $k => $v) {
            if(isset($v)) {
                switch ($k) {
                    case 'created_before':
                        $conditions .= " AND create_time <= $v";
                        break;
                    case 'created_after':
                        $conditions .= " AND create_time >= $v";
                        break;
                    default:
                        $conditions .= " AND $k = :$k:";
                        break;
                }
            }
        }

        $conditions .= " AND status = ".self::STATUS_NORMAL;

        if($type == 'new'){
            $conditions .= " AND reply_count = 0";
            $builder->orderBy('update_time DESC');
        } else if($type == 'hot'){
            $conditions .= " AND reply_count > 0";
            $builder->orderBy('update_time DESC, reply_count DESC');
        }
        $builder->where($conditions, $keys);
        return self::query_page($builder, $page, $limit)->items;
    }



    /// old =========

    /**
     * 添加新求PS
     *
     * @param string $uid        用户ID
     * @param string $desc       求PS详情
     * @param \Psgod\Models\Upload $upload_obj 上传对象
     */
    public static function addNewAsk($uid, $desc, $upload_obj)
    {
        $ask                         = new self();
        $ask->uid                    = $uid;
        $ask->desc                   = $desc;
        $ask->upload_id              = $upload_obj->id;
        $ask->reply_count            = 0;
        $ask->click_count            = 0;
        $ask->share_count            = 0;
        $ask->weixin_share_count     = 0;
        $ask->up_count               = 0;
        $ask->comment_count          = 0;
        $ask->inform_count           = 0;
        $ask->create_time            = time();
        $ask->update_time            = time();
        $ask->del_by                 = 0;
        $ask->del_time               = time();
        $ask->status                 = self::STATUS_NORMAL;
        $ask->type                   = self::TYPE_NORMAL;
        $ask->ip                     = get_client_ip();

        return $ask->save_and_return($ask, true);
    }

    public function getSource()
    {
        return 'asks';
    }

    /**
     * [get_user_ask 获取用户的求P]
     * @param  [type] $uid [description]
     * @return [type]      [description]
     */
    public static function get_user_ask($uid, $page, $limit){
        $offset = ($page - 1) * $limit ;
        $sql = 'SELECT a.*, u.ratio, u.scale, u.savename, u.pathname'.
            ' FROM asks a '.
            ' LEFT JOIN uploads u ON a.upload_id = u.id '.
            ' WHERE a.uid = ' . $uid . ' AND a.status='.Ask::STATUS_NORMAL.
            ' ORDER BY a.id DESC'.
            " LIMIT $offset , $limit";

        $ask = new Ask();
        return new Resultset(null, $ask, $ask->getReadConnection()->query($sql));
    }

    /**
     * 更新ask点击次数
     * @return [boolean]
     */
    public function increase_click_count(){
        $sql = 'UPDATE asks '.
            ' SET click_count = click_count + 1 '.
            ' WHERE id = ' . $this->id;

        $ask = new Ask();

        return $ask->getReadConnection()->query($sql);
    }


    /**
     * Ask的分页
     * @param  [string]    $type  [new,hot, sc]
     * @param  [int]       $page  [页码]
     * @param  [integer]   $limit [单页大小]
     * @param  [array]     $keys  [匹配数组]
     * @return [paginator] $pi    [分页器]
     */
    public static function asks_page($page=1, $limit=10, $type='new', $keys=array())
    {
        if(gettype($keys)=='array') {
            $builder = self::query_builder();
            $conditions = 'TRUE';
            foreach ($keys as $k => $v) {
                if(isset($v)) {
                    switch ($k) {
                        case 'created_before':
                            $conditions .= " AND create_time <= $v";
                            break;
                        case 'created_after':
                            $conditions .= " AND create_time >= $v";
                            break;
                        default:
                            $conditions .= " AND $k = :$k:";
                            break;
                    }
                }
            }

            $conditions .= " AND status = ".self::STATUS_NORMAL;

            if($type == 'new'){
                $conditions .= " AND reply_count = 0";
                $builder->orderBy('update_time DESC');
            } else if($type == 'hot'){
                $conditions .= " AND reply_count > 0";
                $builder->orderBy('update_time DESC, reply_count DESC');
            }
            $builder->where($conditions, $keys);
            return self::query_page($builder, $page, $limit);
        } else
            return false;
    }

    public static function fellow_asks_page($uid, $page=1, $limit=10)
    {
        $builder = self::query_builder();
        $urela = 'Psgod\Models\Follow';
        $builder->join($urela, "ur.fellow = uid", "ur", 'LEFT')
            ->where("ur.fans = {$uid} AND ur.status = ".Follow::STATUS_NORMAL);
        return self::query_page($builder, $page, $limit);
    }

    public static function set_reply_count($ask_id, $count=1){
        $ask = self::findFirst($ask_id);
        if($ask) {
            $ask->reply_count   += $count;
            $ask->update_time       = time();
            $ask->save();
        }
    }

    public function get_replyers_array() {
        $replies =  $this->replies;
        $arr = array();
        foreach($replies as $reply) {
            $temp = $reply->replyer->to_simple_array();
            $temp['reply_id'] = $reply->id;
            $arr[] = $temp;
        }
        return $arr;
    }

    public function get_comments() {
        $conditions = "type = :comment_type:"
                    ."AND target_id = :comment_target_id:";
        $parameters = array(
            "comment_type"      => Comment::TYPE_ASK,
            "comment_target_id" => $this->id
        );
        return Comment::find(array($conditions, "bind" => $parameters));
    }

    /**
     * [get_psgod 获取作品大神头像]
     * @return [type] [description]
     */
    public function get_psgod($num = 5){
        $builder = Reply::query_builder('r');
        $user     = 'Psgod\Models\User';

        $builder->columns(array('u.uid, u.nickname, u.avatar'))
                ->join($user, "r.uid = u.uid", "u", 'LEFT')
                ->where("r.ask_id = {$this->id} and r.status = " . self::STATUS_NORMAL . " and r.type = " . Reply::TYPE_NORMAL)
                ->orderBy('r.id desc');
        return self::query_page($builder, 1, $num)->items;
    }

    public function get_comments_array() {
        $comments =  $this->get_comments();
        $arr = array();
        foreach($comments as $comment) {
            if(!empty($comment->to_simple_array())){
                $arr[] = $comment->to_simple_array();
            }
        }
        return $arr;
    }

    public function to_simple_array() {
        $asker  = $this->asker;
        $upload = $this->upload;

        //todo: what to do if empty
        if(!$asker || !$upload){
            return array();
        }

        return $arr = array(
            "id"                 => $this->id,
            "avatar"             => $asker->avatar,
            "sex"                => $asker->sex,
            "uid"                => $asker->uid,
            "nickname"           => $asker->nickname,
            "upload_id"          => $this->upload_id,
            "create_time"        => $this->create_time,
            "update_time"        => $this->update_time,
            "desc"               => $this->desc,
            "up_count"           => $this->up_count,
            "comment_count"      => $this->comment_count,
            "share_count"        => $this->share_count,
            "weixin_share_count" => $this->weixin_share_count,
            "reply_count"        => $this->reply_count,
            "type"               => Label::TYPE_ASK
        );
    }


    public function toSimpleArray() {

        return array(
            'id'                 => $this->id,
            'uid'                => $this->uid,
            'desc'               => $this->desc,
            'reply_count'        => $this->reply_count,
            'click_count'        => $this->click_count,
            'share_count'        => $this->share_count,
            'weixin_share_count' => $this->weixin_share_count,
            'up_count'           => $this->up_count,
            'comment_count'      => $this->comment_count,
            'inform_count'       => $this->inform_count,
            'create_time'        => $this->create_time,
            'update_time'        => $this->update_time,
            'end_time'           => $this->end_time,
            'status'             => $this->status,
            'nickname'           => $this->asker->nickname,
            'avatar'             => $this->asker->avatar,
            'sex'                => $this->asker->sex,
            //'image_url'          => $this->image_url,
            //'ratio'              => $this->upload->ratio,
            //'scale'              => $this->upload->scale,
        );
    }

    public function toStandardArray( $uid = 0, $width = 480 ) {

        $data = $this->toSimpleArray();

        $data['hot_comments'] = $this->getHotCommentRows();
        $data['new_comments'] = $this->getNewCommentRows();
        //$data['labels'] = $this->getLabelRows();
        //$data['replyer'] = $this->getReplyerRows();
        $data['type']   = 1;
        $data['ask_id'] = $this->id;

        $upload = $this->upload;
        $data['image_width']    = $width;
        $data['image_height']   = ($upload&&$upload->ratio)?intval($width*($upload->ratio)):intval($width*1.333);
        $data['image_url']      = get_cloudcdn_url($upload->savename, $width);

        $data['comments']       = Comment::get_comments(Label::TYPE_ASK, $this->id);
        $data['replyer']        = $this->get_replyers_array();
        $data['labels']         = $this->get_labels_array();
        $data['is_download']    = $this->be_downloaded_by($uid);
        $data['uped']           = Count::has_uped_ask( $this->id, $uid );
        $data['collected']      = Focus::has_focused_ask( $this->id, $uid);

        return $data;
    }


    public function getHotCommentRows($limit=5) {
        $builder = Comment::query_builder('c');
        $users   = 'Psgod\Models\User';
        return $builder->join($users, 'c.uid = u.uid', 'u')
                       ->where("c.target_id = {$this->id} and ".' c.type = '.Comment::TYPE_ASK.' and c.status = '.Comment::STATUS_NORMAL)
                       ->columns('u.uid, u.avatar, u.sex, c.id comment_id, u.nickname, c.content, c.up_count, c.down_count, c.create_time')
                       ->orderBy('c.up_count')
                       ->limit($limit)
                       ->getQuery()
                       ->execute()
                       ->toArray();
    }

    public function be_downloaded_by($uid) {
        return Download::count("uid = {$uid} AND type = "
            .Download::TYPE_ASK." AND target_id = {$this->id} AND status = "
            .Download::STATUS_INITIAL);
    }

    /**
     * [focus_page 我的收藏分页]
     * @param  integer $page  [description]
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public static function focus_page($uid, $page = 1, $limit = 15)
    {
        $builder = self::query_builder();
        $focus   = 'Psgod\Models\Focus';
        $ask     = 'Psgod\Models\Ask';

        $builder->join($focus, "f.ask_id = {$ask}.id", "f", 'RIGHT')
            ->where("f.uid = {$uid} AND f.status = " . Focus::STATUS_NORMAL);
        return self::query_page($builder, $page, $limit);
    }

    public function get_labels()
    {
        $conditions = "type = :type: "
                    ."AND target_id= :target_id:";
        $parameters = array(
            "type"      => Label::TYPE_ASK,
            "target_id" => $this->id
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

    public static function user_get_ask_page($uid, $page=1, $limit=15){
        $builder = self::query_builder('a');
        $upload  = 'Psgod\Models\Upload';
        $builder->join($upload, 'up.id = a.upload_id', 'up')
                ->where("a.uid = {$uid} and a.status = ".self::STATUS_NORMAL)
                ->columns(array('a.id', 'a.reply_count',
                    'up.savename', 'up.ratio', 'up.scale'
                ));
        return self::query_page($builder, $page, $limit);
    }

    public static function update_status($ask, $status, $data="", $oper_by=0)
    {
        $ask->status = $status;
        $uid        = $ask->uid;
        $ask_id     = $ask->id;
        $ask->update_time = time();

        switch($status){
        case self::STATUS_NORMAL:
            break;
        case self::STATUS_READY:
            break;
        case self::STATUS_REJECT:
            $ask->del_by = $oper_by;
            $ask->del_time = time();
            break;
        case self::STATUS_DELETED:
            $ask->del_by = $oper_by;
            $ask->del_time = time();
            break;
        }

        return $ask->save_and_return($ask, true);
    }


    public static function userAskCount($uid) {
        return self::count(array("uid = {$uid} AND status = ".self::STATUS_NORMAL));
    }

    public static function list_asks($ask_ids) {
        if(empty($ask_ids)) return array();

        $ask = new self;
        $sql = 'select uid, count(1) as num'.
            ' FROM asks a'.
            ' WHERE a.status='.self::STATUS_NORMAL.
            ' AND a.id in ( '.implode(',', $ask_ids). ' )'.
            ' GROUP by a.uid';

        // Execute the query
        return new Resultset(null, $ask, $ask->getReadConnection()->query($sql));
    }
}
