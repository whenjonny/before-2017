<?php
namespace Psgod\Android\Models;

use Psgod\Models\Ask as AskBase;
use Psgod\Models\Label as LabelBase;
use Psgod\Models\Count;
use Psgod\Models\Comment;
use Psgod\Models\Focus;
use Psgod\Models\Collection;

class Ask extends AskBase {

	public function getReplyerRows($limit=4, $orderBy='create_time') {
		$builder = Reply::query_builder('r');
		$users   = 'Psgod\Models\User';
		return $builder->join($users, "u.uid = r.uid and r.ask_id = {$this->id} and r.status = ".Reply::STATUS_NORMAL, 'u')
					   ->columns('u.uid, u.nickname, u.sex, u.avatar')
					   ->orderBy('r.'.$orderBy.' desc')
					   ->limit($limit)
					   ->getQuery()
					   ->execute()
					   ->toArray();
	}

	public function getLabelRows() {
		$builder = LabelBase::query_builder('l');
		return $builder->columns('id, content, x, y, direction')
					   ->where("l.target_id = {$this->id} and ".' l.type = '.LabelBase::TYPE_ASK.' and l.status = '.LabelBase::STATUS_NORMAL)
					   ->getQuery()
					   ->execute()
					   ->toArray();
	}

	public function getNewCommentRows($page=1, $limit=10) {
		$builder = Comment::query_builder('c');
		$users   = 'Psgod\Models\User';
		return $builder->join($users, 'c.uid = u.uid', 'u')
					   ->where("c.target_id = {$this->id} and ".' c.type = '.Comment::TYPE_ASK.' and c.status = '.Comment::STATUS_NORMAL)
					   ->columns('u.uid, u.avatar, u.sex, c.id comment_id, u.nickname, c.content, c.up_count, c.down_count, c.create_time')
					   ->orderBy('c.create_time')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute()
					   ->toArray();
	}


	public static function newAskList($page=1, $limit=10) {
		$builder = self::query_builder('a');
		return $builder->where('a.status = '.self::STATUS_NORMAL)
					   ->orderBy('a.create_time desc')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute();
	}

	public static function userAskList($uid, $last_updated, $page=1, $limit=10) {
		$builder = self::query_builder('a');
        return $builder->where('a.status = '.self::STATUS_NORMAL.
            " and a.uid = ".$uid.
            " and a.create_time < ".$last_updated)
            ->orderBy('a.create_time desc')
            ->limit($limit, ($page-1)*$limit)
            ->getQuery()
            ->execute();
	}

	public static function hotAskList($page=1, $limit=10) {
		$builder = self::query_builder('a');
		return $builder->where('a.status = '.self::STATUS_NORMAL)
					   ->orderBy('a.up_count desc and a.update_time desc')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute();
	}

	public static function focusList($uid, $page=1, $limit=10) {
        $builder = self::query_builder('a');
        $focus   = 'Psgod\Models\Focus';

        return $builder->join($focus, "fc.ask_id = a.id", "fc", 'INNER')
                	   ->where("fc.uid = {$uid} AND fc.status = ".self::STATUS_NORMAL)
                	   ->orderBy('fc.create_time desc')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute();
    }

    public static function  getMyFellowAskList($uid, $page, $limit=10) {
    	$builder = self::query_builder('a');
    	$follows = 'Psgod\Models\Follow';
		return $builder->where('a.status = '.self::STATUS_NORMAL)
					   ->join($follows, "f.follow_who = a.uid and f.uid = {$uid} and f.status = ".self::STATUS_NORMAL, 'f')
					   ->orderBy('a.create_time desc')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute();
    }

}
