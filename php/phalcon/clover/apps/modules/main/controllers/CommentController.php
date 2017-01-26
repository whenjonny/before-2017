<?php
namespace Psgod\Main\Controllers;

use Psgod\Models\User,
    Psgod\Models\ActionLog,
    Psgod\Models\Role,
    Psgod\Models\Ask,
    Psgod\Models\Count,
    Psgod\Models\Record,
    Psgod\Models\Reply,
    Psgod\Models\Label,
    Psgod\Models\Comment,
    Psgod\Models\Message,
    Psgod\Models\UserRole;

class CommentController extends ControllerBase
{

   public function showAction(){
        $target_id = $this->get('target_id', 'int');
        $width     = $this->get('width', 'int', 300);
        $type      = $this->get('type', 'int', Label::TYPE_ASK);
        $time = $this->get('time', 'int', time());
        $page = $this->get('page', 'int', 1);
        $size = $this->get('size', 'int', 10);
        $type_name = '';

        if (!$target_id) {
            exit('ID not empty');
        }
        $uid = $this->_uid;

        $this->assets->addJs('uploadify/jquery.min.js')
                     ->addJs('theme/assets/scripts/common.js')
                     ->addJs('js/comment/common.js')
                     ->addJs('js/reply/common.js')
                     ->addCss('css/icomoon/style.css')
                     ->addCss('css/comment/common.css');

        $this->tag->prependTitle('评论详情页面');
        if($type == Label::TYPE_ASK){
            $model = Ask::findFirst($target_id);
            $type_name = '求助';
        }
        else {
            $model = Reply::findFirst($target_id);
            $type_name = '作品';
        }
        $model_arr = $model->to_simple_array();
        $model_arr['type']   = Label::TYPE_REPLY;
        $model_arr['labels'] = $model->get_labels_array();
        $model_arr['is_download'] = $uid ? $model->be_downloaded_by($uid) : 0;
        $new_comments = Comment::comment_page($type, $target_id, $page, $size, $order='new')->items;

        $owner = User::findUserByUID($model_arr['uid'])->to_simple_array();

        $is_owner = $uid == $owner['uid'] ? 1 : 0;        // 是否是当前用户

        if($is_owner){
            $is_fellow    = 0;
        }
        else {
            $current_user = User::findUserByUID($this->_uid);
            $is_fellow    = $current_user->is_fellow_to($owner['uid']);
        }

        if( $model_arr['inform_count'] >= 10 ){ //标记删除的举报数
            die('该'.$type_name.'已被删除');
        }

        $model_arr['comments']['new_comments'] = array();
        foreach ($new_comments as $key => $c) {
            array_push( $model_arr['comments']['new_comments'], $c -> to_simple_array());
        }
        $model_arr['uped']   = Count::has_uped_reply( $model_arr['id'], $uid );


        $image = $model->upload->resize($width);
        $model_arr = array_merge($image, $model_arr);

        $count      = Comment::count(array("create_time <= '$time' AND type = $type AND target_id = $target_id"));
        $Page       = new \Page($count,$size);
        $show       = $Page->show();

        $this->set('model', $model_arr);
        $this->set('ownerUserInfo', $owner);
        $this->set('page', $show);
        $this->set('is_fellow', $is_fellow);
        $this->set('is_parttime', 0);
        $this->set('is_owner'  , $is_owner);
    }

    /**
     * 添加评论
     * $return integer  新增评论
     */
    public function saveAction() {
        $this->noview();

        $uid        = $this->_uid;
        $content    = $this->post('content', 'string');
        $type       = $this->post('type', 'int');
        $target_id  = $this->post('target_id', 'int');
        $reply_to   = $this->post('reply_to', 'int', '0');
        $for_comment= $this->post('for_comment', 'int', '0');

        if(empty($uid)) {
            return ajax_return(0, '非法操作', array('result' => 0));
        }

        if(empty($content) || empty($type) || empty($target_id)) {
            return ajax_return(0, '非法请求', array('result' => 0));
        }
        if(empty($for_comment)){
            $for_comment = 0;
        }

        switch( $type ){
            case Count::TYPE_ASK:
                $ori = Ask::findFirst('id='.$target_id);
                $reply_to = $ori->uid;
                break;
            case Count::TYPE_REPLY:
                $ori =Reply::findFirst('id='.$target_id);
                $reply_to = $ori->uid;
                break;
            case Count::TYPE_COMMENT:
                $ori =Comment::findFirst('id='.$target_id);
                $reply_to = $ori->uid;
                break;
            default:
                $reply_to = 0;
        }


        $result = Comment::add_comment($uid, $content, $type, $target_id, $reply_to, $for_comment);

        $data = array();
        if($result) {
            ActionLog::log(ActionLog::TYPE_POST_COMMENT, array(), $result);
            $data['id'] = $result->id;
            switch( $type ){
                case Count::TYPE_REPLY:
                    Count::up($uid, $target_id, Count::TYPE_REPLY);
                    Record::comment($uid, $target_id, Record::TYPE_REPLY);
                    Reply::count_add($target_id, 'comment');
                    break;
                case Count::TYPE_ASK:
                    break;
                default:
                    break;
            }
        }
        return ajax_return(1, 'okay', $data);
    }


    public function upCommentAction($id) {
        $status_text = array('取消成功','点赞成功');
        $this->noview();
        if( !$this->request->isAjax() ){
            return false;
        }

        $me  = $this->_uid;
        $status = $this->get('status', 'int', 1);

        $comment = Comment::findFirst($id);
        if(!$comment) {
            return ajax_return(0, '该评论不存在');
        }
        $old = ActionLog::clone_obj($comment);

        $ret = Count::up($me, $id, Count::TYPE_COMMENT, $status);


        if( !$ret ){
            return ajax_return(1, 'error', false);
        }

        if( $ret instanceof Count ){
            if( $status == Count::STATUS_NORMAL ){
                $res = Comment::count_add($id, 'up');
                ActionLog::log(ActionLog::TYPE_UP_COMMENT, $old, $res);
            }
            else{
                $res = Comment::count_reduce($id, 'up');
                ActionLog::log(ActionLog::TYPE_CANCEL_UP_COMMENT, $old, $res);
            }
        }

        return ajax_return(1, $status_text[$status] , true);

    }

    public function informAction($id) {
        $this->noview();
        if( !$this->request->isAjax() ){
            return false;
        }

        $me  = $this->_uid;
        $status = $this->post('status','string',1);

        $ret = Count::up($me, $id, Count::TYPE_COMMENT, $status);
        $comment = Comment::findFirst('id='.$id);


        if( !$comment ){
            return ajax_return( 1, 'error', false );
        }
        $old = ActionLog::clone_obj( $comment );

        if( $ret instanceof Count ){
            $res = Comment::count_add($id, 'inform');
            ActionLog::log(ActionLog::TYPE_INFORM_COMMENT, $old, $res);
        }
        return ajax_return(1, '举报成功', true);
    }


}
