<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\Ask;
use Psgod\Models\User;
use Psgod\Models\Usermeta;

class DeployController extends ControllerBase
{

    public function indexAction()
    {
        $this->set('count', count($users)); //User::count());
    }

    public function testAction(){
        $user = User::find();
        pr($user->toArray());
    }

	public function list_deploysAction()
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
        // $cond['type'] = $this->get("type", "int");
         // 关联表数据结构
        $join = array();
        $join['Ask'] = 'uid';
        $join['Reply'] = 'uid';
        
        // 用于遍历修改数据
        $data  = $this->page($user, $cond ,$join);
        
        foreach($data['data'] as $row){ 
            $row->username = '<a class="edit display-block color-red">'.$row->username.'<input  class="contant form-control" placeholder="填写内容" >'.'</a><a class="edit display-block">倒计时12分钟</a>';
            //value='.$row->ask_detail.'
            $row->thumb_url = '<a class="edit display-block">valet</a>'.'<input  class="contant form-control" placeholder="填写内容" /><a class="edit display-block">倒计时12分钟</a>'.$row->thumb_url;
            $row->check = '<a class="edit">查看帖子</a>';
        }
        // 输出json
        return $this->output_table($data);
    }
}
