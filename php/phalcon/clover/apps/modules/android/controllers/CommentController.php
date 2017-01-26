<?php
namespace Psgod\Android\Controllers;

use Psgod\Models\ActionLog;
use Psgod\Models\User;
use Psgod\Models\Vote;
use Psgod\Models\Record;
use Psgod\Models\Count;
use Psgod\Models\Message;
use Psgod\Models\Reply;
use Psgod\Models\Ask;
use Psgod\Android\Models\Comment;

class CommentController extends ControllerBase
{
	public function indexAction()
    {
        $type       = $this->get('type', 'int', Comment::TYPE_ASK);
        $target_id  = $this->get('target_id', 'int');
        $page       = $this->get('page', 'int', 1);
        $size       = $this->get('size', 'int', 10);
        //todo last_updated
        if(!$target_id){
            return ajax_return(1, '请选择ID！');
        }

        $data = array();
        $comments = Comment::comment_page($type, $target_id, 1, 3, $order='hot')->items;
		$comment_arr = array();
		foreach ($comments as $comment) {
			$temp = $comment->to_simple_array();
			$comment_arr[] = $temp;
        }
        $data['hot_comments'] = $comment_arr;

        $comments = Comment::comment_page($type, $target_id, $page, $size, $order='new')->items;
		$comment_arr = array();
		foreach ($comments as $comment) {
            $temp = $comment->to_simple_array();
			$comment_arr[] = $temp;
        }
        $data['new_comments'] = $comment_arr;
		return ajax_return(1, 'okay', $data);
	}

    /**
     * 添加评论
     * $return integer  新增评论
     */
    public function send_commentAction() {
        $uid        = $this->_uid;
        $content    = $this->post('content', 'string');
        $type       = $this->post('type', 'int');
        $target_id  = $this->post('target_id', 'int');
        $reply_to   = $this->post('reply_to', 'string', '0');
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
                $ori =Comment::findFirst('id='.$for_comment);
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
                    Count::up($uid, $target_id, Count::TYPE_REPLY);
                    Record::comment($uid, $target_id, Record::TYPE_REPLY);
                    Ask::count_add($target_id, 'comment');
                    break;
                default:
                    break;
            }
        }
        return ajax_return(1, 'okay', $data);
    }

    public function upcommentAction($id) {
        $status_text = array('取消成功','点赞成功');

        $me  = $this->_uid;
        $status = $this->get('status','string', 1); // STATUS_NORMAL?
        $ret = Count::up($me, $id, Count::TYPE_COMMENT, $status);

        if( !$ret  ){
            return ajax_return(1, 'error', false);
        }

        $msg = 'ok';
        if($ret instanceof Count){
            if( $status == Count::STATUS_NORMAL ){
                $res = Comment::count_add($id, 'up');
                ActionLog::log(ActionLog::TYPE_UP_COMMENT, array(), $res);
            }
            else {
                $res = Comment::count_reduce($id, 'up');
                ActionLog::log(ActionLog::TYPE_CANCEL_UP_COMMENT, array(), $res);
            }
        }

        return ajax_return(1, $status_text[$status], true);
    }

    public function informAction($id) {
        $this->noview();

        $me  = $this->_uid;
        $status = $this->post('status','string','true');
        $sndStatus= ($status==='true')? Count::STATUS_NORMAL : Count::STATUS_DELETED;

        $ret = Count::up($me, $id, Count::TYPE_COMMENT, $sndStatus);

        if( !$ret ){
            return ajax_return(1, 'error', false);
        }

        if( $ret instanceof Count ){
            $comment = Comment::count_add($id, 'inform');
            ActionLog::log(ActionLog::TYPE_INFORM_COMMENT, array(), $comment);
        }

        return ajax_return(1, '举报成功', true);
    }
}
