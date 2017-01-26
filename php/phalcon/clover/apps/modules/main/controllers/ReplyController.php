<?php
namespace Psgod\Main\Controllers;

use \Psgod\Models\Download,
    \Psgod\Models\ActionLog,
    \Psgod\Models\Usermeta,
    \Psgod\Models\Collection,
    \Psgod\Models\Reply,
    \Psgod\Models\Ask,
    \Psgod\Models\Count,
    \Psgod\Models\Record;

class ReplyController extends ControllerBase
{
	public function saveAction()
	{
        $this->noview();

		$ask_id             = $this->post('ask_id', 'int');
        $upload_id          = $this->post('upload_id', 'int');
        $labels             = $this->post('labels');
        $download_type      = 1;
        $uid                = $this->_uid;
        $target_id          = \Psgod\Models\Download::get_current_ask($uid, $ask_id)->target_id;
        $download_type      = $this->post('download_type', 'int', Download::TYPE_ASK);
        $download_target_id = $this->post('download_target_id', 'int', $target_id);
        $direction          = 0;

        if (!$upload_id) {
            return ajax_return(0, '请上传图片');
        }
        $upload_obj = \Psgod\Models\Upload::findFirst($upload_id);
        if (!$upload_obj) {
            return ajax_return(0, "upload id {$upload_id} 对应的文件不存在。");
        }
        if (is_null($ask_id) or \Psgod\Models\Ask::count($ask_id) < 1) {
        	return ajax_return(0, "ask id {$ask_id} 对应的求P不存在。");
        }

        $forbid = Usermeta::read_user_forbid($uid); // 禁言状态 找不到也会反回int 0

        if ($forbid != 0){      // -1永久禁言,0或者过去的时间为不禁言,将来的时间则为禁言
            if (($forbid > 0) && ($forbid > time())){
                return ajax_return(0, '您已被管理员禁言至' . date('Y-m-d H:i:s', $forbid));
            }elseif($forbid == -1){
                return ajax_return(0, '你被举报次数过多你将被禁言7天');
            }
        }

        //上传作品加水印
        $mark_name = name_add_mark($upload_obj->savename);
        $img_url   = get_cloudcdn_url($upload_obj->savename);
        $img_wm_url = watermark2($img_url);
        if(save_image_wm($img_wm_url, $mark_name)) {
            $upload_obj->savename = $mark_name;
            $upload_obj = $upload_obj->save_and_return($upload_obj);
        }

        $reply = \Psgod\Models\Reply::addNewReply($uid, '', $ask_id, $upload_obj, $download_type, $download_target_id);
        if ($reply) {
            ActionLog::log(ActionLog::TYPE_POST_REPLY, array(), $reply);
            //$labels = json_decode($labels_str, true);
            $ret_labels = array();
            if (is_array($labels)){
                foreach ($labels as $label) {
                    $lbl = \Psgod\Models\Label::
                    addNewLabel($label['content'], $label['x'], $label['y'], $uid, $direction, $upload_id, $reply->id, \Psgod\Models\Label::TYPE_REPLY);
                    $ret_labels["{$label['vid']}"] = array('id'=>$reply->id);
                    ActionLog::log(ActionLog::TYPE_ADDED_LABEL, array(), $lbl);
                }
                ajax_return(1, '上传成功！', array('ask_id'=> $reply->id, 'labels'=>$ret_labels));
            } else {
                ajax_return(1, '上传成功！', array('ask_id'=> $reply->id, 'labels'=>array()));
            }
        } else {
            ajax_return(0, '回复失败。请重试');
        }
    }

    public function upReplyAction($id) {
        $status_text = array('取消成功','点赞成功');
        $status = $this->get('status','string', Count::STATUS_NORMAL);

        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }

        $me  = $this->_uid;
        $reply = Reply::findFirst($id);
        if(!$reply) {
            return ajax_return(0, '该作品不存在');
        }
        $old = ActionLog::clone_obj($reply);


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
        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }

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

    public function shareReplyAction($id) {
        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }

        $me  = $this->_uid;
        $ret = Count::share($me, $id, Count::TYPE_REPLY);

        if( !$ret ){
            return ajax_return(1, 'error', false);
        }

        $reply = Reply::findfirst('id='.$id);
        if( !$reply ){
            return ajax_return(0,'error',false);
        }
        $old = ActionLog::clone_obj( $reply );

        if( $ret instanceof Count ){
            $res = Reply::count_add($id, 'share');
            ActionLog::log(ActionLog::TYPE_SHARE_REPLY, $old, $res);
        }

        return ajax_return(1, '分享成功' , true);
    }

    public function wxshareReplyAction($id) {
        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }

        $me  = $this->_uid;
        $ret = Count::wxshare($me, $id, Count::TYPE_REPLY);

        if( !$ret ){
            return ajax_return(1, 'error', false);
        }

        $reply = Reply::findfirst('id='.$id);
        if( !$reply ){
            return ajax_return(0,'error',false);
        }
        $old = ActionLog::clone_obj( $reply );

        if( $ret instanceof Count ){
            $res = Reply::count_add($id, 'wxshare');
            ActionLog::log(ActionLog::TYPE_SHARE_REPLY, $old, $res,'weixin');
        }

        return ajax_return(1, '分享成功' , true);
    }

    public function informReplyAction($id) {
        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }

        if( !$id ){
            $id = $this->post('reply_id', 'int',0);
        }
        $reply = Reply::findFirst($id);
        if( !$reply ){
            return ajax_return(0, 'error', false);
        }
        $oldReply = ActionLog::clone_obj( $reply );

        $ask_id = $reply->ask_id;
        $ask = Ask::findFirst($ask_id);

        //如果楼主举报回复的作品
        if( $ask->uid == $this->_uid){
            Reply::update_status($reply, Reply::STATUS_BLOCKED);
        }

        $me  = $this->_uid;
        $ret = Count::inform($me, $id, Count::TYPE_REPLY);

        if( $ret instanceof Count ){
            $res = Reply::count_add($id, 'inform');
            ActionLog::log(ActionLog::TYPE_INFORM_REPLY, $oldReply, $res);
            //恢复删除的时候，没有做+1的才操作
            $ask->reply_count -= 1;
            $ask->save();
        }
        return ajax_return(1, '举报成功', true);
    }
}
