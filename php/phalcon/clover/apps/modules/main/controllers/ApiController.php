<?php
namespace Psgod\Main\Controllers;

use \Psgod\Models\Ask,
    Phalcon\Mvc\View,
    \Psgod\Models\ActionLog,
    \Psgod\Models\Reply,
    \Psgod\Models\Follow,
    \Psgod\Models\Download,
    \Psgod\Models\Label;

class ApiController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->noview();
    }

    public function detailAction()
    {
        $uid    = $this->_uid;
        $id     = $this->get("id", "int");
        $type   = $this->get("type", "int", Label::TYPE_ASK);
        if (is_null($id)) {
		    return ajax_return(0, '请选择具体的求p');
        }
        if($type == Label::TYPE_ASK){
            $ask    = Ask::findFirst($id);
            $user   = $ask->asker->to_simple_array();
            $data   = $ask->to_simple_array();
            $data['create_time'] = time_in_ago($data['create_time']);
            $data['has_dl']   = $uid ? $ask->be_downloaded_by($uid) : 0;
        }
        else {
            $reply  = Reply::findFirst($id);
            $user   = $reply->replyer->to_simple_array();
            $data   = $reply->to_simple_array();
            $data['create_time'] = time_in_ago($data['create_time']);
            $data['has_dl']   = $uid ? $reply->be_downloaded_by($uid) : 0;
        }

        return ajax_return(1, 'okay', array(
            'data'=>$data,
            'user'=>$user
        ));
    }

    public function delete_workAction(){
        $type = $this->post("type", "int", Label::TYPE_REPLY);
        $id   = $this->post("id", "int");

        if(!$id){
            return ajax_return(0, '请选择删除的作品');
        }

        $reply = Reply::findFirst($id);
        if(!$reply){
            return ajax_return(0, '请选择删除的作品');
        }
        if($reply->uid != $this->_uid){
            return ajax_return(0, '该作品不是你的哦');
        }
        $old = ActionLog::clone_obj( $reply );

        $ret = Reply::update_status($reply, Reply::STATUS_DELETED, '', $this->_uid);

        if( $ret instanceof Reply){
            ActionLog::log(ActionLog::TYPE_DELETE_REPLY, $old, $ret);
        }
        return ajax_return(1, 'okay');
    }

    public function delete_progressAction() {
        $type = $this->post("type", "int", Label::TYPE_REPLY);
        $id   = $this->post("id", "int");

        if(!$id){
            return ajax_return(0, '请选择删除的记录');
        }

        $download = Download::findFirst($id);
        if(!$download){
            return ajax_return(0, '请选择删除的记录');
        }

        if($download->uid != $this->_uid){
            return ajax_return(0, '未下载');
        }
        $old = ActionLog::clone_obj( $download );

        $download->status = Download::STATUS_DELETED;
        $new = $download->save_and_return($download);
        if( $new instanceof Download ){
            ActionLog::log(ActionLog::TYPE_DELETE_DOWNLOAD, $old, $new);
        }

        return ajax_return(1, 'okay');
    }

    public function followAction() {
        $uid = $this->post('uid');
        if(!$uid)
            return ajax_return(0, '请选择关注的账号');

        $me  = $this->_uid;

        $ret = Follow::setUserRelation($uid, $me, Follow::STATUS_NORMAL);
        if($ret){
            if( $ret instanceof Follow ){
                ActionLog::log(ActionLog::TYPE_FOLLOW_USER, array(), $ret);
            }
            return ajax_return(1, 'okay');
        }
        else
            return ajax_return(0, 'error');
    }

    public function unfollowAction() {
        $uid = $this->post('uid');
        $me  = $this->_uid;
        $ret = Follow::setUserRelation($uid, $me, Follow::STATUS_DELETED);

        if($ret){
            if( $ret instanceof Follow ){
                ActionLog::log(ActionLog::TYPE_UNFOLLOW_USER, array(), $ret);
            }
            return ajax_return(1, 'okay');
        }
        else
            return ajax_return(0, 'error');
    }

}
