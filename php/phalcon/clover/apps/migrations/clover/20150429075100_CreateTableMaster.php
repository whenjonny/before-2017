<?php

class CreateTableMaster extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$master = $this->create_table("masters", array('id' => false, 'options' => 'Engine=InnoDB'));
    	$master->column("id", "integer", array("primary_key"      => true,
	                                           "auto_increment"   => true,
	                                           "null"             => false,
	                                           "limit"            => 11));
    	$master->column("uid", "integer", array("limit"=>11, "null"=>false, "default"=>0));
    	$master->column("status", "tinyinteger", array("limit"=>2, "null"=>false, "default"=>1));

    	$master->column("start_time", "integer", array("null"=>true, "limit"=>11, "default"=>0));
        $master->column("end_time", "integer", array("null"=>true, "limit"=>11, "default"=>0));
        $master->column("set_by", "integer", array("limit"=>11, "null"=>false, "default"=>0));
        $master->column("set_time", "integer", array("null"=>true, "limit"=>11, "default"=>0));
        $master->column("del_by", "integer", array("limit"=>11, "null"=>false, "default"=>0));
        $master->column("del_time", "integer", array("null"=>true, "limit"=>11, "default"=>0));
        $master->finish();
    }//up()

    public function down()
    {
    	$this->drop_table("masters");
    }//down()
}