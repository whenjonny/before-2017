<?php

namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class ActionLog extends ModelBase
{
    private $table_prefix = 'action_log_';

    public function initialize()
    {
        parent::initialize();
        $this->setConnectionService('db_log');
    }

    public function getSource() {
        return $this->get_table();
    }

    public function get_log_by_uid_and_oper_type($uid, $type = array(), $start_time = 0, $end_time = 99999999999  ){
        $table = $this->get_table( $uid );
        $typeCond = '';
        if( $type ){
            if( is_array( $type ) ){
                $typeCond = ' AND oper_type IN('.implode(',', $type ).')';
            }
            else{
                $typeCond = ' AND oper_type = '.$type;
            }
        }

        $log = new self;
        $sql = 'select *'.
            ' FROM '.$table.' t'.
            ' WHERE t.uid ='.$uid.' AND create_time>'.$start_time.' AND create_time<'.$end_time.$typeCond;

        return $logs = new Resultset(null, $log, $log->getReadConnection()->query($sql));
    }

    public function get_log($uid, $start_time = 0, $end_time = 99999999999, $type = array() ){
        $table = $this->get_table( $uid );
        $typeCond = '';
        if( $type ){
            $typeCond = 'oper_type IN('.implode(',', $type ).')';
        }

        $log = new self;
        $sql = 'select t.oper_type, count(1) as num'.
            ' FROM '.$table.' t'.
            ' WHERE t.uid ='.$uid.' AND create_time>'.$start_time.' AND create_time<'.$end_time.
            ' GROUP BY t.oper_type';

        return $logs = new Resultset(null, $log, $log->getReadConnection()->query($sql));
        /*
        //todo: 变成键值对,方便后续处理
        $data = array();
        foreach($logs as $log){
            $data[$log->oper_type] = $log->num;
        }
        return $data;
         */
    }

    public static function log($type = self::TYPE_OTHERS, $old_obj = array(), $new_obj = array(), $info = '') {
        $log = new self;
        $log->uid   = _uid();
        $log->data  = json_encode(self::diff($old_obj, $new_obj));
        $log->info  = $info;
        $log->uri   = isset($_SERVER['REQUEST_URI'])? $_SERVER['REQUEST_URI']: '';
        $log->ip    = ip2long(isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR']: '');
        $log->oper_type   = $type;
        $log->create_time = time();

        return $log->save_and_return($log);
    }

    public static function diff( $old_obj, $new_obj ){
        //判断是否相同的instance
        $diff = new \stdClass;


        if( empty($old_obj) && $new_obj ){
            if( is_object($new_obj) ){
                $diff->__class = get_class($new_obj);
                $obj = clone $new_obj;
                $new_obj = new $diff->__class;
                $old_obj = $obj;
            }
            else if( is_array( $new_obj ) ){
                $old_obj = array();
            }
            else{
                return false;
            }
        }
        else if( empty($new_obj) && $old_obj ){
            if( is_object($old_obj)){
                $diff->__class = get_class($old_obj);
                $new_obj = new $diff->__class;
            }
            else if( is_array($old_obj) ){
                $new_obj = array();
            }
            else{
                return false;
            }
        }

        if( is_array( $old_obj ) && is_array($new_obj) ){
            $diff->__class = 'array';
        }
        else if( is_object($old_obj) && is_object($new_obj) ){
            $diff->__class = get_class($old_obj);
        }
        else{
            return false;
        }

        foreach($old_obj as $key=>$val){
            // 如果含有id字段的都可以加入作为记录
            if(!isset($new_obj->$key)){
				$diff->$key = $val;
            }
            else if($new_obj->$key != $val){
				$diff->$key = array($val, $new_obj->$key);
            }
            if(in_array($key, array("id", "_uid", "uid", "__class"))){
				$diff->$key = $val;
            }
		}

		return $diff;
	}

    private function get_table( $uid = 0){
        if( $uid ==  0 ){
            $uid = _uid();
        }
        if(  !$uid  ){
            $uid = 0;
        }
        $uid_mod = $uid %100;
        return $this->table_prefix.str_pad($uid_mod, 2, '0', STR_PAD_LEFT);
    }

    public static function clone_obj( $obj ){
        return clone $obj;
    }


    const TYPE_OTHERS  = 0; //其他
    const TYPE_LOGIN   = 0x1; //账户登录
    const TYPE_LOGOUT  = 0x2; //账户登出
    const TYPE_REGISTER= 0x3; //用户注册

    const TYPE_POST_ASK     = 0x4; //发布求助
    const TYPE_POST_REPLY   = 0x5; //发布作品
    const TYPE_DELETE_ASK   = 0x6; //删除求助
    const TYPE_DELETE_REPLY = 0x7; //删除作品
    const TYPE_VERIFY_ASK   = 0x8; //审核求助
    const TYPE_VERIFY_REPLY = 0x9; //审核作品
    const TYPE_REJECT_ASK   = 0x10; //审核失败求助
    const TYPE_REJECT_REPLY = 0x11; //审核失败作品
    const TYPE_RECOVER_ASK  = 0x12; //恢复求助
    const TYPE_RECOVER_REPLY= 0x13; //恢复作品

    const TYPE_ADD_HELPER   = 0x14; //添加求助账号
    const TYPE_ADD_WORKER   = 0x15; //添加大神账号
    const TYPE_ADD_PARTTIME = 0x16; //添加兼职账号
    const TYPE_ADD_STAFF    = 0x17; //添加后台账号
    const TYPE_ADD_JUNIOR   = 0x18; //添加初级账号

    const TYPE_MODIFY_REMARK   = 0x19; //修改备注
    const TYPE_CHANGE_PASSWORD = 0x20; //修改密码
    const TYPE_MODIFY_USER_INFO = 0x21; //修改用户信息

    const TYPE_PARTTIME_PAID= 0x22; //兼职结算
    const TYPE_STAFF_PAID   = 0x23; //后台结算
    const TYPE_JUNIOR_PAID  = 0x24; //初级账号结算

    const TYPE_ADD_ROLE     = 0x25; //添加角色项
    const TYPE_EDIT_ROLE    = 0x26; //编辑角色项
    const TYPE_ASSIGN_ROLE  = 0x27; //赋予角色
    const TYPE_REVOKE_ROLE  = 0x28; //撤销角色
    const TYPE_UPDATE_PERMISSION    = 0x29; //更新角色权限项
    const TYPE_EDIT_PERMISSION      = 0x30; //更新权限项
    const TYPE_ADD_PERMISSION       = 0x31; //增加权限项
    const TYPE_DELETE_PERMISSION    = 0x32; //删除权限项
    const TYPE_GRANT_PRIVILEGE      = 0x33;  //赋予权限
    const TYPE_REVOKE_PRIVILEGE     = 0x34; //撤销权限

    const TYPE_ADD_RECOMMEND    = 0x35; //添加推荐大神
    const TYPE_SET_RECOMMEND    = 0x36; //设置推荐大神时间
    const TYPE_CANCEL_RECOMMEND = 0x37; //取消推荐大神

    const TYPE_INFORM_PROCESSING    = 0x38; //投诉处理

    const TYPE_VERIFY_COMMENT   = 0x39; //审核评论
    const TYPE_POST_COMMENT     = 0x40; //添加评论
    const TYPE_EDIT_COMMENT     = 0x41; //编辑评论
    const TYPE_DELETE_COMMENT   = 0x42; //删除评论

    const TYPE_POST_SYSTEM_MESSAGE    = 0x43; //发布系统消息
    const TYPE_DELETE_SYSTEM_MESSAGE  = 0x44; //发布系统消息

    const TYPE_ADD_APP    = 0x45;    //新增推荐App
    const TYPE_DELETE_APP = 0x46;    //删除推荐App

    const TYPE_ADD_FEEDBACK    = 0x47;  //新增反馈
    const TYPE_DELETE_FEEDBACK = 0x48;  //删除反馈
    const TYPE_NOTE_FEEDBACK   = 0x49;  //给反馈添加纪录
    const TYPE_MODIFY_FEEDBACK_STATUS = 0x50;    //修改状态

    const TYPE_SET_STAFF_TIME = 0x51;   //设置后台账号登陆时间
    const TYPE_FORBID_USER    = 0x52;   //设置用户禁言

    const TYPE_UPLOAD_FILE  = 0x53;  //上传文件

    const TYPE_REPORT_ABUSE = 0x54;  //新增举报
    const TYPE_DEAL_INFORM  = 0x55;  //处理举报

    const TYPE_SET_SCHEDULE    = 0x56; //设置上班时间
    const TYPE_OFF_DUTY        = 0x57; //设置下班
    const TYPE_DELETE_SCHEDULE = 0x58; //删除上班设置

    const TYPE_PUSH_UMENG   = 0x59;  //推送消息
    const TYPE_REMARK_USER  = 0x60; //备注用户

    const TYPE_DELETE_REVIEW= 0x61; //删除review
    const TYPE_ADD_REMARK   = 0x62; //修改备注

    //MAIN
    const TYPE_UP_ASK     = 0x63; //点赞求助
    const TYPE_FOCUS_ASK  = 0x64; //关注求助
    const TYPE_INFORM_ASK = 0x65; //举报求P
    const TYPE_CANCEL_UP_ASK     = 0x68; //取消点赞求助
    const TYPE_CANCEL_FOCUS_ASK  = 0x69; //取消关注求助
    const TYPE_CANCEL_INFORM_ASK = 0x70; //取消举报求P

    const TYPE_INVITE_FOR_ASK        = 0x66; //邀请求助
    const TYPE_CANCEL_INVITE_FOR_ASK = 0x71; //邀请求助

    const TYPE_ADDED_LABEL = 0x67; //添加标签

    const TYPE_BIND_ACCOUNT   = 0x72;  //绑定
    const TYPE_UNBIND_ACCOUNT = 0x73; //解绑

    const TYPE_UP_COMMENT        = 0x74; //点赞评论
    const TYPE_CANCEL_UP_COMMENT = 0x75; //取消点赞评论
    const TYPE_INFORM_COMMENT        = 0x75; //举报评论
    const TYPE_CANCEL_INFORM_COMMENT = 0x76; //取消举报评论

    const TYPE_DELETE_MESSAGES = 0x77; //删除消息

    const TYPE_UP_REPLY        = 0x78; //点赞作品
    const TYPE_CANCEL_UP_REPLY = 0x79; //取消点赞作品
    const TYPE_COLLECT_REPLY        = 0x80; //收藏作品
    const TYPE_CANCEL_COLLECT_REPLY = 0x81; //取消收藏作品
    const TYPE_INFORM_REPLY = 0x82; //举报作品

    const TYPE_NEW_DEVICE = 0x83; //注册新设备
    const TYPE_USER_CHANGE_DEVICE = 0x84; //用户更换设备登陆

    const TYPE_FOLLOW_USER = 0x85; //关注用户
    const TYPE_UNFOLLOW_USER = 0x86; //取消关注用户
    const TYPE_RESET_PASSWORD = 0x87; //重置密码
    const TYPE_USER_DOWNLOAD = 0x88; //下载
    const TYPE_USER_MODIFY_PUSH_SETTING = 0x89; //修改推送设置

    //PC
    const TYPE_DELETE_DOWNLOAD = 0x90; //删除进行中
    const TYPE_SHARE_ASK = 0x91; //分享求助
    const TYPE_SHARE_REPLY = 0x92; //分享作品
    const TYPE_EDIT_CONFIG = 0x93;

    const TYPE_RECOVER_SCHEDULE = 0x94; //恢复排班
    //current type count : 94

    public function data(){
        return array(
            self::TYPE_OTHERS  => '其他',
            self::TYPE_LOGIN   => '账户登录',
            self::TYPE_LOGOUT  => '账户登出',
            self::TYPE_REGISTER=> '用户注册',
            self::TYPE_POST_ASK     => '发布求助',
            self::TYPE_POST_REPLY   => '发布作品',
            self::TYPE_DELETE_ASK   => '删除求助',
            self::TYPE_DELETE_REPLY => '删除作品',
            self::TYPE_VERIFY_ASK   => '审核求助',
            self::TYPE_VERIFY_REPLY => '审核作品',
            self::TYPE_RECOVER_ASK  => '恢复求助',
            self::TYPE_RECOVER_REPLY=> '恢复求助',
            self::TYPE_ADD_HELPER   => '添加求助账号',
            self::TYPE_ADD_WORKER   => '添加大神账号',
            self::TYPE_ADD_PARTTIME => '添加兼职账号',
            self::TYPE_ADD_STAFF    => '添加后台账号',
            self::TYPE_ADD_JUNIOR   => '添加初级账号',
            self::TYPE_ADD_REMARK   => '修改备注',
            self::TYPE_PARTTIME_PAID=> '兼职结算',
            self::TYPE_STAFF_PAID   => '后台结算',
            self::TYPE_JUNIOR_PAID  => '初级账号结算',
            self::TYPE_ADD_ROLE     => '添加角色',
            self::TYPE_EDIT_ROLE    => '编辑角色',
            //self::TYPE_UPDATE_PRIVILEGE => '更新角色权限',
            //self::TYPE_EDIT_PRIVILEGE   => '更新权限',
            //self::TYPE_ADD_PRIVILEGE    => '更新权限',
            self::TYPE_ADD_RECOMMEND    => '添加推荐大神',
            self::TYPE_SET_RECOMMEND    => '设置推荐大神时间',
            self::TYPE_INFORM_PROCESSING=> '投诉处理',
            self::TYPE_VERIFY_COMMENT   => '审核评论',
            self::TYPE_POST_COMMENT     => '添加评论',
            self::TYPE_EDIT_COMMENT     => '编辑评论',
            self::TYPE_DELETE_COMMENT   => '删除评论',
            self::TYPE_POST_SYSTEM_MESSAGE    => '发布系统消息',
            self::TYPE_DELETE_SYSTEM_MESSAGE  => '删除系统消息',
            self::TYPE_ADD_APP                => "新增推荐App",
            self::TYPE_DELETE_APP             => "删除推荐App",
            self::TYPE_ADD_FEEDBACK           => "新增反馈",
            self::TYPE_DELETE_FEEDBACK        => "删除反馈",
            self::TYPE_NOTE_FEEDBACK          => "给反馈添加纪录",
            self::TYPE_MODIFY_FEEDBACK_STATUS => "修改状态",
            self::TYPE_DELETE_PERMISSION      => "删除权限项",
            self::TYPE_ASSIGN_ROLE            => "赋予角色",
            self::TYPE_REVOKE_ROLE            => "撤销角色",
            self::TYPE_GRANT_PRIVILEGE        => "赋予权限",
            self::TYPE_REVOKE_PRIVILEGE       => "撤销权限",
            self::TYPE_SET_STAFF_TIME         => "设置后台账号登陆时间",
            self::TYPE_FORBID_USER            => "设置用户禁言",
            self::TYPE_UPLOAD_FILE            => "上传文件",
            self::TYPE_REPORT_ABUSE           => "新增举报",
            self::TYPE_DEAL_INFORM            => "处理举报",
            self::TYPE_SET_SCHEDULE           => "设置上班时间",
            self::TYPE_OFF_DUTY               => "设置下班",
            self::TYPE_DELETE_SCHEDULE        => "删除上班设置",
            self::TYPE_PUSH_UMENG             => "推送消息",
            self::TYPE_REMARK_USER            => "备注用户",
            self::TYPE_MODIFY_USER_INFO       => "修改用户信息",
            self::TYPE_DELETE_REVIEW          => "删除review",
            self::TYPE_ADD_REMARK              => "修改备注",
            self::TYPE_UP_ASK                  => "点赞求助",
            self::TYPE_FOCUS_ASK               => "关注求助",
            self::TYPE_INFORM_ASK              => "举报求P",
            self::TYPE_CANCEL_UP_ASK           => "取消点赞求助",
            self::TYPE_CANCEL_FOCUS_ASK        => "取消关注求助",
            self::TYPE_CANCEL_INFORM_ASK       => "取消举报求P",
            self::TYPE_INVITE_FOR_ASK          => "邀请求助",
            self::TYPE_CANCEL_INVITE_FOR_ASK   => "邀请求助",
            self::TYPE_ADDED_LABEL             => "添加标签",
            self::TYPE_BIND_ACCOUNT            => "绑定",
            self::TYPE_UNBIND_ACCOUNT          => "解绑",
            self::TYPE_UP_COMMENT              => "点赞评论",
            self::TYPE_CANCEL_UP_COMMENT       => "取消点赞评论",
            self::TYPE_INFORM_COMMENT          => "举报评论",
            self::TYPE_CANCEL_INFORM_COMMENT   => "取消举报评论",
            self::TYPE_POST_INFORM             => "添加举报（带举报内容",
            self::TYPE_DELETE_MESSAGES         => "删除消息",
            self::TYPE_UP_REPLY                => "点赞作品",
            self::TYPE_CANCEL_UP_REPLY         => "取消点赞作品",
            self::TYPE_COLLECT_REPLY           => "收藏作品",
            self::TYPE_CANCEL_COLLECT_REPLY    => "取消收藏作品",
            self::TYPE_INFORM_REPLY            => "举报作品",
            self::TYPE_NEW_DEVICE              => "注册新设备",
            self::TYPE_USER_CHANGE_DEVICE      => "用户更换设备登陆",
            self::TYPE_FOLLOW_USER             => "关注用户",
            self::TYPE_UNFOLLOW_USER           => "取消关注用户",
            self::TYPE_RESET_PASSWORD          => "重置密码",
            self::TYPE_USER_DOWNLOAD           => "下载",
            self::TYPE_USER_MODIFY_PUSH_SETTING=> "修改推送设置",
            self::TYPE_DELETE_DOWNLOAD  => "删除进行中",
            self::TYPE_SHARE_ASK        => "分享求助",
            self::TYPE_SHARE_REPLY      => "分享作品",
            self::TYPE_RECOVER_SCHEDULE => "恢复排班"
        );
    }
}
