<?php

class CreateTableSysMsg extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$sysMsg = $this->create_table("sys_msgs", array('id' => true, 'options' => 'Engine=InnoDB'));
    	$sysMsg->column("title", "string", array("limit"=>100, "null"=>false,"comment"=>'消息标题'));
    	$sysMsg->column("target_type", "tinyinteger", array("limit"=>2, "null"=>false));
    	$sysMsg->column("target_id", "integer", array("limit"=>11, "null"=>false, "default"=>0, "comment"=>'目标id,跳转url时为0'));
        $sysMsg->column("jump_url", "string", array("limit"=>1000, "null"=>false, "default"=>'', "comment"=>'跳转时的url'));
        $sysMsg->column("post_time", "integer", array("limit"=>11, "null"=>false, "comment"=>"推送时间"));
        $sysMsg->column("receiver_uids", "string", array("limit"=>5000, "null"=>false, "default"=>'0', "comment"=>"接收者用户id列表，逗号分隔"));
    	$sysMsg->column("status", "tinyinteger", array("limit"=>2, "null"=>false, "default"=>1));
        $sysMsg->column("create_time", "integer", array("limit"=>11, "null"=>false ));
    	$sysMsg->column("create_by", "biginteger", array("limit"=>20, "null"=>false ));
        $sysMsg->column("update_time", "integer", array("limit"=>11, "null"=>false ));
        $sysMsg->finish();
    }//up()

    public function down()
    {
    	$this->drop_table("sys_msgs");
    }//down()
}
