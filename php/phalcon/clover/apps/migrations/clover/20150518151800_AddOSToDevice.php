<?php

class AddOSToDevice extends Ruckusing_Migration_Base{
	public function up(){
		$this->add_column("devices","os", "tinyinteger", array("limit"=> 2));
	}

	public function down(){

	}
}