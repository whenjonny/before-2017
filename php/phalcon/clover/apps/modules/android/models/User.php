<?php

namespace Psgod\Android\Models;

use Psgod\Models\User as UserBase;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class User extends UserBase {

    public function toSimpleArray(){

		return array(
			'uid'         => $this->uid,
			'sex'         => $this->sex,
			'avatar'      => $this->avatar,
			'nickname'    => $this->nickname,
			'ask_count'   => $this->asks_count,
			'reply_count' => $this->replies_count,
			'uped_count'  => $this->uped_count,
			'location'    => $this->location,
			'bg_image'    => $this->bg_image,
		);
    }

    public static function myFansList($uid, $page=1, $limit=10) {
  //   	$builder = self::query_builder('u');
  //   	$follows = 'Psgod\Models\Follow';
		// return $builder->join($follows, "f.uid = u.uid and f.follow_who = {$uid} and f.status = ".self::STATUS_NORMAL, 'f')
		// 			   ->join($follows, "fr.uid = {$uid} and fr.follow_who = f.uid and fr.status = ".self::STATUS_NORMAL, 'fr', 'left')
		// 			   ->columns("u.uid, u.nickname, u.avatar, u.sex, if(fr.follow_who, TRUE, FALSE) is_fellow, TRUE is_fans, (SELECT 1) count1 ")
		// 			   ->orderBy('f.create_time desc')
		// 			   ->limit($limit, ($page-1)*$limit)
		// 			   ->getQuery()
		// 			   ->execute();

		if(($offset = ($page-1)*$limit) < 0)
			$offset = 0;
		$sql = "SELECT f.id fid, u.uid, u.nickname, u.avatar, u.sex,
					IF(fr.follow_who, 1, 0) is_fellow,
					1 is_fans,
					(SELECT count(*) FROM follows WHERE follows.uid = u.uid and follows.status = 1) fellow_count,
					(SELECT count(*) FROM follows WHERE follows.follow_who = u.uid and follows.status = 1) fans_count,
					(SELECT count(*) FROM asks WHERE asks.uid = u.uid and asks.status = 1) ask_count,
					(SELECT count(*) FROM replies WHERE replies.uid = u.uid and replies.status = 1) reply_count
		        FROM users AS u
		        INNER JOIN follows AS f  ON f.uid  = u.uid AND f.follow_who  = $uid  AND f.status = 1
		        LEFT  JOIN follows AS fr ON fr.uid = $uid  AND fr.follow_who = f.uid AND fr.status = 1
		        ORDER BY f.create_time DESC
		        LIMIT $offset, $limit";
        $object = new self();
        return (new Resultset(null, $object, $object->getReadConnection()->query($sql)))->toArray();
    }

    public static function myFellowList($uid, $page=1, $limit=10) {
		if(($offset = ($page-1)*$limit) < 0)
			$offset = 0;
		$sql = "SELECT f.id fid, u.uid, u.nickname, u.avatar, u.sex, u.asks_count as ask_count, u.replies_count as reply_count
		        FROM users AS u
		        INNER JOIN follows AS f  ON f.uid  = $uid AND f.follow_who  = u.uid  AND f.status = 1
		        ORDER BY f.create_time DESC
		        LIMIT $offset, $limit";
        $object = new self();
        return (new Resultset(null, $object, $object->getReadConnection()->query($sql)))->toArray();
    }

    public static function othersFansList($uid, $c_uid, $page=1, $limit=10) {
		if(($offset = ($page-1)*$limit) < 0)
			$offset = 0;
		// $sql = "SELECT f.id fid, u.uid, u.nickname, u.avatar, u.sex,
		// 			IF(fr1.follow_who, 1, 0) is_fellow,
		// 			IF(fr2.uid, 1, 0) is_fans,
		// 			(SELECT count(*) FROM follows WHERE follows.uid = u.uid and follows.status = 1) fellow_count,
		// 			(SELECT count(*) FROM follows WHERE follows.follow_who = u.uid and follows.status = 1) fans_count,
		// 			(SELECT count(*) FROM asks WHERE asks.uid = u.uid and asks.status = 1) ask_count,
		// 			(SELECT count(*) FROM replies WHERE replies.uid = u.uid and replies.status = 1) reply_count
		//         FROM users AS u
		//         INNER JOIN follows AS f   ON f.uid  = u.uid AND f.follow_who  = $uid  AND f.status = 1
		//         LEFT  JOIN follows AS fr1 ON fr1.uid = $c_uid AND fr1.follow_who = f.uid AND fr1.status = 1
		//         LEFT  JOIN follows AS fr2 ON fr2.uid = f.uid AND fr2.follow_who = $c_uid AND fr2.status = 1
		//         ORDER BY f.create_time DESC
		//         LIMIT $offset, $limit";
		$sql = "SELECT f.id as fid, u.uid, u.nickname, u.avatar, u.sex,
				  exists(select id from follows as myfans where myfans.uid=$c_uid and myfans.follow_who=f.follow_who and myfans.status=1) as is_followed,
				  exists(select id from follows as myfollowers where myfollowers.follow_who=$c_uid and myfollowers.uid=f.follow_who and myfollowers.status=1) as is_fans
				FROM follows AS f
				JOIN users AS u ON u.uid = f.follow_who
				WHERE
					f.uid = $uid
				AND f.status = 1
				ORDER BY f.create_time DESC
		        LIMIT $offset, $limit";
        $object = new self();
        return (new Resultset(null, $object, $object->getReadConnection()->query($sql)))->toArray();
    }

    public static function othersFellowList($uid, $c_uid, $page=1, $limit=10) {
		if(($offset = ($page-1)*$limit) < 0)
			$offset = 0;
		// $sql = "SELECT f.id fid, u.uid, u.nickname, u.avatar, u.sex,
		// 			IF(fr1.follow_who, 1, 0) is_fellow,
		// 			IF(fr2.uid, 1, 0) is_fans,
		// 			(SELECT count(*) FROM follows WHERE follows.uid = u.uid and follows.status = 1) fellow_count,
		// 			(SELECT count(*) FROM follows WHERE follows.follow_who = u.uid and follows.status = 1) fans_count,
		// 			(SELECT count(*) FROM asks WHERE asks.uid = u.uid and asks.status = 1) ask_count,
		// 			(SELECT count(*) FROM replies WHERE replies.uid = u.uid and replies.status = 1) reply_count
		//         FROM users AS u
		//         INNER JOIN follows AS f  ON f.uid  = $uid AND f.follow_who  = u.uid  AND f.status = 1
		//         LEFT  JOIN follows AS fr1 ON fr1.uid = $c_uid AND fr1.follow_who = f.uid AND fr1.status = 1
		//         LEFT  JOIN follows AS fr2 ON fr2.uid = f.uid AND fr2.follow_who = $c_uid AND fr2.status = 1
		//         ORDER BY f.create_time DESC
		//         LIMIT $offset, $limit";
		$sql = "SELECT f.id as fid, f.uid, u.nickname, u.avatar, u.sex,
				  exists(select id from follows as myfans where myfans.uid=$c_uid and myfans.follow_who=f.uid and myfans.status=1) as is_followed,
				  exists(select id from follows as myfollowers where myfollowers.follow_who=$c_uid and myfollowers.uid=f.uid and myfollowers.status=1) as is_fans
				FROM follows AS f
				JOIN users AS u ON u.uid = f.follow_who
				WHERE
					f.follow_who = $uid
				AND f.status = 1
				ORDER BY f.create_time DESC
		        LIMIT $offset, $limit";
        $object = new self();
        return (new Resultset(null, $object, $object->getReadConnection()->query($sql)))->toArray();
    }

    public static function recommendFellows($uid, $limit=3) {
    	$sql = "SELECT u.uid, u.nickname, u.avatar, u.sex,
					IF(fr.uid, 1, 0) is_fans,
					1 is_fellow,
    				(SELECT count(*) FROM follows WHERE follows.uid = u.uid AND follows.status = 1) fellow_count,
					(SELECT count(*) FROM follows WHERE follows.follow_who = u.uid AND follows.status = 1) fans_count
				FROM users AS u
				INNER JOIN follows AS f  ON f.uid  = $uid AND f.follow_who  = u.uid  AND f.status = 1
		        LEFT  JOIN follows AS fr ON fr.uid = f.follow_who  AND fr.follow_who = $uid AND fr.status = 1
		        WHERE u.uid <> $uid AND NOT EXISTS(SELECT 'x' FROM follows WHERE follows.follow_who = u.uid AND follows.uid = $uid)
				LIMIT $limit";
		$object = new self();
        return (new Resultset(null, $object, $object->getReadConnection()->query($sql)))->toArray();
    }

    public static function getInviteList($uid, $ask_id, $page=1, $limit=10) {
    	if(($offset = ($page-1)*$limit) < 0)
			$offset = 0;

		$sql = "SELECT f.id fid, u.uid, u.nickname, u.avatar, u.sex,
					if(inv.id, 1, 0) has_invited,
					(SELECT count(*) FROM asks WHERE asks.uid = u.uid AND asks.status = 1) ask_count,
					(SELECT count(*) FROM replies WHERE replies.uid = u.uid AND replies.status = 1) reply_count
		        FROM users AS u
		        INNER JOIN follows AS f ON f.uid = $uid AND f.follow_who  = u.uid  AND f.status = 1
		        LEFT  JOIN invitations AS inv ON inv.ask_id = $ask_id AND inv.invite_uid = u.uid AND inv.status = 1
		        ORDER BY f.create_time DESC
		        LIMIT $offset, $limit";
        $object = new self();
        return (new Resultset(null, $object, $object->getReadConnection()->query($sql)))->toArray();
    }

    public static function getMasterRows($ask_id, $row = 2) {
    	$now = time();
    	$sql = "SELECT u.uid, u.nickname, u.avatar, u.sex,
				if(inv.id, 1, 0) has_invited,
				(SELECT count(*) FROM asks WHERE asks.uid = u.uid AND asks.status = 1) ask_count,
				(SELECT count(*) FROM replies WHERE replies.uid = u.uid AND replies.status = 1) reply_count
				FROM users AS u
    			INNER JOIN masters AS m ON m.uid = u.uid AND m.start_time < $now AND m.end_time > $now AND m.status = 1
    			LEFT  JOIN invitations AS inv ON inv.ask_id = $ask_id AND inv.invite_uid = u.uid AND inv.status = 1
    			ORDER BY m.end_time ASC
    			LIMIT $row";
    	$object = new self();
        return (new Resultset(null, $object, $object->getReadConnection()->query($sql)))->toArray();
    }

    public static function getFellowsDynamicID($uid, $page, $limit=10) {
    	if(($offset = ($page-1)*$limit) < 0)
			$offset = 0;

    	$sql = "(SELECT a.id id, a.create_time create_time, 1 type FROM asks a JOIN follows f on f.follow_who=a.uid AND f.uid = $uid AND a.status = ".self::STATUS_NORMAL." AND f.status = ".self::STATUS_NORMAL.")
    			UNION
				(SELECT r.id id, r.create_time create_time, 2 type FROM replies r JOIN follows f on f.follow_who=r.uid AND f.uid = $uid AND r.status = ".self::STATUS_NORMAL." AND f.status = ".self::STATUS_NORMAL.")
				ORDER BY create_time DESC
                LIMIT $offset, $limit";
    	$object = new self();
        return (new Resultset(null, $object, $object->getReadConnection()->query($sql)))->toArray();
    }

    public static function getCollectionFocus($uid, $last_updated, $page, $limit=10) {
    	if(($offset = ($page-1)*$limit) < 0)
            $offset = 0;
        $normal_status = self::STATUS_NORMAL;
        //$fields = "user.avatar, user.sex, user.uid, user.nickname ".
        $fields = "item.uid, item.create_time, item.update_time, item.desc ".
            ",item.up_count, item.comment_count, item.share_count ".
            ",item.click_count, item.inform_count, item.status ".
            ",item.weixin_share_count, item.upload_id";

        //reply_count end_time 为不统一字段
        $sql = "(SELECT $fields, item.reply_count, item.end_time, 1 type, item.id id,
            item.id ask_id
            FROM focuses f
            LEFT JOIN asks item
            ON f.uid=$uid AND f.status = $normal_status AND f.ask_id=item.id
            WHERE item.create_time < $last_updated
            )
            UNION
            (SELECT $fields, 0 end_time, 0 reply_count , 2 type, c.reply_id id,
            item.ask_id ask_id
            FROM collections c
            LEFT JOIN replies item
            ON c.uid = $uid AND c.status = $normal_status AND c.reply_id=item.id
            WHERE item.create_time < $last_updated
            )
            ORDER BY create_time DESC
            LIMIT $offset, $limit";
    	$object = new self();
        return (new Resultset(null, $object, $object->getReadConnection()->query($sql)))->toArray();
    }
}
