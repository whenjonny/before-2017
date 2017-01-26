<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\ActionLog;
use Psgod\Models\User;
use Psgod\Models\Config;
use Psgod\Models\UserScore;
use Psgod\Models\UserSettlement;
use Psgod\Models\UserScheduling;
use Psgod\Models\Usermeta;
use Psgod\Models\UserRole;

class UserController extends ControllerBase
{

    public function indexAction()
    {
    }

    public function beatAction(){
        $this->noview(); 
        
        \Heartbeat::init(\Heartbeat::DB_LOGON)->hello($this->_uid, session_id());

        $online_count = \Heartbeat::init(\Heartbeat::DB_LOGON)->online_count();

        $nums = array();
        foreach(\Heartbeat::data() as $row){
            $nums[$row] = sizeof(\Heartbeat::init(\Heartbeat::DB_PROCESS)->fetch($row, $online_count));
        }
        
        $data = array(
            'notifications'=>array(
                '审核作品'  => $nums[\Heartbeat::CACHE_REPLY],
                '举报数'    => $nums[\Heartbeat::CACHE_INFORM],
                '帖子列表'  => $nums[\Heartbeat::CACHE_ASK] + $nums[\Heartbeat::CACHE_REPLY],
                '评论列表'  => $nums[\Heartbeat::CACHE_COMMENT],
                '用户反馈'  => $nums[\Heartbeat::CACHE_FEEDBACK]
            )
        );
        return $this->output_table($data);
    }

    
    public function list_rolesAction()
    {

        $user_role = new UserRole;
        // 检索条件
        $cond = array();
        $cond['uid']        = $this->post("uid", "int");

        // 用于遍历修改数据
        $data  = $this->page($user_role, $cond);
        foreach($data['data'] as $row){
        }
        // 输出json
        return $this->output_table($data);
    }

    public function parttime_paidAction() {
        $this->noview();

        $uid = $this->post("uid", "int");
        $oper_id = $this->_uid;

        if(!$uid) {
            return ajax_return(0, '用户不存在');
        }
        $user = User::findUserByUID($uid);
        if(!$user) {
            return ajax_return(0, '用户不存在');
        }

        $balance = UserScore::get_balance($uid);
        $current_score = $balance[UserScore::STATUS_NORMAL];
        $paid_score    = $balance[UserScore::STATUS_PAID];

        if( $current_score <= 0 ) {
            return ajax_return(0, '当前未结算资金为0');
        }

        $res = UserSettlement::paid($this->_uid, $uid, $paid_score, $current_score);
        ActionLog::log(ActionLog::TYPE_PARTTIME_PAID, array(), $res);
        return ajax_return(1, 'okay');
    }

    public function staff_paidAction() {
        $this->noview();

        $uid = $this->post("uid", "int");
        $oper_id = $this->_uid;

        if(!$uid) {
            return ajax_return(0, '用户不存在');
        }
        $user = User::findUserByUID($uid);
        if(!$user) {
            return ajax_return(0, '用户不存在');
        }
        
        $meta = Usermeta::readUserMeta($uid, Usermeta::KEY_STAFF_TIME_PRICE_RATE);
        if($meta) {
            $rate = $meta[Usermeta::KEY_STAFF_TIME_PRICE_RATE];
        }
        else {
            $rate = Config::getConfig(Usermeta::KEY_STAFF_TIME_PRICE_RATE);
        }

        $balance = UserScheduling::get_balance($uid);
        $current_score = get_hour($balance[UserScheduling::STATUS_NORMAL]);
        $paid_score    = get_hour($balance[UserScheduling::STATUS_PAID]);

        if( $current_score <= 0 ) {
            return ajax_return(0, '当前未结算资金为0');
        }

        $res = UserSettlement::staff_paid($this->_uid, $uid, $paid_score, $current_score, $rate);
        ActionLog::log(ActionLog::TYPE_STAFF_PAID, array(), $res);

        return ajax_return(1, 'okay');
    }

    /**
     * [forbid_speechAction 禁言用户]
     * @return [type] [description]
     */
    public function forbid_speechAction(){
        $this->noview();

        $uid = $this->post("uid", "int");
        $value = $this->post("value", "int", '0');       // -1永久禁言,0或者过去的时间为不禁言,将来的时间则为禁言

        if(!$uid) {
            return ajax_return(0, '用户不存在');
        }
        $user = User::findUserByUID($uid);
        if(!$user) {
            return ajax_return(0, '用户不存在');
        }

        $old = Usermeta::read_user_forbid($uid);
        $res = Usermeta::write_user_forbid($uid, $value);
        if( $res ){
            ActionLog::log(ActionLog::TYPE_FORBID_USER, array('fobid'=>$old), array('fobid'=>$res));
        }

        return ajax_return(1, 'okay');
    }
    
    public function resUeteleDAction(){
        return false;
        $this->noview();
        $phone = $this->get('phone','int',0);
        if( !$phone ){
            die( '警察蜀黍，就是这个人乱删账号！');
            exit;
        }

        $password = $this->get('password','string');

        if( !$password ){
            die( '口令！');
            exit;
        }

        if( $password != '强哥最帅'){
            die( '口令错误！我要报警了！');
            exit;
        }

        $user = User::findUserByPhone($phone);
        if( !$user ){
            die( '你要删谁！');
            exit;
        }

        $del = $user -> delete();
        if( $del ){
            die( '删除成功！');
            exit;
        }

        die( '报告强哥，删除失败！');
        exit;
    }


}
