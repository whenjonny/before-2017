<?php
namespace Psgod\Android\Controllers;

use Psgod\Models\ActionLog;
use Psgod\Models\User;
use Psgod\Models\Label;
use Psgod\Models\Count;
use Psgod\Models\Focus;
use Psgod\Models\SysMsg;
use Psgod\Android\Models\Ask;
use Psgod\Android\Models\Reply;
use Psgod\Android\Models\Comment;
use Psgod\Android\Models\Message;

class MessageController extends ControllerBase
{

    public function delMsgAction(){
        $mids = $this->post('mids', 'string', '');
        $type = $this->post('type', 'string','');
        $uid = $this->_uid;

        $res= false;

        if( $type ){
            $msgs = Message::find('msg_type='.$type.' AND receiver='.$uid );
            if( !$msgs ){
                return ajax_return( 1, 'okay', false);
            }
            $mids = implode(',', array_column($msgs-> toArray(), 'id') );
        }
        if( !$mids ){
            return ajax_return(1,'error', false);
        }

        $msgs = Message::find('id IN('.$mids.') AND receiver='.$uid);
        if( !$msgs ){
            return ajax_return( 1, 'okay', false);
        }

        $old = ActionLog::clone_obj($msgs);
        $res = Message::delMsgs( $uid, $mids );
        if( $res ){
            ActionLog::log(ActionLog::TYPE_DELETE_MESSAGES, explode(',',$mids), array());
        }
        return ajax_return(1,'okay', $res);
    }

	public function followAction() {
		$uid          = $this->_uid;
		$page         = $this->get('page', 'int', 1);
		$size         = $this->get('size', 'int', 15);
		$last_updated = $this->get('last_updated', 'int', time());

        $data = array();
        $msgs = Message::followMsgList($uid, $last_updated, $page, $size);

        $data = $msgs->toArray();

		return ajax_return(1, 'okay', $data);
    }

	public function replyAction() {
		$uid          = $this->_uid;
		$page         = $this->get('page', 'int', 1);
		$size         = $this->get('size', 'int', 15);
		$width        = $this->get('width', 'int', 480);
		$last_updated = $this->get('last_updated', 'int', time());

        $msgs = Message::replyMsgList($uid, $last_updated, $page, $size);

        $data = array();
        foreach($msgs as $msg){
            $tmp = array();
            $tmp['id']  = $msg->id;
            $tmp['ask'] = $msg->ask->toStandardArray($uid, $width);
            unset($msg->ask);
            $tmp['reply'] = $msg->toArray();

            $data[] = $tmp;
        }
		return ajax_return(1, 'okay', $data);
	}

	public function inviteAction() {
		$uid          = $this->_uid;
		$page         = $this->get('page', 'int', 1);
		$size         = $this->get('size', 'int', 15);
		$width        = $this->get('width', 'int', 480);
		$last_updated = $this->get('last_updated', 'int', time());

        $msgs = Message::inviteMsgList($uid, $last_updated, $page, $size);

        $data = array();
        foreach($msgs as $msg){
            $tmp = array();
            $tmp['id']  = $msg->id;
            $tmp['ask'] = $msg->ask->toStandardArray($uid, $width);
            unset($msg->ask);
            $tmp['inviter'] = $msg->toArray();

            $data[] = $tmp;
        }
		return ajax_return(1, 'okay', $data);
	}

	public function commentAction() {
		$uid          = $this->_uid;
		$page         = $this->get('page', 'int', 1);
		$size         = $this->get('size', 'int', 15);
		$width        = $this->get('width', 'int', 480);
		$last_updated = $this->get('last_updated', 'int', time());

		$data   = array();
        $msgs = Message::commentMsgList($uid, $last_updated, $page, $size);

        foreach( $msgs as $msg){
			$temp   = array();
	        $temp['comment']   = $msg->toArray();

			if($msg['type']==Message::TARGET_ASK) {
                $ask_id = $msg['target_id'];
            }
            else if($msg['type']==Message::TARGET_REPLY) {
				$reply = Reply::findFirst($msg['target_id']);
				$ask_id = $reply->ask_id;
            }

			$ask = Ask::findFirst($ask_id);
            $temp['ask'] = $ask->toStandardArray($uid, $width);

            $data[] = $temp;
        }
		return ajax_return(1, 'okay', $data);
	}

    public function sysmsgAction(){
        $uid          = $this->_uid;
        $page         = $this->get('page', 'int', 1);
        $size         = $this->get('size', 'int', 15);
        $width        = $this->get('width', 'int', 480);
        $last_updated = $this->get('last_updated', 'int', time());

        $data   = array();
        $msgs = Message::sysMsgList($uid, $last_updated, $page, $size) -> toArray();

        $data = array();
        foreach( $msgs as $msg ){
            $msg['avatar'] = 'http://'.$this->config['host']['pc'].'/img/avatar.jpg';
            if( $msg['sender'] == 0 ){
                $msg['username'] = '系统消息';
            }
            else{
                $sender = User::findFirst('uid='.$msg['sender']);
                $msg['username'] = $sender->username;
                $msg['avatar'] = $sender->avatar;
            }
            switch( $msg['target_type'] ){
                case Message::TARGET_ASK:
                    $ask = Ask::findFirst( 'id='.$msg['target_id'] );
                    $msg['pic_url'] = get_cloudcdn_url($ask->upload->savename);
                    break;
                case Message::TARGET_REPLY:
                    $reply = Reply::findFirst( 'id='. $msg['target_id'] );
                    $msg['pic_url']  = get_cloudcdn_url($reply->upload->savename);
                    break;
                case Message::TARGET_SYSTEM:
                    $sysmsg = SysMsg::findFirst('id='.$msg['target_id']);
                    $msg['jump_url'] = '-';
                    if( $sysmsg ){
                        $msg['jump_url'] = $sysmsg->jump_url;
                        $msg['target_type'] = $sysmsg->target_type;
                        $msg['target_id'] = $sysmsg->target_id;
                    }
                    break;
                default:
                    break;
            }
            array_push( $data, $msg );
        }

        return ajax_return(1, 'okay', $data);
    }
}
