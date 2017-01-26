<?php

class AlterUserUseDevice extends Ruckusing_Migration_Base{
	public function up(){
		$this->change_column("users_use_devices","uid", "biginteger", array("primary_key"      => false, 
	                                               "auto_increment"   => false, 
	                                               "null"             => false,
	                                               "limit"            => 20));
	}

	public function down(){

	}
}