<?php

class AddColumnToUserUseDevice extends Ruckusing_Migration_Base{
	public function up(){
		$this->add_column("users_use_devices","status", "tinyinteger", array("null" => false,"limit"=> 1));
		$this->add_column("users_use_devices","settings", "string", array("null" => false,"limit"=> 1024));
	}

	public function down(){

	}
}
