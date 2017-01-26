<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\User,
    Psgod\Models\Usermeta,
    Psgod\Models\Role,
    Psgod\Models\Config,
    Psgod\Models\ActionLog,
    Psgod\Models\Permission,
    Psgod\Models\PermissionRole,
    Psgod\Models\UserRole;

class ConfigController extends ControllerBase
{

    public $config = "";

    public function indexAction()
    {

    }

    public function list_configsAction()
    {
        $rows = Config::data();

        $role = new Config;
        // 检索条件
        $cond = array();
        $cond['id']             = $this->post("role_id", "int");
        
        $cond['create_time']        = $this->post("role_created", "string");
        $cond['update_time']        = $this->post("role_updated", "string");
        $cond['name']           = array(
            "'".implode("','", $rows)."'",
            'IN'
        );

        // 用于遍历修改数据
        $data  = $this->page($role, $cond);

        foreach($data['data'] as $row){
            $config_id = $row->id;
            $row->create_time = date('Y-m-d H:i:s', $row->create_time);
            $row->update_time = date('Y-m-d H:i:s', $row->update_time);
            $row->oper = '<a href="#edit_config" data-toggle="modal" class="edit">编辑</a> ';
        }
        // 输出json
        return $this->output_table($data);
	}

    public function set_configAction(){
        $this->noview();

        $name   = $this->post("name", "string");
        $value  = $this->post("value", "string");

        $oldConfig = Config::findfirst("name='$name'");
        if(is_null($value)){
		    return ajax_return(0, '请输入具体数值');
        }

        $ret = false;
        if($oldConfig){
            $newConfig = Config::setConfig($oldConfig->id, $name, $value);
            ActionLog::log(ActionLog::TYPE_EDIT_CONFIG, $oldConfig, $newConfig);
        }
        
        return ajax_return(1, 'okay');
    }

    public function set_person_rateAction(){
        $this->noview();

        $uid   = $this->post("uid", "int");
        $value = $this->post("value", "float");

        Usermeta::writeUserMeta($uid, Usermeta::KEY_STAFF_TIME_PRICE_RATE, $value);
        return ajax_return(1, 'okay');
    }
}
