<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\User;
use Psgod\Models\Count;
use Psgod\Models\Usermeta;
use Psgod\Models\Ask;
use Psgod\Models\Master;
use Psgod\Models\ActionLog;

class PersonalController extends ControllerBase
{

    public function indexAction()
    {

    }

    public function created_userAction(){

    }

    public function list_created_usersAction(){
        $actionLog = new ActionLog();
        $ownerUid = $this->get('creator','int', $this->_uid);

        $created_user = $actionLog->get_log_by_uid_and_oper_type( $ownerUid, ActionLog::TYPE_REGISTER )->toArray();
        $usersDiff = array_column( $created_user, 'data');
        array_walk( $usersDiff, function( &$value ){
            $value = json_decode($value, true);
        });
        $uids = array_column( $usersDiff, 'uid' );

        $user = new User;
        $cond = array();
        $findUid = $this->post("uid", "int");
        if( in_array( $findUid, $uids ) ){
            $cond['uid'] = array( $findUid, 'IN');
        }
        else{
            $cond['uid'] = array( implode(',',$uids), 'IN' );
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
        $cond['phone']   = array(
            $this->post("phone", "string"),
            "LIKE",
            "AND"
        );

        $_REQUEST['sort'] = "create_time desc";


        $data  = $this->page($user, $cond, array(), 'uid DESC');
        foreach($data['data'] as $row){
            $uid = $row->uid;
            $row->sex = get_sex_name($row->sex);
            $row->avatar = $row->avatar ? '<img class="user-portrait" src="'.$row->avatar.'" />':'无头像';
            $row->create_time = date('Y-m-d H:i', $row->create_time);
            $creator = User::findUserByUID($ownerUid);
            $row->creator = $creator->username;

        }
        // 输出json
        return $this->output_table($data);
    }

    public function list_usersAction(){
    	$user = new User;
        $cond = array();
        $cond['uid']        = $this->post("uid", "int");
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

        $_REQUEST['sort'] = "create_time desc";

        $data  = $this->page($user, $cond, array(), 'uid DESC');
        foreach($data['data'] as $row){
            $uid = $row->uid;
            $row->sex = get_sex_name($row->sex);
            $row->avatar = $row->avatar ? '<img class="user-portrait" src="'.$row->avatar.'" />':'无头像';
            $row->create_time = date('Y-m-d H:i', $row->create_time);
            $row->download_count=$user->get_download_count($uid);
            $row->asks_count = $user->get_ask_count($uid);
            $row->replies_count = $user->get_reply_count($uid);
            $row->inprogress_count = $user->get_inprogress_count($uid);
            $row->upload_count=$user->get_upload_count($uid);
            $row->total_inform_count = $user->get_all_inform_count($uid);
            $counts = Count::get_counts_by_uid($uid);
            $row->share_count=$counts[Count::ACTION_SHARE];
            $row->wxshare_count=$counts[Count::ACTION_WEIXIN_SHARE];
            $row->friend_share_count="辣么任性";
            $row->comment_count=$user->get_comment_count($uid);
            $row->focus_count = $user->get_focus_count($uid);
            $row->fans_count = $user->get_fans_count($uid);
            $row->fellow_count = $user->get_fellow_count($uid);
            $row->oper   = "<a class='edit'>编辑</a>";
            $time = Usermeta::read_user_forbid($uid);
            if($time != -1 and ($time == "" || $time < time())) {
                $row->forbid = "<a class='forbid' data='-1' uid='$uid'>禁言</a>";
            }
            else {
                $row->forbid = "<a class='forbid' data='0' uid='$uid'>解禁</a>";
            }
            $row->assign = '<a href="#assign_role" data-toggle="modal" class="assign" data-uid="'.$uid.'">赋予角色</a>';
            $master_oper_name = ($row->is_god==0)?'设置':'取消';
            $row->master = '<a href="#" class="master" data-uid="'.$uid.'">'.$master_oper_name.'</a>';
        }
        // 输出json
        return $this->output_table($data);
    }

    public function set_masterAction(){
        $this->noview();
        if( !$this->request->isAjax() ){
            return ;
        }

        $uid = $this->post('uid', 'int', 0);
        $user = User::findFirst($uid);
        if( !$user ){
            return false;
        }
        $old = ActionLog::clone_obj($user);

        $save = $user->set_master($uid);
        if($save){
            ActionLog::log(ActionLog::TYPE_ADD_RECOMMEND, $old, $save);
            return ajax_return(1, '设置成功');
        }
        else{
            return ajax_return(2, '设置失败');
        }
    }

}
