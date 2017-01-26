<?php
namespace Psgod\Mobile\Controllers;

use Psgod\Mobile\Models\User;
use Psgod\Mobile\Models\Ask;
use Psgod\Mobile\Models\Reply;
use Psgod\Models\Focus;
use Psgod\Models\Comment;
use Psgod\Models\Label;
use Psgod\Models\Record;
use Psgod\Models\Collection;
use Psgod\Models\Upload;
use Psgod\Models\Count;
use Psgod\Models\Invitation;

class ReplyController extends ControllerBase
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
        $time   = time();
        $askItems = Ask::asks_page($page, $size, $type, array('created_before'=>$time))->items;
        $data = array();
        foreach ($askItems as $ask) {
            $temp = $ask->to_simple_array();
            $image= $ask->upload->resize($width);
			$temp['replyer']        = $ask->get_replyers_array();
            $temp['labels']         = $ask->get_labels_array();
            $data[] = array_merge($temp, $image);
        }

        $this->set('data', $data);
    }

    /**
     * [showAction 作品详情]
     * @param  [type] $ask_id [description]
     * @return [type]         [description]
     */
    public function shareAction($reply_id)
    {
    	$page  = $this->get('page', 'int', 0);
		$size  = $this->get('size', 'int', 1);
		$width = $this->get('width', 'int', 480);
        $time  = $this->get('last_updated', 'int', time());

        $reply = Reply::findFirst(intval($reply_id));
        if( !$reply ){
            return ajax_return(0,'没有此作品');
        }
        $ask = Ask::findFirst($reply->ask_id);

        $replies = array();
        $replies[] = $reply->toStandardArray(0, $width);
        $ask    = $ask->toStandardArray($width);

        $this->set('ask', $ask);
        $this->set('replies', $replies);
    }

	public function saveAction()
    {
        $upload_id = $this->post('upload_id', 'int', 3729);
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
                $ret_labels["{$label['vid']}"] = array('id'=>$lbl->id);
            }
            return ajax_return(1, '新建PS成功！', array('ask_id'=> $ask->id, 'labels'=>$ret_labels));
        }
        return ajax_return(1, '新建PS成功！', array('ask_id'=> $ask->id, 'labels'=>array()));
	}

    public function upAskAction($id) {

        $status = $this->get('status', 'int', 1);
        $me  = $this->_uid;
        $str    = '点赞';
        if($status!=1) {
            $status=0;
            $str   ='取消'.$str;
        }
        $ret = Count::up($me, $id, Count::TYPE_ASK, $status);
        Record::up($me, $id, Record::TYPE_ASK, $status);
        if($ret===0) {
            $code = 2;
            $msg = '你已经'.$str.'过！';
        } else if($ret) {
            $code = 1;
            $msg = 'okay!';
            $status==1?Ask::count_add($id, 'up'):Ask::count_reduce($id, 'up');
        } else {
            $code = 0;
            $msg = '系统出错！';
        }
        return ajax_return($code, $msg);
    }

    public function focusAskAction($id) {

        $status = $this->get('status', 'int', 1);
        $me     = $this->_uid;
        $str    = '关注';
        if($status!=1) {
            $status=0;
            $str   ='取消关注';
        }

        $ret = Focus::setFocus($me, $id, $status);

        if($ret===0) {
            $code = 2;
            $msg = '你已经'.$str.'过！';
        } else if($ret) {
            $code =1;
            $msg = 'okay!';
        } else {
            $code = 0;
            $msg = '系统出错！';
        }
        return ajax_return($code, $msg);
    }

    public function shareAskAction($id) {

        $me  = $this->_uid;
        $ret = Count::share($me, $id, Count::TYPE_ASK);
        Record::share($me, $id, Record::TYPE_ASK);
        if($ret===0) {
            $code = 2;
            $msg = '你已经分享过！';
        } else if($ret) {
            $code =1;
            $msg = 'okay!';
            Ask::count_add($id, 'share');
        } else {
            $code = 0;
            $msg = '系统出错！';
        }
        return ajax_return($code, $msg);
    }

    public function wxshareAskAction($id) {

        $me  = $this->_uid;
        $ret = Count::wxshare($me, $id, Count::TYPE_ASK);
        Record::wxshare($me, $id, Record::TYPE_ASK);
        if($ret===0) {
            $code = 2;
            $msg = '你已经分享过！';
        } else if($ret) {
            $code =1;
            $msg = 'okay!';
            Ask::count_add($id, 'wxshare');
        } else {
            $code = 0;
            $msg = '系统出错！';
        }
        return ajax_return($code, $msg);
    }

    public function informAskAction($id) {
        $me  = $this->_uid;
        $ret = Count::inform($me, $id, Count::TYPE_ASK);
        Record::inform($me, $id, Record::TYPE_ASK);
        if($ret===0) {
            $code = 2;
            $msg = '你已经举报过！';
        } else if($ret) {
            $code =1;
            $msg = 'okay!';
            Ask::count_add($id, 'inform');
        } else {
            $code = 0;
            $msg = '系统出错！';
        }
        return ajax_return($code, $msg);
    }

    public function inviteAction($id) {
        $status = $this->get('status', 'int', 1);
        $invite = $this->get('invite', 'int', 1);
        $me  = $this->_uid;
        $str    = '邀请';
        if($status!=1) {
            $status=0;
            $str   ='取消邀请';
        }
        $code = 0; $msg = 'err';
        $ask = Ask::findFirst($id);
        if($ask) {
            if($me == $ask->uid) {
                $ret = Invitation::updateInvitation($id, $invite, $status);
                if($ret===0) {
                    $code = 2;
                    $msg  = '你已经'.$str.'过！';
                } else if($ret) {
                    $code = 1;
                    $msg  = 'okay!';
                } else {
                    $code = 0;
                    $msg = '系统出错！';
                }
            }
        }
        return ajax_return($code, $msg);
    }

    public function inviteListAction($id) {
        $me  = $this->_uid;
        $page  = $this->get('page', 'int', 1);
        $size  = $this->get('size', 'int', 10);

        $data = array();
        $master = array();
        $ask = Ask::findFirst($id);
        if($ask) {
            if($me == $ask->uid) {
                $data = User::getInviteList($me, $id, $page, $size);
                $master = User::getMasterRows($id);
            }
        }
         return ajax_return(1, 'okay', array(
            'master' =>$master,
            'fellows'=>$data
        ));
    }

}
