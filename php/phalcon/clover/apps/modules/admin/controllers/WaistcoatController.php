<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\ActionLog;
use Psgod\Models\User;
use Psgod\Models\Config;
use Psgod\Models\Role;
use Psgod\Models\Reply;
use Psgod\Models\UserRole;
use Psgod\Models\Usermeta;
use Psgod\Models\UserScore;
use Psgod\Models\UserSettlement;
use Psgod\Models\UserScheduling;

class WaistcoatController extends ControllerBase
{
    public function initialize() {
        parent::initialize();

        $this->view->roles = Role::find();
    }

    public function indexAction() {
    }

    public function helpAction() {
    }

    public function workAction() {
    }

    public function parttimeAction() {
        $num    = UserRole::count("role_id = ".Role::TYPE_PARTTIME);
        $score  = floor(UserSettlement::sum(array('column'=>'score')));

        $this->set('num', $num);
        $this->set('score', round($score));
    }

    public function staffAction() {
        $this->assets->addCss('theme/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');
        $this->assets->addJs('theme/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js');

        $rate   = Config::getConfig(Usermeta::KEY_STAFF_TIME_PRICE_RATE);
        $num    = UserRole::count("role_id = ".Role::TYPE_STAFF);
        $score  = floor(UserSettlement::sum(array('column'=>'score')));

        $this->set('rate', $rate);
        $this->set('num', $num);
        $this->set('score', $score);
    }

    public function juniorAction() {
    }

    public function list_usersAction()
    {
        $pc_host = $this->config['host']['pc'];

        $user = new User;
        // 检索条件
        $cond = array();
        $cond['role_id']    = $this->get("role_id", "int");
        $uid        = $this->post("uid", "int");
        if( $uid ){
            $cond[get_class($user).'.uid'] = $uid;
        }
        $cond['username']   = array(
            $this->post("username", "string"),
            "LIKE",
            "AND"
        );
        $cond['nickname']   = array(
            $this->post("nickname", "string"),
            "LIKE",
            "AND"
        );
        // $cond['type'] = $this->get("type", "int");
         // 关联表数据结构
        $join = array();
        $join['UserRole'] = 'uid';
        //$join['Role']     = 'role_id';

        $order = 'id DESC';
        $action= new ActionLog();
        $types = UserScheduling::operTypes();

        // 用于遍历修改数据
        $data  = $this->page($user, $cond, $join, $order );

        foreach($data['data'] as $row){
            $row->uid = $row->uid;
            // 兼职员工的换算单位
            // 后台账号的兑换比例
            $row->rate = 1;
            if($cond['role_id'] == Role::TYPE_STAFF){
                $meta = Usermeta::readUserMeta($row->uid, Usermeta::KEY_STAFF_TIME_PRICE_RATE);
                if($meta) {
                    $row->rate = $meta[Usermeta::KEY_STAFF_TIME_PRICE_RATE];
                }
                else
                    $row->rate = Config::getConfig(Usermeta::KEY_STAFF_TIME_PRICE_RATE);
            }
            // 结算金额
            $row->paid_money    = sprintf("%0.1f", UserSettlement::sum(array(
                'column'=>'score',
                'conditions'=> "operate_to=$row->uid"
            )));
            //const STATUS_NORMAL = 0;
            //const STATUS_PAID   = 1;
            //const STATUS_COMPLAIN = 2;
            //const STATUS_DELETED  = 3;
            // 兼职结算的分数
            $balance = UserScore::get_balance($row->uid);
            $row->current_score = $balance[UserScore::STATUS_NORMAL];
            $row->paid_score    = $balance[UserScore::STATUS_PAID];
            $row->total_score   = $row->current_score + $row->paid_score;
            // 员工结算的时间，按天
            $balance = UserScheduling::get_balance($row->uid);
            $row->current_time  = $balance[UserScheduling::STATUS_NORMAL];
            $row->paid_time     = $balance[UserScheduling::STATUS_PAID];
            $row->total_time    = $row->current_time + $row->paid_time;
            // 按小时结算
            $row->current_hour  = get_time($row->current_time);
            $row->paid_hour     = get_time($row->paid_time);
            $row->total_hour    = get_time($row->total_time);
            // 按天结算
            $row->current_day   = get_day($row->current_time);
            $row->paid_day      = get_day($row->paid_time);
            $row->total_day     = get_day($row->total_time);
            // 换算薪资
            $row->hour_money    = number_format(get_money($row->current_time, $row->rate, 'hour'),1);

            $row->create_time = date("Y-m-d H:i", $row->create_time);

            $row->sex = get_sex_name($row->sex);
            $row->ask_count     = $user->get_ask_count($row->uid);
            $row->fans_count    = $user->get_fans_count($row->uid);
            $row->inform_count  = User::get_all_inform_count($row->uid);
            $row->remark        = Usermeta::read_user_remark($row->uid);


            $logs   = $action->get_log($row->uid);

            //$row->passed_replies_count = Reply::count(array('conditions'=>'uid='.$row->uid.' AND status='.Reply::STATUS_NORMAL));
            //$row->rejected_replies_count = Reply::count(array('conditions'=>'uid='.$row->uid.' AND status='.Reply::STATUS_REJECT));
            //$row->total_replies_count = $row->passed_replies_count + $row->rejected_replies_count;


            $row->passed_replies_count = UserScore::count(array('conditions'=>'uid='.$row->uid.' AND type='.UserScore::TYPE_REPLY.' AND content=""'));
            $row->rejected_replies_count =UserScore::count(array('conditions'=>'uid='.$row->uid.' AND type='.UserScore::TYPE_REPLY.' AND content!=""'));
            $row->total_replies_count = $row->passed_replies_count + $row->rejected_replies_count;

            foreach($types as $key=>$type){
                if(!isset($type_arr[$key])) $row->$key = 0;
                foreach($logs as $log) {
                    if(in_array($log->oper_type, $type)){
                        $row->$key += $log->num;
                    }
                }
            }

            $score_cond = array('column'=>'score','conditions'=>'oper_by='.$row->uid);
            //总审分
            $row->total_score = 0;
            $total_score = UserScore::sum($score_cond);
            if( $total_score ){
                $row->total_score = $total_score;
            }

            //平均审分
            $avg_score = UserScore::average($score_cond);
            $row->avg_score = number_format($avg_score,1);

            //平均得分
            $avg_points = UserScore::average(array('column'=>'score','conditions'=>'uid='.$row->uid));
            $row->avg_points = number_format($avg_points,1);

            $row->rate = "<input class='form-control' type='text' value='".$row->rate."' /><button data='$row->uid' type='submit' class='form-control rate_save'>保存</button>";
            $row->data = '<a href="#remark_user" data-toggle="modal" class="remark" remark="'.$row->remark.'" uid="'.$row->uid.'" nickname="'.$row->nickname.'">备注</a> '.
                "<a target='_blank' href='http://$pc_host/user/profile/{$row->uid}' class='detail'>详情</a> ";
            $row->avatar = '<img class="user-portrait" src='.$row->avatar.' alt="头像">';
            $row->money = "<a uid='".$row->uid."' class='paid'>结算资金</a> <a uid='".$row->uid."' class='paid_list'>结算记录</a>";
            $row->set_time =    '<a href="#add_user_schedule" style="color:green" data-toggle="modal" class="set_time" uid="'.$row->uid.'" nickname="'.$row->nickname.'">设置</a> '.
            '<a href="/scheduling/index?uid='.$row->uid.'">查看</a> ';
        }

        // 输出json
        return $this->output_table($data);
    }

