<?php
namespace Psgod\Admin\Controllers;

use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

use Phalcon\Mvc\Controller,
    Phalcon\Mvc\View;

use Psgod\Models\User;
use Psgod\Models\Ask;
use Psgod\Models\Reply;
use Psgod\Models\ActionLog;
use Psgod\Models\Usermeta;
use Psgod\Models\Label;
use Psgod\Models\UserScore;
use Psgod\Models\UserRole;
use Psgod\Models\Role;
use Psgod\Models\Evaluation;

class CheckController extends ControllerBase
{

    public function check_sessionAction($session_id) {
        @session_destroy();
        session_id($session_id);
        session_start();
        pr($_SESSION);
    }

    public function previewAction(){
        $id     = $this->get("id", "int");
        $type   = $this->get("type", "int", Label::TYPE_ASK);

        if($type == Label::TYPE_ASK){
            $model = Ask::findFirst("id=$id");
        }
        else {
            $model = Reply::findFirst("id=$id");
        }
        $data = $model->to_simple_array();
        $data['type']   = $type;
        $data['labels'] = $model->get_labels_array();
        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->view->model = $data;
    }

    public function indexAction() {

    }

    public function waitAction() {
        $this->assets->addCss('theme/assets/global/plugins/jquery-flexselect/css/flexselect.css');
        $this->assets->addJs('theme/assets/global/plugins/jquery-flexselect/js/jquery.flexselect.js');
        $this->assets->addJs('theme/assets/global/plugins/jquery-flexselect/js/liquidmetal.js');
    }

    public function passAction() {

    }

    public function rejectAction() {

    }

    public function releaseAction() {

    }

    public function deleteAction() {

    }

