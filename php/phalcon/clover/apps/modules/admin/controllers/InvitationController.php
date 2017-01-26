<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\Ask; 
use Psgod\Models\User;
use Psgod\Models\Usermeta;

class InvitationController extends ControllerBase
{

    public function workAction(){

    }

    public function helpAction() {

    }

    public function delhelpAction() {

    }

    public function delworkAction() {

    }

    public function completeAction() {

    }

    public function listAction() {
// 获取model
        $asks = new Ask;
        // 检索条件
        $cond = array();
        $join = array();
        $join['User'] = 'uid';
        $data  = $this->page($asks, $cond, $join);

        foreach($data['data'] as $row){
            $row->id =  $row->id;
            $row->uid = "评论用户ID:" . $row->uid;
            $row->total_share = '数据库没这个字段';//$row ->; 
            $row->sex = get_sex_name($row -> sex);
            $row->status = ($row -> status) ? "已处理":"未处理";
            $row->create_time = date('m-d H:i', $row->create_time);
        //     $row->oper = "<button class='edit'>编辑</button>"."<button class='delete'>删除</button>";
        }
        return $this->output_table($data);
    }
}

