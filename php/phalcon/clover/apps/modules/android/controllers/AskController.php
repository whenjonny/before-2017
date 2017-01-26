<?php
namespace Psgod\Android\Controllers;

use Psgod\Android\Models\User;
use Psgod\Android\Models\Ask;
use Psgod\Android\Models\Reply;
use Psgod\Models\ActionLog;
use Psgod\Models\Focus;
use Psgod\Models\Comment;
use Psgod\Models\Label;
use Psgod\Models\Record;
use Psgod\Models\Collection;
use Psgod\Models\Upload;
use Psgod\Models\Count;
use Psgod\Models\Invitation;

class AskController extends ControllerBase
{
	public function indexAction()
    {
		$page         = $this->get('page', 'int', 1);
		$size         = $this->get('size', 'int', 15);
        $width        = $this->get('width', 'int', 480);
        //todo: type后续改成数字
		$type         = $this->get('type', 'string', 'hot');
		$sort         = $this->get('sort', 'string', 'time');
		$order        = $this->get('order', 'string', 'desc');
		$last_updated = $this->get('last_updated', 'int', time());
        $uid = $this->_uid;

        $askItems = Ask::asks_page($page, $size, $type, array('created_before'=>$last_updated))->items;
        $data = array();
        foreach ($askItems as $ask) {
            $data[] = $ask->toStandardArray($uid, $width);
		}
		return ajax_return(1, 'okay', $data);
    }

    /**
     * [showAction 求p详情]
     * @param  [type] $ask_id [description]
     * @return [type]         [description]
     */
    public function showAction($ask_id)
    {
    	$page  = $this->get('page', 'int', 1);
		$size  = $this->get('size', 'int', 15);
        $width = $this->get('width', 'int', 480);
        $fold  = $this->get('fold', 'int', 0);
        $time  = $this->get('last_updated', 'int', time());

        $uid = $this->_uid;

        $ask   = Ask::findFirst(intval($ask_id));
        if( !$ask ){
            return ajax_return(0,'没有此作品');
        }
        $ask->increase_click_count();    // 点击数加一
        $replyItems = Reply::get_reply_by_ask_id($ask->id, $page, $size);

        $replies = array();
        if($page == 1 && $fold == 1){
            $replies[] = $ask->toStandardArray($uid, $width);
        }
        foreach ($replyItems as $reply) {
            $replies[] = $reply->toStandardArray($uid, $width);
        }

        return ajax_return(1, 'okay', array(
            'replies'=>$replies
        ));
    }

	public function saveAction()
    {
        $this->debug_log->log(json_encode($_REQUEST));
        $upload_id = $this->post('upload_id', 'int');
        $labels_str= $this->post('labels');

        if (!$upload_id) {
			return ajax_return(0, 'upload_id 不能为空');
        }
        $uid = $this->_uid;
        $upload_obj = \Psgod\Models\Upload::findFirst($upload_id);

        if (!$upload_obj) {
			return ajax_return(0, "upload id {$upload_id} 对应的文件不存在。");
        }
		$ask = \Psgod\Models\Ask::addNewAsk($uid, '', $upload_obj);
        if (!$ask) {
		    return ajax_return(0, '创建求PS失败。请重试');
        }
        ActionLog::log(ActionLog::TYPE_POST_ASK, array(), $ask);

        $user = User::findUserByUID($uid);
        $user->asks_count ++;
        $user->save_and_return($user);

        $labels = json_decode($labels_str, true);

        $ret_labels = array();
        if (is_array($labels)){
            foreach ($labels as $label) {
                $lbl = \Psgod\Models\Label::addNewLabel(
                    $label['content'],
                    $label['x'],
                    $label['y'],
                    $uid,
                    $label['direction'],
                    $upload_id,
                    $ask->id
                );
                $ret_labels[$label['vid']] = array('id'=>$lbl->id);
                ActionLog::log(ActionLog::TYPE_ADDED_LABEL, array(), $lbl);
            }
            return ajax_return(1, '新建PS成功！', array('ask_id'=> $ask->id, 'labels'=>$ret_labels));
        }
        return ajax_return(1, '新建PS成功！', array('ask_id'=> $ask->id, 'labels'=>array()));
	}