    public function create_userAction() {
        $this->noview();

        $username = $this->post("username", "string");
        $password = $this->post("password", "string");
        $nickname = $this->post("nickname", "string");
        $sex      = $this->post("sex", "int");
        $phone    = 19000000000;//mt_rand(19000000000,19999999999);//$this->post("phone", "int");
        $avatar   = $this->post("avatar", "string");
        $role_id  = $this->post("role_id", "int");

        if(is_null($username) || is_null($password) || is_null($nickname)  || is_null($sex)){
            return ajax_return(0, '请输入角色名称或展示名称');
        }

        if(User::count("username='$username'") > 0){
            return ajax_return(0, '用户已存在');
        }
        if(User::count("nickname='$nickname'") > 0){
            return ajax_return(0, '该昵称已被注册');
        }

        $phone += User::count();
        $user = User::addNewUser($username,$password,$nickname, $phone, 0, "", $avatar, $sex);
        if(!$user || !isset($user->uid)){
            return ajax_return(0, '保存失败'.$user->getMessages());
        }
        ActionLog::log(ActionLog::TYPE_REGISTER, array(), $user);

        $role = UserRole::addNewRelation($user->uid, $role_id);
        ActionLog::log(ActionLog::TYPE_ASSIGN_ROLE, array(), $role);
        return ajax_return(1, 'okay');
    }

    public function remarkAction(){
        $this->noview();
        $id         = $this->post('id');
        $nick       = $this->post('name');
        $password   = $this->post('password');
        $is_reset   = $this->post('is_reset');
        $remark     = trim($this->post('remark'));
        if(is_null($id)){
            ajax_return(0, '参数错误');
        }
        $u = User::findFirst(array("uid = $id"));
        $old = ActionLog::clone_obj( $u );
        if($nick) $u->nickname = $nick;
        if($remark){
            $oldRemark = Usermeta::read_user_remark( $u->uid );
            Usermeta::write_user_remark($u->uid, $remark);
            ActionLog::log(ActionLog::TYPE_MODIFY_REMARK, array('remark'=>$oldRemark), array('remark'=>$remark) );
        }

        if(isset($is_reset) and $is_reset){
            $u->password = User::hash($password);
        }

        $u = $u->save_and_return($u);
        if($u){
            ActionLog::log(ActionLog::TYPE_MODIFY_USER_INFO, $old, $u);
            return ajax_return(1, 'ok');
        }
        else
            return ajax_return(0, 'err');
    }

    public function set_timeAction(){
        $this->noview();
        if( !$this->request->isAjax() ){
            return array();
        }

        $uid = $this->post('uid', 'int', 0);
        $start_time = $this->post('start_time', 'int', 0);
        $end_time = $this->post('end_time', 'int', 0);
        if( $start_time < time() ){
            return ajax_return(5, '不能设置过去的时间');
        }
        if( $start_time > $end_time ){
            return ajax_return(4,'开始时间不能晚于结束时间');
        }
        if( ($end_time - $start_time) - 24 * 60 * 60 > 0 ) {
            return ajax_return(4,'工作时间不能超过12h');
        }

        $schedule = new UserScheduling();
        $schedule->uid = $uid;
        $schedule->start_time = $start_time;
        $schedule->end_time = $end_time;
        $schedule->set_by = $this->_uid;
        $schedule->del_by = 0;
        $schedule->del_time = 0;
        $schedule->create_time = time();
        $schedule->update_time = time();
        $schedule->status = UserScheduling::STATUS_NORMAL;

        $data = $schedule->save_and_return($schedule);
        if( $data ){
            ActionLog::log(ActionLog::TYPE_SET_STAFF_TIME, array(), $data);
            return ajax_return(1,'添加成功');
        }
        else{
            return ajax_return(2,'添加失败');
        }
    }
}
