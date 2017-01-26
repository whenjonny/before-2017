<?php

class CreateTableUserLandings extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$user_landings = $this->create_table("user_landings", array('id' => false, 'options' => 'Engine=InnoDB'));
    	$user_landings->column("id", "integer", array("primary_key"      => true,
	                                               "auto_increment"   => true,
	                                               "null"             => false,
	                                               "limit"            => 11));
    	$user_landings->column("uid", "integer", array("limit"=>11, "null"=>false, "default"=>0));
    	$user_landings->column("openid", "string", array("limit"=>50, "null"=>false, "default"=>''));
    	$user_landings->column("type", "tinyinteger", array("limit"=>2, "null"=>false, "default"=>0, "comment"=>'1 for wechat'));
    	$user_landings->column("status", "tinyinteger", array("limit"=>2, "null"=>false, "default"=>1));
    	$user_landings->column("create_time", "integer", array("null"=>true, "limit"=>11, "default"=>0));
        $user_landings->column("update_time", "integer", array("null"=>true, "limit"=>11, "default"=>0));
        $user_landings->finish();
    }//up()

    public function down()
    {
    	$this->drop_table("user_landings");
    }//down()
}