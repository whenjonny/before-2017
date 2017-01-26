<?php

namespace Psgod\Models;

use \Psgod\Models\Count;
use \Psgod\Models\Usermeta;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Comment extends ModelBase
{
    use \Psgod\Traits\CountOpt;
    /**
     * 求助的评论
     */
    const TYPE_ASK = 1;

    /**
     * 回复的评论
     */
    const TYPE_REPLY = 2;

    /**
     * 评论的评论
     */
    const TYPE_COMMENT = 4;

    public $parent = array();

    public function getSource()
    {
        return 'comments';
    }

    public function initialize()
    {
        $this->useDynamicUpdate(true);

        $this->belongsTo("uid", "Psgod\Models\User", "uid", array(
            'alias' => 'commenter'
        ));

    }

    public function afterFetch() {
        if(isset($this->for_comment) && $this->for_comment) {
            $temp = self::findFirst($this->for_comment);
            if($temp) {
                $this->parent = $temp->parent;
                //if(count($temp->parent)<2)
                //逆序放入
                array_unshift( $this->parent, $temp->to_tiny_array() );
            }
        }
    }

    /**
     *
     * Comment Model's Common Method
     *
    */
    public function to_simple_array() {
        if(!$this->commenter || !$this->content){
            //todo: what to do if empty
            return array();
        }
        
        return $arr = array(
            'uid'        => $this->commenter->uid,
            'avatar'     => $this->commenter->avatar,
            'sex'        => $this->commenter->sex,
            'reply_to'   => $this->reply_to,
            'for_comment'=> $this->for_comment,
            'comment_id' => $this->id,
            'nickname'   => $this->commenter->nickname,
            'content'    => $this->content,
            'up_count'   => self::format($this->up_count),
            'down_count'   => self::format($this->down_count),
            'inform_count'   => self::format($this->inform_count),
            'create_time'    => $this->create_time,
            'at_comment'     => $this->parent,
            'target_id' => $this->target_id,
            'target_type' => $this->type,
            'uped'      => Count::has_uped_comment( $this->id, _uid() )
        );
    }

    public function to_tiny_array() {
        return array(
            'comment_id' => $this->id,
            'uid'        => $this->commenter->uid,
            'nickname'   => $this->commenter->nickname,
            'content'    => $this->content,
        );
    }

    /**
     * Add new comment
     * @param integer $uid                  用户ID
     * @param string  $content              评论内容
     * @param integer $comment_type         评论类型
     * @param integer $comment_target_id    评论目标ID
     * @param integer $comment_reply_to     评论回复评论目标ID 默认为0
     * @return $new_comment_id              新创建评论ID
     */
    public static function add_comment($uid, $content, $comment_type, $comment_target_id, $comment_reply_to=0, $for_comment = 0) {
        $comment_target = null;
        if($comment_type == self::TYPE_ASK) {
            $comment_target = Ask::findFirst($comment_target_id);
        } else if($comment_type == self::TYPE_REPLY) {
            $comment_target = Reply::findFirst($comment_target_id);
        }

        if($comment_target) {
            $comment = new self();

            $comment->uid            = $uid;
            $comment->content        = $content;
            $comment->create_time    = time();
            $comment->type           = $comment_type;
            $comment->target_id      = $comment_target_id;
            $comment->reply_to       = $comment_reply_to;
            $comment->for_comment    = $for_comment;
            $comment->status         = self::STATUS_NORMAL;
            $comment->up_count       = 0;
            $comment->down_count     = 0;
            $comment->inform_count   = 0;
            $comment->ip             = get_client_ip();

            return $comment->save_and_return($comment, true);
        }
        return false;
    }

    public static function comment_page($type, $target_id, $page=1, $limit=10, $order='new', $keys=array())
    {
        if(gettype($keys)=='array') {
            $builder = self::query_builder();
            $conditions = "true AND ";
            $conditions .= " type = {$type} AND target_id  = {$target_id} ";
            foreach ($keys as $k => $v) {
                if(isset($v)) {
                    switch ($k) {
                        case 'created_before':
                            $conditions .= " AND created <= :created_before:";
                            break;
                        case 'created_after':
                            $conditions .= " AND created >= :created_after:";
                            break;
                        default:
                            $conditions .= " AND $k = :$k:";
                            break;
                    }
                }
            }
            //$builder->where($conditions, $keys);

            if($order == 'new'){
                $builder->orderBy('create_time DESC');
            } else if($order == 'hot'){
                $conditions .= " AND up_count > 10"; // 点赞数大于10的才会显示
                $builder->orderBy('up_count DESC');
            }
            $builder->where($conditions, $keys);

            return self::query_page($builder, $page, $limit);
        } else
            return false;
    }

    public static function get_comments($type, $target_id, $page=1, $size=10) {
        // comment 评论
        $data = array();
        $comments = self::comment_page($type, $target_id, $page, $size, $order='hot')->items;
		$comment_arr = array();
		foreach ($comments as $comment) {
			$temp = $comment->to_simple_array();
			$comment_arr[] = $temp;
        }
        $data['hot_comments'] = $comment_arr;

        $comments = Comment::comment_page($type, $target_id, $page, $size, $order='new')->items;
		$comment_arr = array();
		foreach ($comments as $comment) {
			$temp = $comment->to_simple_array();
			$comment_arr[] = $temp;
        }
        $data['new_comments'] = $comment_arr;

        return $data;
    }

    public static function updateMsg( $uid, $last_updated, $page = 1, $size = 15 ){
        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_COMMENT );
        $lasttime = $lasttime?$lasttime[Usermeta::KEY_LAST_READ_COMMENT]: 0;

        $builder = self::query_builder('c');
        $where = array(
            'c.create_time < '.$last_updated,
            'c.create_time > '.$lasttime,
            'c.status='.Reply::STATUS_NORMAL,
            'c.reply_to='.$uid
        );

        $res = $builder -> where( implode(' AND ',$where) )
            -> getQuery()
            -> execute();
        $comments = self::query_page($builder)->items;

        foreach ($comments as $row) {
            Message::newComment(
                $row->uid,
                $uid,
                'uid:'.$row->uid.' commented u ',
                $row->id
            );
        }

        if(isset($row)){
            Usermeta::refresh_read_notify(
                $uid,
                Usermeta::KEY_LAST_READ_COMMENT,
                $row->create_time
            );
        }

        return $comments;
    }

    public static function count_unread( $uid ){
        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_COMMENT );
        if( $lasttime ){
            $lasttime = $lasttime[Usermeta::KEY_LAST_READ_COMMENT];
        }
        else{
            $lasttime = 0;
        }

        $res = Comment::count(array(
            'create_time>'.$lasttime,
            'status='.Comment::STATUS_NORMAL,
            'reply_to='.$uid
            )
        );

        return $res;
    }

    public static function list_unread_comments( $lasttime, $page = 1, $size = 500 ){

        $builder = Comment::query_builder('c');
        $where = array(
            'c.create_time>'.$lasttime,
            'c.status='.Comment::STATUS_NORMAL,
        );

        $builder -> where( implode(' AND ',$where) )
                 -> getQuery()
                 -> execute();

        $res = self::query_page($builder, $page, $size)->items;

        return $res;
    }

    public static function list_comments($comment_ids){
        if(empty($comment_ids)) return array();

        $comment = new self;
        $sql = 'select uid, count(1) as num'.
            ' FROM comments c'.
            ' WHERE c.status='.self::STATUS_NORMAL.
            ' AND c.id in ( '.implode(',', $comment_ids). ' )'.
            ' GROUP BY c.uid';

        // Execute the query
        return new Resultset(null, $comment, $comment->getReadConnection()->query($sql));
    }
}
