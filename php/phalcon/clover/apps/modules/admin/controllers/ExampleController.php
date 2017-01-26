<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\User;
use Psgod\Models\Usermeta;

class ExampleController extends ControllerBase
{

    public function tableAction()
    {

    }

    public function list_usersAction()
    {

        $user = new User;
        // 检索条件
        $cond = array();
        $cond['uid']        = $this->post("uid", "int");
        $cond['username']   = array(
            $this->post("username", "string"),
            "LIKE",
            "AND"
        );
        // 关联表数据结构
        $join = array();
        $join['Usermeta'] = 'uid';

        // 用于遍历修改数据
        $data  = $this->page($user, $cond, $join);
        foreach($data['data'] as $row){
            $row->username = "用户名:" . $row->username;
            $row->oper = "";//"<button class='edit'>编辑</button>";
        }
        // 输出json
        return $this->output_table($data);
    }
   
}