    public function list_repliesAction()
    {
        $u = $this->post('uid','int');
        //$status = $this->get('status');
        $status = $this->get("status", "int", 3);

        $username = $this->post('username', 'string');
        if( $username ){
            $matchedUserByUsername = User::findUserByUsername( $username );
            if( $matchedUserByUsername ){
                $u = $matchedUserByUsername->uid;
            }
        }
        $nickname = $this->post('nickname', 'string');
        if( $nickname ){
            $matchedUserByNickname = User::findUserByNickname( $nickname );
            if( $matchedUserByNickname ){
                $u = $matchedUserByNickname -> uid;
            }
        }

        $uids = UserRole::get_role_users(Role::TYPE_PARTTIME);
        $uid_arr = array();
        foreach($uids as $uid){
            $uid_arr[] = $uid->uid;
        }

        if( $u && in_array($u, $uid_arr) ){
            $uid_arr = array( $u );
        }
        $uid_str = implode(",", $uid_arr);
        $reply = new Reply;
        // 检索条件
        $cond = array();
        $cond[get_class($reply).'.status'] = $status;
        // 需求变更，当状态为2的时候，需要把删除的作品也拿出来
        $cond[get_class($reply).'.uid']  = array(
            $uid_str,
            "IN"
        );


        // 关联表数据结构
        $join = array();
        $join['User'] = 'uid';

        $order = array(get_class($reply).'.update_time desc');
        if($status == 3){
            $order = array('id ASC');
        }

        // 用于遍历修改数据
        $data  = $this->page($reply, $cond ,$join, $order);

        // 审批的意见
        $evaluations = Evaluation::find(array(
            "conditions"=>"uid={$this->_uid} AND status=".Evaluation::STATUS_NORMAL,
            "order"=>"update_time desc"
        ));

        foreach($data['data'] as $row){
            $row_id = $row->id;
            $row->content = '';
            $stat = $this->get_stat( $row->uid );
            $totalScore = UserScore::get_balance($row->uid);
            $row->stat = '<div class="today">今日：'.$stat['today_passed'].' / '.$stat['today_denied'].'</div>' .
                        '<div class="yesterday">昨日：'.$stat['yesterday_passed'].' / '.$stat['yesterday_denied'].'</div>' .
                        '<div class="last7days">上周：'.$stat['last7days_passed'].' / '.$stat['last7days_denied'].'</div>' .
                        '<div class="success">合计：'.$stat['passed'].' / '.$stat['denied'].'</div>' .
                        '<div class="total">总分：'.($totalScore[0] + $totalScore[1]).'</div>';

            //todo 优化搜索
            $upload = \Psgod\Models\Upload::findFirst($row->upload_id);
            $upload = $upload->resize('99999999');
            $row->image_url = $upload['image_url'];

            $ask = Ask::findFirst($row->ask_id);
            $tmp = $ask->upload->resize('99999999');
            $ask->image_url   = $tmp['image_url'];
            $ask->image_width = $tmp['image_width'];
            $ask->image_height= $tmp['image_height'];
            $labels = $ask->get_labels();
            $desc = array();
            foreach($labels as $label) {
                $desc[] = $label->content;
            }

            $row->delete   = '<button data="'.$row_id.'" class="del btn red" type="button" >删除作品</button>';
            $row->recover  = '<button data="'.$row_id.'" class="recover btn green" type="button" >重新审核</button>';

            $pc_host = $this->config['host']['pc'];
            $row->username = $row->username."<p>昵称:".$row->nickname."</p>".
                "<p><img width='50' style='border-radius: 50% !important;
              height: 50px;
              width: 50px;' src='".$row->avatar."' /></p>".
                "<p><a target='_blank' href='http://$pc_host/ask/show/$row->ask_id'>查看原图</a></p>";

            $row->create_time = "<label class='create_time'>".date("m-d H:i:s", $row->create_time)."</label><p class='counting'></p>";
            $row->ask_image = '<div class="wait-image-height">'.$this->format_image($ask->image_url, array(
                'type'=>Label::TYPE_ASK,
                'model_id'=>$ask->id
            )).'</div>'.'<div class="image-url-content">'.implode(",", $desc).'</div>';
            $reply_labels   = Label::find("type=".Label::TYPE_REPLY." and target_id=".$row->id);
            $reply_desc = array();
            foreach($reply_labels as $label) {
                $reply_desc[] = $label->content;
            }
            $row->thumb_url = '<div class="wait-image-height">'.$this->format_image($row->image_url, array(
                'type'=>Label::TYPE_REPLY,
                'model_id'=>$row->id
            )).'</div>'.'<div class="image-url-content">'.implode(",", $reply_desc).'</div>';

            $row->auditor = '无';
            $audit = UserScore::oper_user(Label::TYPE_REPLY, $row->id )->toArray();
            if($audit){
                $audit = $audit[0];
                if( $audit['uid'] ){
                    $row->auditor = $audit['username']."<p>昵称:".$audit['nickname']."</p>".
                "<p><img width='50' style='border-radius: 50% !important;
              height: 50px;
              width: 50px;' src='".$audit['avatar']."' /></p>";
                }
            }

            switch($cond[get_class($reply).'.status']){
            case Reply::STATUS_READY:
            default:
                $e_str = "";
                $o_str = "";
                foreach($evaluations as $e){
                    $e_str .= '<li><button data="'.$row_id.'" class="form-control quick-deny">'.$e->content.'</button></li>';
                    $o_str .= '<option class="quick-deny" value="'.$e->id.'">'.$e->id.'.'.$e->content.'</option>';
                }
                $row->oper = '
                    <div>
                        通过：<div>
                            <button class="btn green button-pass" type="button" data-toggle="dropdown">通过</button>
                            <ul class="dropdown-menu" role="menu">
                            <li><button data="'.$row_id.'" class="score form-control">1 分</button></li>
                            <li><button data="'.$row_id.'" class="score form-control">2 分</button></li>
                            <li><button data="'.$row_id.'" class="score form-control">3 分</button></li>
                            <li><button data="'.$row_id.'" class="score form-control">4 分</button></li>
                            <li><button data="'.$row_id.'" class="score form-control">5 分</button></li>
                            </ul>
                        </div>
                    </div>
                    <div>
                    拒绝：<a class="deny" data-toggle="modal" href="#modal_evaluation" data="'.$row_id.'">管理</a><div >
                        <select name="reason" class="flexselect">
                            '.$o_str .'
                        </select>
                        <ul class="dropdown-menu deny-reasons" role="menu"><div class="li_container">
                        '.$e_str .'
                        </div><li><a class="btn deny" data-toggle="modal" href="#modal_evaluation" data="'.$row_id.'">管理</a></li>
                        </ul>
                        <button class="btn red button-deny reject_btn" type="button" data-toggle="dropdown">拒绝</button>
                    </div></div>';
                    //<a class="deny btn red" data-toggle="modal" href="#modal_evaluation" data="'.$row_id.'">deny</a>';
                    //<button class="deny btn red btn-xs" type="button" data="'.$row_id.'">deny</button>';
                break;
            case Reply::STATUS_NORMAL:
                $user_score = UserScore::findFirst("type=".UserScore::TYPE_REPLY." and item_id=".$row->id);
                $row->score = 0;
                if($user_score) {
                    $row->score = $user_score->score;
                }
                break;
            case Reply::STATUS_DELETED:
            case Reply::STATUS_REJECT:
                $user_score = UserScore::findFirst("type=".UserScore::TYPE_REPLY." and item_id=".$row->id);
                if($user_score)
                    $row->content = $user_score->content;
                break;
            }
        }
        // 输出json
        return $this->output_table($data);
    }

