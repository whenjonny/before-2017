<?php

namespace Psgod\Mobile\Models;


use Psgod\Models\Message as MessageBase;


class Message extends MessageBase
{

    public static function followMsgList($uid, $last_updated, $page=1, $limit=10) {
        \Psgod\Models\Follow::updateMsg($uid, $last_updated);

		$builder = self::query_builder('m');
		$users   = 'Psgod\Models\User';
        $follows = 'Psgod\Models\Follow';

		return $builder->join($users, 'm.sender = u.uid', 'u', 'LEFT')
            ->where('m.status = '.self::STATUS_NORMAL.
            ' and m.msg_type= '.self::TYPE_FOLLOW.
            ' and m.target_type= '.self::TARGET_USER.
            ' and m.receiver = '.$uid)
            ->columns('m.id, u.uid, u.nickname, u.avatar, u.sex, m.create_time')
            ->orderBy('m.create_time desc')
            ->limit($limit, ($page-1)*$limit)
            ->getQuery()
            ->execute();
    }

	public static function replyMsgList($uid, $last_updated, $page=1, $limit=10) {
        \Psgod\Models\Reply::updateMsg($uid, $last_updated);

		$builder = self::query_builder('m');
		$users   = 'Psgod\Models\User';
		$asks    = 'Psgod\Android\Models\Ask';
		return $builder->join($users, 'm.sender = u.uid', 'u', 'LEFT')
		    ->join($asks, 'm.target_id = ask.id', 'ask', 'LEFT')
            ->where('m.msg_type = '.self::TYPE_REPLY.
            ' and m.target_type = '.self::TARGET_ASK.
            ' and m.status = '.self::STATUS_NORMAL.
            ' and m.receiver = '.$uid)
            ->columns('m.id, u.uid, u.nickname, u.avatar, u.sex,
                m.sender, m.create_time, ask.*')
            ->orderBy('m.create_time desc')
            ->limit($limit, ($page-1)*$limit)
            ->getQuery()
            ->execute();
	}

    public static function inviteMsgList($uid, $last_updated, $page=1, $limit=10) {
        \Psgod\Models\Invitation::updateMsg($uid, $last_updated);

		$builder = self::query_builder('m');
		$users   = 'Psgod\Models\User';
		$invites = 'Psgod\Models\Invitation';
		$asks    = 'Psgod\Android\Models\Ask';
		return $builder->join($users, 'm.sender = u.uid', 'u', 'LEFT')
		    ->join($invites, 'm.target_id = i.id', 'i', 'LEFT')
		    ->join($asks, 'i.ask_id= ask.id', 'ask', 'LEFT')
            ->where('m.msg_type = '.self::TYPE_INVITE.
            ' and m.target_type = '.self::TARGET_USER.
            ' and m.status = '.self::STATUS_NORMAL.
            ' and m.receiver = '.$uid)
            ->columns('m.id, u.uid, u.nickname, u.avatar, u.sex, m.sender, m.create_time, ask.*')
            ->orderBy('m.create_time desc')
            ->limit($limit, ($page-1)*$limit)
            ->getQuery()
            ->execute();
	}


	public static function commentMsgList($uid, $last_updated, $page=1, $limit=10) {
        \Psgod\Models\Comment::updateMsg($uid, $last_updated);

		$builder = self::query_builder('m');
		$users   = 'Psgod\Models\User';
		$comms   = 'Psgod\Models\Comment';
		return $builder->join($users, 'm.sender = u.uid', 'u', 'LEFT')
            ->join($comms, 'c.uid = m.sender and c.status = '.self::STATUS_NORMAL, 'c', 'LEFT')
            ->where('m.msg_type = '.self::TYPE_COMMENT.' and m.status = '.self::STATUS_NORMAL.' and m.receiver = '.$uid)
            ->columns('m.id, u.uid, u.nickname, u.avatar, u.sex, m.sender, c.create_time, c.content, c.target_id, c.type, c.reply_to')
            ->orderBy('m.create_time desc')
            ->limit($limit, ($page-1)*$limit)
            ->getQuery()
            ->execute();
	}

    public static function sysMsgList($uid, $last_updated, $page=1, $limit=10) {
        \Psgod\Models\SysMsg::updateMsg($uid, $last_updated);

        $builder = self::query_builder('m');
        $users   = 'Psgod\Models\User';
        $comms   = 'Psgod\Models\Comment';
        $reply   = 'Psgod\Models\Reply';
        $asks    = 'Psgod\Models\Ask';
        $res =  $builder
            ->where('m.msg_type = '.self::TYPE_SYSTEM.' and m.status = '.self::STATUS_NORMAL.' and m.receiver = '.$uid)
            ->orderBy('m.create_time desc')
            ->limit($limit, ($page-1)*$limit)
            ->getQuery()
            ->execute();

        return $res;
    }
}
