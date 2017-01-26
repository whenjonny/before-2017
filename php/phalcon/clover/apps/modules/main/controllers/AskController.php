<?php
namespace Psgod\Main\Controllers;

use \Psgod\Models\Ask,
    Phalcon\Mvc\View,
    \Psgod\Models\ActionLog,
    \Psgod\Models\Reply,
    \Psgod\Models\Focus,
    \Psgod\Models\Comment,
    \Psgod\Models\Count,
    \Psgod\Models\Usermeta,
    \Psgod\Models\Label,
    \Psgod\Models\User,
    \Psgod\Models\Upload,
    \Psgod\Models\Record;

class AskController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->assets->addCss('css/icomoon/style.css'); // 通用公共样式
        $this->assets->addCss('theme/assets/global/plugins/font-awesome/css/font-awesome.min.css');
    }

    public function downloadAction(){
        $this->noview();
        $file = $this->get('file');
        if(!is_null($file)){
                $file = "/tmp/20141219140546_.jpg";
                header ("Content-type: octet/stream");
                header ("Content-disposition: attachment; filename=".$file.";");
                header("Content-Length: ".filesize($file));
                readfile($file);
        }
        // In your html
        echo '<a href="?file=pic.jpg">Image goes Here</a>';
    }

    public function addAction()
    {
        $this->tag->prependTitle('求PS');
        //$this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

        $this->assets->addCss('tapmodo/css/jquery.Jcrop.css')
                     ->addJs('uploadify/jquery.min.js')
                     ->addJs('uploadify/jquery.uploadify.min.js')
                     ->addJs('tapmodo/js/jquery.Jcrop.js')
                     ->addJs('jqdrag/jquery-drag.js')
                     ->addJs('theme/assets/scripts/common.js')
                     ->addJs('js/ask/add.js');
    }

    public function modalAction ()
    {
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);

    }

    public function saveAction()
    {
        $this->noview();

        // todo: 写成library @feng
        // 禁言状态 找不到也会反回int 0
        $forbid = Usermeta::read_user_forbid($this->_uid);

        if ($forbid != 0){      // -1永久禁言,0或者过去的时间为不禁言,将来的时间则为禁言
            if (($forbid > 0) && ($forbid > time())){
                return ajax_return(0, '您已被管理员禁言至' . date('Y-m-d H:i:s', $forbid));
            }elseif($forbid == -1){
                return ajax_return(0, '你被举报次数过多你将被禁言7天');
            }
        }

        $upload_id  = $this->post('upload_id', 'int');
        $labels     = $this->post('labels');

        if (!$upload_id) {
            return ajax_return(0, '请上传图片');
        }

        $upload_obj = \Psgod\Models\Upload::findFirst($upload_id);
        if (!$upload_obj) {
            return ajax_return(0, "upload id {$upload_id} 对应的文件不存在。");
        }

        $ask = Ask::addNewAsk($this->_uid, '', $upload_obj);
        if ($ask) {
            ActionLog::log(ActionLog::TYPE_POST_ASK, array(), $ask);

            $user = User::findUserByUID($this->_uid);
            $user->asks_count ++;
            $user->save_and_return($user);

            $ret_labels = array();
            if (is_array($labels)){
                foreach ($labels as $label) {
                    $lbl = \Psgod\Models\Label::addNewLabel(
                        $label['content'],
                        $label['x'],
                        $label['y'],
                        $this->_uid,
                        $label['direction'],
                        $upload_id,
                        $ask->id
                    );
                    $ret_labels["{$label['vid']}"] = array('id'=>$ask->id);
                    ActionLog::log(ActionLog::TYPE_ADDED_LABEL, array(), $lbl);
                }
                return ajax_return(1, '发布求助成功！', array('ask_id'=> $ask->id, 'labels'=>$ret_labels));
            } else {
                return ajax_return(1, '发布求助成功！', array('ask_id'=> $ask->id, 'labels'=>array()));
            }
        } else {
            return ajax_return(0, '创建求PS失败。请重试');
        }
    }

    public function hotAction()
    {
        $this->tag->prependTitle('图片创意社区');
        $page   = $this->get('page', 'int', 1);
        $width  = $this->get('width', 'int', 300);
        $size   = $this->get('size', 'int', 15);
        $time   = time();
        $uid    = $this->_uid;
        $askItems = Ask::asks_page($page, $size, 'hot', array('created_before'=>$time))->items;
        $data = array();
        foreach ($askItems as $ask) {
            $temp = $ask->to_simple_array();
            $temp['labels'] = $ask->get_labels_array();
            $temp['psgods'] = $ask->get_psgod(6)->toArray();            // ps大神头像(最新的5个)
            $temp['is_download'] = $uid ? $ask->be_downloaded_by($uid) : 0;

			$image= $ask->upload->resize($width);
            $data[] = array_merge($temp, $image);
        }

        $count      = Ask::count(array("create_time <= '$time' AND reply_count > 0"));
        $Page       = new \Page($count,$size);
        $show       = $Page->show();

        $this->set('asks', $data);
        $this->set('page', $show);
    }

    public function latestAction()
    {
        $this->tag->prependTitle('最新');
        $page   = $this->get('page', 'int', 1);
        $size   = $this->get('size', 'int', 15);
        $width  = $this->get('width', 'int', 99999999);
        $time   = time();
        $uid    = $this->_uid;

        $askItems = Ask::page( array('created_before'=>$time),$page, $size, 'new');
        $data = array();
        foreach ($askItems as $ask) {
            $temp = $ask->to_simple_array();
            $temp['labels'] = $ask->get_labels_array();
            $temp['is_download'] = $uid ? $ask->be_downloaded_by($uid) : 0;

            $image= $ask->upload->resize($width);
            $data[] = array_merge($temp, $image);
        }
        $count      = Ask::count(array("create_time <= '$time' AND reply_count = 0 AND status=".Ask::STATUS_NORMAL));
        $Page       = new \Page($count,$size);
        $show       = $Page->show();

        $this->set('asks', $data);
        $this->set('page', $show);
    }

    public function showAction($id)
    {
        if (!$id) {
            $this->back();
        }
        $uid = $this->_uid;
        if( !$uid ){
            $uid = 0;
        }

        $this->assets->addCss('tapmodo/css/jquery.Jcrop.css')
                     ->addCss('css/comment/common.css')
                     ->addJs('uploadify/jquery.min.js')
                     ->addJs('uploadify/jquery.uploadify.min.js')
                     ->addJs('theme/assets/scripts/common.js')
                     ->addJs('js/ask/show.js')
                     ->addJs('js/reply/common.js')
                     ->addJs('js/comment/common.js');

        $this->tag->prependTitle('详情页面');
        $ask   = Ask::findFirst(intval($id));
        if (!$ask) $this->back();

        $count = $ask->reply_count;
        if (!$count) $this->back();


        $page  = $this->get('page', 'int', 1);
        $width = $this->get('width', 'int', 99999999);
        $limit = $this->get('limit', 'int', 10);
        $Page  = new \Page($count, $limit);
        $show  = $Page->show();

        // 作品集合
        $data = array();
        $replies = Reply::get_reply_by_ask_id($ask->id, $page, $limit);
        if( count($replies) == 0 ){
            $this->back();
        }

        $ask->increase_click_count();    // 点击数加一

        foreach ($replies as $reply) {
            $data[] = $reply->toStandardArray($uid, $width);
        }

        $ask_arr = $ask->to_simple_array();
        $image= $ask->upload->resize($width);
        $ask_arr['labels'] = Label::find("target_id=$id and type=".Label::TYPE_ASK)->toArray();
        $ask_arr = array_merge($ask_arr, $image);

        $this->set('replies', $data);
        $this->set('ask', $ask);
        $this->set('ask_arr', $ask_arr);
        $this->set('has_dl', $uid?$ask->be_downloaded_by($uid):0);
        $this->set('page', $show);
    }

    public function comment_detailAction($id) {
        if (!$id) {
            $this->back();
        }
        $uid = $this->_uid;

        $this->assets->addCss('tapmodo/css/jquery.Jcrop.css')
                     ->addJs('uploadify/jquery.min.js')
                     ->addJs('uploadify/jquery.uploadify.min.js')
                     ->addJs('theme/assets/scripts/common.js')
                     ->addJs('js/ask/show.js');

        $this->tag->prependTitle('详情页面');
        $ask   = Ask::findFirst(intval($id));
        if (!$ask) $this->back();

        $count = $ask->reply_count;
        if (!$count) $this->back();

        $ask->increase_click_count();    // 点击数加一

        $page  = $this->get('page', 'int', 1);
        $limit = 10;
        $Page  = new \Page($count, $limit);
        $show  = $Page->show();

        // 作品集合
        $data = array();
        $replies = Reply::get_reply_by_ask_id($ask->id, $page, $limit);

        foreach ($replies as $reply) {
            $data[] = $reply->toStandardArray($uid, $width);
        }

        $ask_arr = $ask->to_simple_array();
        $ask_arr['labels'] = Label::find("target_id=$id and type=".Label::TYPE_ASK)->toArray();
    }

    public function upAskAction($id) {
        $status_text = array('取消成功','点赞成功');
        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }
        $status = $this->get('status', 'int', 1);

        $ask = Ask::findFirst($id);
        if(!$ask) {
            return ajax_return(0, '求助信息不存在');
        }
        $old = ActionLog::clone_obj($ask);

        $me  = $this->_uid;
        $ret = Count::up($me, $id, Count::TYPE_ASK, $status);

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
        $status_text = array('取消关注成功','关注成功');
        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }

        $status = $this->get('status', 'int', 1);
        $ask = Ask::findFirst($id);
        if(!$ask) {
            return ajax_return(0, '求助信息不存在');
        }
        $me     = $this->_uid;

        $ret = Focus::setFocus($me, $id, $status);



        if( !$ret ){
            return ajax_return(1, 'error', false);
        }

        if( $ret instanceof Focus ){
            if( $status == Count::STATUS_NORMAL ){
                ActionLog::log(ActionLog::TYPE_FOCUS_ASK, $old, $res);
            }
            else{
                ActionLog::log(ActionLog::TYPE_CANCEL_FOCUS_ASK, $old, $res);
            }
        }
        return ajax_return(1, $status_text[$status] , true);
    }

    public function shareAskAction($id) {
        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }

        $me  = $this->_uid;
        $ask = Ask::findFirst('id='.$id);
        if( !$ask ){
            return ajax_return(0,'error', false);
        }
        $old = ActionLog::clone_obj( $ask );
        $ret = Count::share($me, $id, Count::TYPE_ASK);

        if( !$ret ){
            return ajax_return(1, 'error', false);
        }

        if( $ret instanceof Count ){
            $res = Ask::count_add($id, 'share');
            ActionLog::log(ActionLog::TYPE_SHARE_ASK, $old , $res);
        }
        return ajax_return(1, '分享成功' , true);
    }

    public function wxshareAskAction($id) {
        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }

        $me  = $this->_uid;
        $ret = Count::wxshare($me, $id, Count::TYPE_ASK);

        if( !$ret ){
            return ajax_return(1, 'error', false);
        }

        if( $ret instanceof Count ){
            $res = Ask::count_add($id, 'share');
            ActionLog::log(ActionLog::TYPE_SHARE_ASK, array(), $ret, 'weixin');
        }
        return ajax_return(1, '分享成功' , true);
    }

    public function informAskAction($id) {
        $this->noview();
        if( !$this->request->isAjax() ){
            return;
        }

        $me  = $this->_uid;

        $ask = Ask::findFirst('id='.$id);

        if( !$ask ){
            return ajax_return( 1, 'error', false );
        }
        $old = ActionLog::clone_obj( $ask );

        $ret = Count::inform($me, $id, Count::TYPE_ASK);

        if( $ret instanceof Count ){
            $res = Ask::count_add($id, 'inform');
            ActionLog::log(ActionLog::TYPE_INFORM_ASK, $old, $res);
        }
        return ajax_return(1, '举报成功', true);
    }
}
