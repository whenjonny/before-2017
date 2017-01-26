<?php

class CreateTableFeedback extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$feedback = $this->create_table("feedback", array('id' => false, 'options' => 'Engine=InnoDB'));
    	$feedback->column("id", "integer", array("primary_key"      => true, 
	                                             "auto_increment"   => true, 
	                                             "null"             => false,
	                                             "limit"            => 11));
    	$feedback->column("uid", "biginteger", array("limit"=>20, "null"=>false));
    	$feedback->column("content", "text", array("null"=>false));
    	$feedback->column("contact", "string", array("limit"=>30, "null"=>false));
    	$feedback->column("create_time", "integer", array("limit"=>11, "null"=>false));
    	$feedback->column("status", "string", array("limit"=>30, "null"=>false));
    	$feedback->column("del_time", "integer", array("null"=>true, "limit"=>11));
        $feedback->column("del_by", "integer", array("null"=>true, "limit"=>20));
        $feedback->finish();
    }//up()

    public function down()
    {
    	$this->drop_table("feedback");
    }//down()
}