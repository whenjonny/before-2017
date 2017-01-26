<?php

namespace Psgod\Models;
use Phalcon\Mvc\Model\Behavior\SoftDelete;

class Message extends ModelBase
{

    const TYPE_COMMENT = 1; // 评论
    const TYPE_REPLY   = 2; // 作品
    const TYPE_FOLLOW  = 3; // 关注
    const TYPE_INVITE  = 4; // 邀请
    const TYPE_SYSTEM  = 5; // 系统

    const TARGET_ASK     = 1;
    const TARGET_REPLY   = 2;
    const TARGET_COMMENT = 3;
    const TARGET_USER    = 4;
    const TARGET_SYSTEM  = 5;

    public function initialize(){
        $this->addBehavior(new SoftDelete(
            array(
                'field' => 'status',
                'value' => Message::STATUS_DELETED
            )
        ));
    }

    /**
     * 获取推送提示语
     */
    public static function getPushMessage($type = null, $target_type = null) {
        switch($type){
        case self::TYPE_COMMENT:
            $text = "收到一条评论消息";
            break;
        case self::TYPE_REPLY:
            $text = "收到一条作品消息";
            break;
        case self::TYPE_FOLLOW:
            $text = "有新的朋友关注你";
            break;
        case self::TYPE_INVITE:
            $text = "有朋友邀请你帮忙P图";
            break;
        case self::TYPE_SYSTEM:
            $text = "收到一条系统消息";
            break;
        //todo: 缺少相同求助被处理的提醒
        default:
            break;
        }

        return $text;
    }

    public static function delMsgs( $uid, $mids ){
        if( !$uid ){
            return false;
        }
        if( !$mids ){
            return false;
        }
        $mids = implode(',',array_filter(explode(',', $mids)));
        if( empty($mids) ){
            return false;
        }

        $msgs = Message::find('receiver='.$uid.' AND id IN('.$mids.')');
        return $msgs->delete();
    }

	protected static function newMsg( $sender, $receiver, $content, $msg_type, $target_type = NULL, $target_id = NULL ){
		if( $sender == $receiver ){
			return false;
		}
		$msg = new self();
		$msg -> sender = $sender;
		$msg -> receiver = $receiver;
		$msg -> content = $content;
		$msg -> msg_type = $msg_type;
		$msg -> status = Message::STATUS_NORMAL;
		$msg -> target_type = $target_type;
		$msg -> target_id = $target_id;
		$msg -> create_time = time();
		$msg -> update_time = time();
		return $msg -> save_and_return($msg, true);
	}

	public static function newReply( $sender, $receiver, $content, $target_id ){
        return Message::newMsg(
            $sender,
            $receiver,
            $content,
            Message::TYPE_REPLY,
            Message::TARGET_ASK,
            $target_id
        );
    }

	public static function newSystemMsg( $sender, $receiver, $content, $target_type = '', $target_id = '' ){
        return Message::newMsg(
            $sender,
            $receiver,
            $content,
            Message::TYPE_SYSTEM,
            $target_type,
            $target_id
        );
    }

	public static function newFollower( $sender, $receiver, $content, $target_id ){
        return Message::newMsg(
            $sender,
            $receiver,
            $content,
            Message::TYPE_FOLLOW,
            Message::TARGET_USER,
            $target_id
        );
    }

	public static function newComment( $sender, $receiver, $content, $target_id ){
        return Message::newMsg(
            $sender,
            $receiver,
            $content,
            Message::TYPE_COMMENT,
            Message::TARGET_COMMENT,
            $target_id
        );
    }

	public static function newInvitation( $sender, $receiver, $content, $target_id ){
        return Message::newMsg(
            $sender,
            $receiver,
            $content,
            Message::TYPE_INVITE,
            Message::TARGET_USER,
            $target_id
        );
    }

    public function getSource()
    {
        return 'messages';
    }
}