    public function upAskAction($id) {
        $status_text = array('取消成功','点赞成功');
        $status = $this->get('status', 'int', 1);
        if($status > 1){
            return ajax_return(0, '点赞状态错误');
        }
        $ask = Ask::findFirst($id);
        $old = ActionLog::clone_obj($ask);
        if(!$ask) {
            return ajax_return(0, '求助信息不存在');
        }
        $uid = $this->_uid;

        $ret = Count::up($uid, $id, Count::TYPE_ASK, $status);

        if( !$ret  ){
            return ajax_return(1, 'error', false);
        }

        $msg = 'ok';
        if( $status == Count::STATUS_NORMAL ){
            $res = Ask::count_add($id, 'up');
            ActionLog::log(ActionLog::TYPE_UP_ASK, $old, $res);
        }
        else {
            $res = Ask::count_reduce($id, 'up');
            ActionLog::log(ActionLog::TYPE_CANCEL_UP_ASK, $old, $res);
        }

        return ajax_return(1, $status_text[$status], true);
    }

    public function focusAskAction($id) {
        $status = $this->get('status', 'int', 1);
        $ask = Ask::findFirst($id);
        if(!$ask) {
            return ajax_return(0, '求助信息不存在');
        }
        $me     = $this->_uid;

        $ret = Focus::setFocus($me, $id, $status);


        if( !$ret || !($ret instanceof Focus) ){
            return ajax_return(1, 'error', false);
        }

        if( $status == Focus::STATUS_NORMAL ){
            ActionLog::log(ActionLog::TYPE_FOCUS_ASK, array(), $ret);
            return ajax_return(1, '关注成功', true);
        }
        else {
            ActionLog::log(ActionLog::TYPE_CANCEL_FOCUS_ASK, array(), $ret);
            return ajax_return(1, '取消关注成功', true);
        }

        return ajax_return(1, $msg, $ret);
    }

    public function informAskAction($id) {
        $ask = Ask::findFirst($id);
        $old = ActionLog::clone_obj($ask);
        if(!$ask) {
            return ajax_return(0, '求助信息不存在');
        }

        $me  = $this->_uid;
        $ret = Count::inform($me, $id, Count::TYPE_ASK);

        $res = false;
        $msg = 'error';

        if( $ret instanceof Count ){
            $msg = 'okay';
            $res = Ask::count_add($id, 'inform');
            ActionLog::log(ActionLog::TYPE_INFORM_ASK, $old, $res);
        }

        return ajax_return(1, $msg, $ret);
    }

    public function inviteAction($id) {
        $me  = $this->_uid;
        $ask = Ask::findFirst($id);
        if(!$ask) {
            return ajax_return(0, '求助信息不存在');
        }
        if($me == $ask->uid) {
            return ajax_return(0, '该求助信息不是您发布的');
        }

        $status = $this->get('status', 'int', 1);
        $invite = $this->get('invite', 'int');

        $ret = Invitation::updateInvitation($id, $invite, $status);

        if( !$ret || !($ret instanceof Invitation) ){
            return ajax_return(1, 'error', false);
        }

        if( $status == Invitation::STATUS_NORMAL ){
            ActionLog::log(ActionLog::TYPE_INVITE_FOR_ASK, array(), $ret);
            $msg = '邀请成功';
        }
        else {
            ActionLog::log(ActionLog::TYPE_CANCEL_INVITE_FOR_ASK, array(), $ret);
            $msg = '取消邀请成功';
        }

        return ajax_return(1, $msg, $ret);
    }

    public function inviteListAction($id) {
        $me  = $this->_uid;
        $page  = $this->get('page', 'int', 1);
        $size  = $this->get('size', 'int', 10);

        $data = array();
        $master = array();
        $ask = Ask::findFirst($id);
        if(!$ask) {
            return ajax_return(0, '求助信息不存在');
        }
        if($me == $ask->uid) {
            $data = User::getInviteList($me, $id, $page, $size);
            $master = User::getMasterRows($id);
        }
        return ajax_return(1, 'okay', array(
            'master' =>$master,
            'fellows'=>$data
        ));
    }
}
