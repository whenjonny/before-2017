<?php
namespace Psgod\Android\Controllers;
use Psgod\Models\ActionLog;
use Psgod\Models\Reply;
use Psgod\Models\User;
use Psgod\Models\Collection;
use Psgod\Models\Record;
use Psgod\Models\Count;

class ReplyController extends ControllerBase
{

    /**
     * 回复作品
     */
	public function saveAction()
    {
		$ask_id    = $this->post('ask_id', 'int');
		$upload_id = $this->post('upload_id', 'int');
        $labels_str= $this->post('labels');

        if (!$upload_id){
            return ajax_return(0, "请上传图片");
        }
        if (!$ask_id){
            return ajax_return(0, "请选择回复的内容");
        }
        $uid = $this->_uid;

        $upload_obj = \Psgod\Models\Upload::findFirst($upload_id);
        if (!$upload_obj) {
            return ajax_return(0, "请重新上传图片");
        }

        $reply = \Psgod\Models\Reply::addNewReply($uid, $labels_str, $ask_id, $upload_obj);
        if (!$reply) {
            return ajax_return(0, "添加回复失败");
        }
        ActionLog::log(ActionLog::TYPE_POST_REPLY, array(), $reply);

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
                    $reply->id,
                    \Psgod\Models\Label::TYPE_REPLY
                );
                $ret_labels[$label['vid']] = array('id'=>$lbl->id);
                ActionLog::log(ActionLog::TYPE_ADDED_LABEL, array(), $lbl);
            }

            return ajax_return(1, '内容回复成功！', array('reply_id'=> $reply->id, 'labels'=>$ret_labels));
        }

        return ajax_return(1, '回复成功！', array('reply_id'=> $reply->id, 'labels'=>array()));
	}

    public function upReplyAction($id) {
        $status_text = array('取消成功','点赞成功');
        $reply = Reply::findFirst($id);
        if(!$reply) {
            return ajax_return(0, '该作品不存在');
        }
        $old = ActionLog::clone_obj($reply);

        $me  = $this->_uid;
        $status = $this->get('status','string', Count::STATUS_NORMAL);

        $ret = Count::up($me, $id, Count::TYPE_REPLY, $status);

        if( !$ret ){
            return ajax_return(1, 'error', false);
        }

        if( $ret instanceof Count ){
            if( $status == Count::STATUS_NORMAL ){
                $res = Reply::count_add($id, 'up');
                ActionLog::log(ActionLog::TYPE_UP_REPLY, $old, $res);
            }
            else{
                $res = Reply::count_reduce($id, 'up');
                ActionLog::log(ActionLog::TYPE_CANCEL_UP_REPLY, $old, $res);
            }
        }
        return ajax_return(1, $status_text[$status] , true);
    }

    public function collectReplyAction($id) {
        $status_text = array('取消收藏成功','收藏成功');
        $status = $this->get('status', 'int', Collection::STATUS_NORMAL);
        $me     = $this->_uid;

        $ret = Count::collect($me, $id, Count::TYPE_REPLY, $status);

        if( !$ret ){
            return ajax_return(1, 'error', false);
        }

        if( $ret instanceof Count ){
            $collect = Collection::setCollection($me, $id, $status);
            if( $collect->status == Collection::STATUS_NORMAL ){
                ActionLog::log(ActionLog::TYPE_COLLECT_REPLY, array(), $collect);
            }
            else{
                ActionLog::log(ActionLog::TYPE_CANCEL_COLLECT_REPLY, array(), $collect);
            }
        }
        return ajax_return(1, $status_text[$status] , true);

    }

    public function informReplyAction($id) {
        $me  = $this->_uid;
        $ret = Count::inform($me, $id, Count::TYPE_REPLY);

        if( !$ret ){
            return ajax_return(1, 'error', false);
        }
        $reply = Reply::findFirst('id='.$id);

        if( !$reply ){
            return ajax_return( 1, 'error', false );
        }
        $old = ActionLog::clone_obj( $reply );

        if( $ret instanceof Count ){
            $res = Reply::count_add($id, 'inform');
            ActionLog::log(ActionLog::TYPE_INFORM_REPLY, $old, $res);
        }

        return ajax_return(1, '举报成功', true);
    }
}
