<?php

class CreateTableRecords extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$records = $this->create_table("records", array('id' => false, 'options' => 'Engine=InnoDB'));
    	$records->column("id", "biginteger", array("primary_key"      => true, 
	                                               "auto_increment"   => true, 
	                                               "null"             => false,
	                                               "limit"            => 20));
    	$records->column("uid", "integer", array("limit"=>11, "null"=>false));
    	$records->column("type", "tinyinteger", array("limit"=>2, "null"=>false));
    	$records->column("target_id", "biginteger", array("limit"=>20, "null"=>false));
    	$records->column("action", "tinyinteger", array("limit"=>2, "null"=>false));
    	$records->column("status", "tinyinteger", array("limit"=>2, "null"=>false, "default"=>1));
    	$records->column("create_time", "integer", array("null"=>true, "limit"=>11));
        $records->column("update_time", "integer", array("null"=>true, "limit"=>11));
        $records->finish();

    }//up()

    public function down()
    {
    	$this->drop_table("records");
    }//down()
}
