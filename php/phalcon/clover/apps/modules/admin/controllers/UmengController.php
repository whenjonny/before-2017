<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\UserDevice;
use Psgod\Models\Push;
use Psgod\Models\Invitation;
use Psgod\Models\Follow;
use Psgod\Models\Message;
use Psgod\Models\Comment;
use Psgod\Models\Ask;
use Psgod\Models\Reply;
use Psgod\Models\ActionLog;

class UmengController extends ControllerBase{

    public function initialize(){
    }

    /**
     * 友盟推送消息侧测试接口
     */
    public function push_messagesAction() { 

        $this->push_repliesAction();
        $this->push_commentsAction();
        $this->push_followsAction();
        $this->push_invitesAction();
    }

    private function push_switch($type, $uid){
        $settings = UserDevice::get_push_stgs( $uid );
        if(!$settings) {
            return false;
        }
        return $settings->$type;
    }

    public function push_invitesAction(){
        $time       = Push::lastPushTime(Message::TYPE_INVITE);
        //$time = 0;
        $invites    = Invitation::list_unread_invites($time);

        $data = array();
        foreach($invites as $invite) {
            if(!$this->push_switch(UserDevice::PUSH_TYPE_INVITE, $invite->invite_uid))
                continue;
            $token = UserDevice::getUsersDeviceToken($invite->invite_uid);
            $text  = Message::getPushMessage(Message::TYPE_INVITE);

            $custom = array(
                'type'=>Message::TYPE_INVITE,
                'count'=>$follow->num
            );
            $this->push($text, $custom, $token);
            $custom['token'] = $token;
            $custom['uid']   = $invite->invite_uid;
            $data[] = $custom;
        }

        if(sizeof($data) > 0)
            Push::addNewPush(Message::TYPE_INVITE, json_encode($data));
    }


    public function push_followsAction(){
        $time       = Push::lastPushTime(Message::TYPE_FOLLOW);
        //$time = 0;
        $follows    = Follow::list_unread_followers($time);

        $data = array();
        foreach($follows as $follow) {
            if(!$this->push_switch(UserDevice::PUSH_TYPE_FOLLOW, $follow->follow_who))
                continue;
            $token = UserDevice::getUsersDeviceToken($follow->follow_who);
            $text  = Message::getPushMessage(Message::TYPE_FOLLOW);

            $custom = array(
                'type'=>Message::TYPE_FOLLOW,
                'count'=>$follow->num
            );
            $this->push($text, $custom, $token);
            $custom['token'] = $token;
            $custom['uid']   = $follow->follow_who;
            $data[] = $custom;
        }

        if(sizeof($data) > 0)
            Push::addNewPush(Message::TYPE_FOLLOW, json_encode($data));
    }

    public function push_repliesAction(){
        $time       = Push::lastPushTime(Message::TYPE_REPLY);
        //$time = 0;
        $replies    = Reply::list_unread_replies($time);

        $data = array();
        foreach($replies as $reply) {
            if(!$this->push_switch(UserDevice::PUSH_TYPE_REPLY, $reply->uid))
                continue;
            $token = UserDevice::getUsersDeviceToken($reply->uid);
            $text  = Message::getPushMessage(Message::TYPE_REPLY);
            $custom = array(
                'type'=>Message::TYPE_REPLY,
                'count'=>$reply->num
            );
            $this->push($text, $custom, $token);
            $custom['token'] = $token;
            $custom['uid']   = $reply->uid;
            $data[] = $custom;
        }

        if(sizeof($data) > 0)
            Push::addNewPush(Message::TYPE_REPLY, json_encode($data));
    }

    public function push_commentsAction(){
        $time       = Push::lastPushTime(Message::TYPE_COMMENT);
        //$time = 0;
        $comments   = Comment::list_unread_comments($time);

        $comment_ids = array();
        $ask_ids     = array();
        $reply_ids   = array();
        foreach($comments as $comment){
            // 评论的评论
            if($comment->for_comment != 0){
                $comment_ids[] = $comment->for_comment;
            }
            // 求助的评论
            else if($comment->type == Comment::TYPE_ASK) {
                $ask_ids[] = $comment->target_id;
            }
            // 作品的评论
            else if($comment->type == Comment::TYPE_REPLY) {
                $reply_ids[] = $comment->target_id;
            }
        }

        $data       = array();
        $comments   = Comment::list_comments($comment_ids);
        foreach($comments as $comment){
            if(!$this->push_switch(UserDevice::PUSH_TYPE_COMMENT, $comment->uid))
                continue;
            $token = UserDevice::getUsersDeviceToken($comment->uid);
            $text  = Message::getPushMessage(Message::TYPE_COMMENT);

            $custom = array(
                'type'=>Message::TYPE_COMMENT,
                'count'=>$comment->num
            );
            $this->push($text, $custom, $token);
            $custom['token'] = $token;
            $custom['uid']   = $comment->uid;
            $data[] = $custom;
        }

        $asks = Ask::list_asks($ask_ids);
        foreach($asks as $ask){
            if(!$this->push_switch(UserDevice::PUSH_TYPE_COMMENT, $ask->uid))
                continue;
            $token = UserDevice::getUsersDeviceToken($ask->uid);
            $text  = Message::getPushMessage(Message::TYPE_COMMENT);
            $custom= array(
                'type'=>Message::TYPE_COMMENT,
                'count'=>$ask->num
            );
            $this->push($text, $custom, $token); 
            $custom['token'] = $token;
            $custom['uid']   = $ask->uid;
            $data[] = $custom;
        }

        $replies = Reply::list_replies($reply_ids);
        foreach($replies as $reply){
            if(!$this->push_switch(UserDevice::PUSH_TYPE_COMMENT, $reply->uid))
                continue;
            $token = UserDevice::getUsersDeviceToken($reply->uid);
            $text  = Message::getPushMessage(Message::TYPE_COMMENT);
            $custom= array(
                'type'=>Message::TYPE_COMMENT,
                'count'=>$reply->num
            );
            $this->push($text, $custom, $token); 

            $custom['token'] = $token;
            $custom['uid']   = $reply->uid;
            $data[] = $custom;
        }
        //ActionLog::log(ActionLog::TYPE_PUSH_UMENG, array(), $data);

        $num = sizeof($comments) + sizeof($asks) + sizeof($replies);
        if($num > 0)
            Push::addNewPush(Message::TYPE_COMMENT, json_encode($data));
    }

    private function push($text, $custom, $tokenList){
        if( !empty( $tokenList['android']) ){
            $Umeng = new \AndroidUMeng();
            $ret = $Umeng->title(APP_NAME)
               ->ticker($text)
               ->text($text)
               ->listcast( $tokenList['android'] )
               ->after_open('go_custom')
               ->setContent('custom', json_encode($custom))
               ->send();
        }

        if( !empty( $tokenList['ios']) ){
            $Umeng = new \iOSUMeng();
            $ret = $Umeng->alert($text)
               ->listcast( $tokenList['ios'] )
               ->setContent('custom', json_encode($custom))
               ->send();
        }
    }
}
