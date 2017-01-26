<?php
namespace Psgod\Android\Controllers;

use Psgod\Models\User;
// use Psgod\Models\Ask;
use Psgod\Android\Models\Ask;
use Psgod\Models\Upload;
use Psgod\Models\Reply;
use Psgod\Models\Comment;
use Psgod\Models\Download;

class IndexController extends ControllerBase
{

	public function testAction(){
		$a = Ask::newAskList();
		foreach ($a as $item) {
			dump($item->toStandardArray());die();
		}
		$a= $a->save_and_return($a);
		dump($a->toArray());
		die();
		$a = Ask::newAskList();
		dump($a);die();
		// dump($a->getReplyerRows());
		dump($a->getLabelRows());
		dump($a->getNewCommentRows());
		dump($a->getHotCommentRows());
		die();
		$user = User::findFirst(39);
		dump($user->toArray());die();
		$ask = Ask::findFirst(70);
		dump($ask->uid);
		dump($ask->be_downloaded_by(2));
		$cc = Comment::comment_page(1,68)->items;
		foreach ($cc as $k){
			dump($k->toArray());
		}
		dump(count($cc));
		$a = Ask::findFirst(array('89'));
		dump($a->id);
		dump($a->get_labels_array());
		dump(now_str());
		dump(strtotime(now_str()));
		dump(date('Y-m-d H:i:s',strtotime(now_str())));
		dump(date('Y-m-d s:i:H'));
		dump(strtotime(date('Y-m-d s:i:H')));
		dump(date('Y-m-d H:i:s' ,strtotime(date('Y-m-d s:i:H'))));
		$p1 = Ask::asks_page('new', 1,10);
		count($p1->items);
		$p2 = Ask::asks_page('new', 2,10);
		count($p2->items);
		$this->noview();
		// dump(new Ask());
		dump(Ask::result_page(1, 10, array('uid'=>2))->items->count());
		dump(Ask::fellow_asks_page(2,1,10)->items[0]->uid);
	}

    public function indexAction()
    {

    	$this->response->redirect('v1/ask/index');
		$page         = $this->get('page', 'int', 1);
		$size         = $this->get('size', 'int', 15);
		$width        = $this->get('width', 'int', 480);
		$type         = $this->get('type', 'string', 'new');
		// $sort         = $this->get('sort', 'string', 'time');
		// $order        = $this->get('order', 'string', 'desc');
		$last_updated = $this->get('last_updated', 'int');

        $uid = $this->_uid;

        $askItems = Ask::asks_page($page, $size, $type, array('status'=>Ask::STATUS_NORMAL))->items;
        $data = array();
        foreach ($askItems as $ask) {
            $temp = $ask->to_simple_array();

            $width = $width*$temp['scale'];
            $temp['image_width']    = $width;
            $temp['image_height']   = intval($width*$temp['ratio']);
            $temp['image_url']      = get_cloudcdn_url($temp['image_url'], $width);

			$temp['is_download']    = $ask->be_downloaded_by($uid);
			$temp['replyer']        = $ask->get_replyers_array();
			$temp['hot_comments']   = $ask->get_comments_array();
            $temp['comments']       = $ask->get_comments_array();
            $temp['labels']         = $ask->get_labels_array();

			$data[] = $temp;
		}
		ajax_return(1, 'okay', $data);
    }

    /**
     * [showAction 求p详情]
     * @param  [type] $ask_id [description]
     * @return [type]         [description]
     */
    public function showAction($ask_id)
    {
    	$this->response->redirect('v1/ask/show/'.$ask_id);
    	$page  = $this->get('page', 'int', 1);
		$size  = $this->get('size', 'int', 15);
		$width = $this->get('width', 'int', 480);
		$time  = time();
        $replyItems = Reply::replies_page($page, $size, 'new', array('ask_id'=>$ask_id, 'created_before'=>$time, 'status'=>Reply::STATUS_NORMAL))->items;
		$data = array(); $data['replies'] = array();
		foreach ($replyItems as $reply) {
			$temp = $reply->to_simple_array();
			$temp['image_width']  = $width;
            $temp['image_height'] = get_image_height($reply->image_url,$width);
			$temp['is_download']  = time()%2;
            $temp['hot_comments'] = $reply->get_comments_array();
            $temp['comments'] = $reply->get_comments_array();
			$data['replies'][]    = $temp;
		}
		ajax_return(1, 'okay', $data);
    }

    public function get_commentsAction()
    {
		$page   = $this->get('page', 'int', 1);
		$size   = $this->get('size', 'int', 15);
		$type   = $this->get('type');
		$target = $this->get('target');
		$time  = now_str();
    	if($type == 'ask') $type = Comment::TYPE_ASK;
    	else if($type == 'reply') $type = Comment::TYPE_REPLY;
		else{
			return ajax_return(1,'error','没有type');
		}
		if( !$target ){
			return ajax_return( 1,'error', '没有id');
		}

    	$commentItems = Comment::comment_page($type, $target, $page, $size, 'new', array('created_before'=>$time, 'status'=>Comment::STATUS_NORMAL))->items;
		$data = array();
		foreach ($commentItems as $comment) {
			$temp = $comment->to_simple_array();
			$data[] = $temp;
		}
		ajax_return(1, 'okay', $data);

    }

    public function FirstAction () {

    	$page         = $this->get('page', 'int', 1);
		$size         = $this->get('size', 'int', 15);
		$width        = $this->get('width', 'int', 480);
		$last_updated = $this->get('last_updated', 'int');
		$type         = $this->get('type', 'string', 'new');

        $uid = $this->_uid;

        $data = array();
        if($type == 'new') {
        	$asks = Ask::newAskList($page, $size);
        }else {
        	$asks = Ask::hotAskList($page, $size);
        }

        $temp = array();
        foreach ($asks as $ask) {
        	$temp = $ask->toStandardArray();
        	$temp['image_width']    = $width;
            $temp['image_height']   = intval($width*(isset($temp['ratio'])?$temp['ratio']: 3/4));
        	$data[] = $temp;
        }
		ajax_return(1, 'okay', $data);
    }
}


