<?php

class CreateTableRecommendApp extends Ruckusing_Migration_Base
{
    public function up()
    {
    	/*$recommend_app = $this->create_table("recommend_app", array('id' => false, 'options' => 'Engine=InnoDB'));
    	$recommend_app->column("id", "integer", array("primary_key"      => true, 
	                                             "auto_increment"   => true, 
	                                             "null"             => false,
	                                             "limit"            => 11));
    	$recommend_app->column("app_name", "string", array("limit"=>255, "null"=>false));
    	$recommend_app->column("logo_upload_id", "biginteger", array("limit"=>20, "null"=>false));
    	$recommend_app->column("jumpurl", "string", array("limit"=>255, "null"=>false));
    	$recommend_app->column("order_by", "smallinteger", array("limit"=>6, "null"=>false));
    	$recommend_app->column("create_time", "integer", array("limit"=>11, "null"=>false));
    	$recommend_app->column("create_by", "biginteger", array("limit"=>20, "null"=>false));
    	$recommend_app->column("del_time", "integer", array("null"=>true, "limit"=>11));
        $recommend_app->column("del_by", "biginteger", array("null"=>true, "limit"=>20));
        $recommend_app->finish();*/
    }//up()

    public function down()
    {
    	$this->drop_table("recommend_app");
    }//down()
}
