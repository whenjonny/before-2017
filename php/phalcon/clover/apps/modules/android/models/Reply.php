<?php
namespace Psgod\Android\Models;

use Psgod\Models\Reply as ReplyBase;
use Psgod\Models\Label as LabelBase;
use Psgod\Models\Count ;
use Psgod\Models\Collection ;

class Reply extends ReplyBase {



	public function getNewCommentRows($page=1, $limit=10) {
		$builder = Comment::query_builder('c');
		$users   = 'Psgod\Models\User';
		return $builder->join($users, 'c.uid = u.uid', 'u')
					   ->where("c.target_id = {$this->id} and ".' c.type = '.Comment::TYPE_REPLY.' and c.status = '.Comment::STATUS_NORMAL)
					   ->columns('u.uid, u.avatar, u.sex, c.id comment_id, u.nickname, c.content, c.up_count, c.down_count, c.create_time')
					   ->orderBy('c.create_time')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute()
					   ->toArray();
	}


	public static function userReplyList($uid, $last_updated, $page=1, $limit=10) {
        $builder = self::query_builder('r');
        $asks    = 'Psgod\Models\Ask';
        return $builder->where('r.status = '.self::STATUS_NORMAL.
            " AND r.uid = ".$uid.
            " AND r.create_time < ".$last_updated)
            //->join($asks, "r.ask_id= r.id", "a", 'left')
            //->columns('id, content, x, y, direction')
            ->orderBy('r.create_time desc')
            ->limit($limit, ($page-1)*$limit)
            ->getQuery()
            ->execute();
	}

	public static function collectionList($uid, $page=1, $limit=10) {
        $builder = self::query_builder('r');
        $coll   = 'Psgod\Models\Collection';

        return $builder->join($coll, "cl.reply_id = r.id", "cl", 'RIGHT')
                	   ->where("cl.uid = {$uid} AND cl.status = ".self::STATUS_NORMAL)
                	   ->orderBy('cl.create_time desc')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute();
    }

    public static function getMyFellowReplyList($uid, $page, $limit=10) {
    	$builder = self::query_builder('r');
    	$follows = 'Psgod\Models\Follow';
		return $builder->where('r.status = '.self::STATUS_NORMAL)
					   ->join($follows, "f.follow_who = r.uid and f.uid = {$uid} and f.status = ".self::STATUS_NORMAL, 'f')
					   ->orderBy('r.create_time desc')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute();
    }


}
