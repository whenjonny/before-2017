<?php

class CreateTableInform extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$inform = $this->create_table("informs", array('id' => false, 'options' => 'Engine=InnoDB','comment'=>'用户举报记录'));
    	$inform->column("id", "integer", array("primary_key"      => true,
	                                             "auto_increment"   => true,
	                                             "null"             => false,
	                                             "limit"            => 11));
        $inform->column("uid", "biginteger", array("limit"=>20, "null"=>false));
        $inform->column("target_type", "tinyinteger", array("limit"=>2, "null"=>false));
    	$inform->column("target_id", "biginteger", array("limit"=>20, "null"=>false));
    	$inform->column("content", "string", array("null"=>false,"limit"=>5000, "comment"=>'举报内容'));
    	$inform->column("create_time", "integer", array("limit"=>11, "null"=>false,'comment'=>'举报时间'));
    	$inform->column("status", "tinyinteger", array("limit"=>2, "null"=>false));
    	$inform->column("oper_time", "integer", array("null"=>true, "limit"=>11,'comment'=>'处理时间'));
        $inform->column("oper_by", "biginteger", array("null"=>true, "limit"=>20, 'comment'=>'处理者'));
        $inform->finish();
    }//up()

    public function down()
    {
    	$this->drop_table("informs");
    }//down()
}
