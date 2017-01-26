<?php

class CreateTableCounts extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$counts = $this->create_table("counts", array('id' => false, 'options' => 'Engine=InnoDB'));
    	$counts->column("id", "biginteger", array("primary_key"      => true, 
	                                               "auto_increment"   => true, 
	                                               "null"             => false,
	                                               "limit"            => 20));
    	$counts->column("uid", "integer", array("limit"=>11, "null"=>false));
    	$counts->column("type", "tinyinteger", array("limit"=>2, "null"=>false));
    	$counts->column("target_id", "biginteger", array("limit"=>20, "null"=>false));
    	$counts->column("action", "tinyinteger", array("limit"=>2, "null"=>false));
    	$counts->column("status", "tinyinteger", array("limit"=>2, "null"=>false, "default"=>1));
    	$counts->column("create_time", "integer", array("null"=>true, "limit"=>11));
        $counts->column("update_time", "integer", array("null"=>true, "limit"=>11));
        $counts->finish();

    }//up()

    public function down()
    {
    	$this->drop_table("counts");
    }//down()
}