    public function get_stat( $uid ){
        $stat = array(
            'today_passed'     => 0,
            'yesterday_passed' => 0,
            'last7days_passed' => 0,
            'today_denied'     => 0,
            'yesterday_denied' => 0,
            'last7days_denied' => 0,
            'total'            => 0,
            'passed'           => 0,
            'denied'           => 0
        );

        //统计
        $phql  = 'SELECT count( CASE WHEN (UNIX_TIMESTAMP()-action_time<60*60*24) AND score>0 THEN id END) as today_passed,';
        $phql .= ' count( CASE WHEN ( UNIX_TIMESTAMP()-action_time>60*60*24*2 AND (UNIX_TIMESTAMP()-action_time)<60*60*24 and score>0) THEN id END) as yesterday_passed,';
        $phql .= ' count( CASE WHEN ( UNIX_TIMESTAMP()-action_time<60*60*24*7  and score>0) THEN id END ) as last7days_passed,';
        $phql .= ' count( CASE WHEN (UNIX_TIMESTAMP()-action_time<60*60*24) AND score<=0 THEN id END) as today_denied,';
        $phql .= ' count( CASE WHEN ( UNIX_TIMESTAMP()-action_time>60*60*24*2 AND (UNIX_TIMESTAMP()-action_time)<60*60*24 and score<=0) THEN id END) as yesterday_denied,';
        $phql .= ' count( CASE WHEN ( UNIX_TIMESTAMP()-action_time<60*60*24*7  and score<=0) THEN id END ) as last7days_denied,';
        $phql .= ' count( id ) as total,';
        $phql .= ' count( CASE WHEN score>0 THEN id END) as passed,';
        $phql .= ' count( CASE WHEN score=0 THEN id END) as denied';
        $phql .= ' FROM user_scores where uid='.$uid.' group by uid';

        $userScore = new UserScore();
        $ret = new Resultset(null, $userScore, $userScore->getReadConnection()->query($phql));
        if( !$ret ){
            return $stat;
        }
        if( empty($ret->toArray()) ){
            return $stat;
        }
        $stat = array_merge($stat,$ret->toArray()[0]);

        return $stat;
    }

    public function set_statusAction(){
        $this->noview();

        $reply_id = $this->post("reply_id", "int");
        $status    = $this->post("status", "int");
        $data      = $this->post("data", "string", 0);

        if(!$reply_id or !isset($status)){
		    return ajax_return(0, '请选择具体的求助信息');
        }

        $reply = Reply::findFirst("id=$reply_id");
        if(!$reply){
		    return ajax_return(0, '请选择具体的求助信息');
        }
        $old = ActionLog::clone_obj($reply);
        // 设置状态为正常，等待定时器触发
        $res = Reply::update_status($reply, $status, $data, $this->_uid);
        if( $res ){
            //根据status不同，应该是不同TYPE
            if($status == Reply::STATUS_NORMAL)
                ActionLog::log(ActionLog::TYPE_VERIFY_REPLY, $old, $res, $data);
            else if ($status == Reply::STATUS_REJECT)
                ActionLog::log(ActionLog::TYPE_REJECT_REPLY, $old, $res);
        }
        return ajax_return(1, 'okay');
    }

    public function get_evaluationsAction(){
        $this->noview();

        $uid = $this->_uid;
        $evaluations = Evaluation::find("uid={$uid} AND status=".Evaluation::STATUS_NORMAL)->toArray();
        ajax_return(1, 'ok', $evaluations);
    }

    public function set_evaluationAction(){
        $this->noview();
        $data   = $this->post("data", "string");
        $uid    = $this->_uid;

        $evaluation = Evaluation::set_evaluation($uid, $data);
        ajax_return(1, 'okay', $evaluation);
    }

    public function del_evaluationAction(){
        $this->noview();
        if( !$this->request->isAjax() ){
            return false;
        }

        $tags = $this->post( 'data', 'string', '' );
        $uid = $this->_uid;
        $tags = explode(',', $tags);
        $failcount = 0;
        foreach( $tags as $key => $tag ){
            $evaluation = Evaluation::findFirst('status='.Evaluation::STATUS_NORMAL.' AND uid='.$uid.' AND content="'. $tag.'"');
            if( !$evaluation ){
                $failcount++;
                continue;
            }
            $evaluation->status = Evaluation::STATUS_DELETED;
            $evaluation->del_by = $uid;
            $evaluation->del_time = time();
            $save = $evaluation->save();
            if( !$save ){
                $failcount++;
            }
        }
        if( $failcount ){
            ajax_return( 1, 'error', '有部分理由删除失败' );
        }


        ajax_return( 1,'okay', $save);
    }
}

