<?php

class CreateTableInvitations extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$invitations = $this->create_table("invitations", array('id' => false, 'options' => 'Engine=InnoDB'));
    	$invitations->column("id", "integer", array("primary_key"      => true,
	                                               "auto_increment"   => true,
	                                               "null"             => false,
	                                               "limit"            => 11));
    	$invitations->column("ask_id", "biginteger", array("limit"=>20, "null"=>false, "default"=>0));
    	$invitations->column("invite_uid", "integer", array("limit"=>11, "null"=>false, "default"=>0));
    	$invitations->column("status", "tinyinteger", array("limit"=>2, "null"=>false, "default"=>1));
    	$invitations->column("create_time", "integer", array("null"=>true, "limit"=>11, "default"=>0));
        $invitations->column("update_time", "integer", array("null"=>true, "limit"=>11, "default"=>0));
        $invitations->finish();
    }//up()

    public function down()
    {
    	$this->drop_table("invitations");
    }//down()
}
