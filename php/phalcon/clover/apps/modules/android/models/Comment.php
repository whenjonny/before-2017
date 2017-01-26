<?php
namespace Psgod\Android\Models;

use Psgod\Models\Comment as CommentBase;

class Comment extends CommentBase {

    public static function getNewComment($tid, $type, $page=1, $limit=10) {
    	$builder = self::query_builder('c');
    	$builder//->columns('c.id comment_id, c.content, c.create_time, c.update_time, c.up_count, c.down_count, c.inform_count, c.status')
    			->where("c.type = :type: and c.target_id = :tid: and c.status = :status:",
    					array("type" => $type, "tid"=>$tid, "status"=>self::STATUS_NORMAL))
    			->orderBy('c.create_time desc');

    	return self::query_page($builder, $page, $limit);
    }

    public function getCommenterInfo() {
    	if($this->commenter) {
    		return $this->getRelated('commenter',array('columns'=>'avatar, sex, nickname nick'))->toArray();
    	} else {
    		return array();
    	}
    }

    public function getReplytoUserInfo() {
    	$result =false;
    	if($this->reply_to) {
    		$com = self::findFirst($this->reply_to);
    		if($com) {
    			$result =  $com->getCommenterInfo();
    		}
    	}
    	return $result;
    }

    public static function get_comment_by_id($cid){
    	return self::findFirst($cid);
    }
}
