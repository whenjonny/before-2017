<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\Comment;
use Psgod\Models\User;

class CommentController extends ControllerBase
{

    public function indexAction()
    {

    }

    public function list_commentsAction()
    {
        // 检索条件
        $cond = array();
        // 获取model
        $comment = new Comment;
        $cond[get_class($comment).'.uid'] = $this->post('uid');
        $cond[get_class(new User).'.username']   = array(
            $this->post("username", "string"),
            "LIKE",
            "AND"
        );
        $cond['content']   = array(
            $this->post("content", "string"),
            "LIKE",
            "AND"
        );
        $join = array();
        $join['User'] = 'uid';

        $data  = $this->page($comment, $cond, $join);
        foreach($data['data'] as $row){
            $row->id =  $row->id;
            $row->uid = "评论用户ID:" . $row->uid;
            $row->oper = "<a class='edit'>编辑</a> <a class='delete'>删除</a>";
        }
        // 输出json
        return $this->output_table($data);
    }


    public function delete_comment()
    {
        // 获取model
        $comment = new Comment;
        // 检索条件
        $cond = array();
        $cond['cid']        = $this->post("cid", "int");
        $cond['comment_id']   = array(
            $cond['cid']
        );
        $data  = $this->page($user, $cond);
    }

}
