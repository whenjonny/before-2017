<?php

class RemoveUidFromDevices extends Ruckusing_Migration_Base{
	public function up(){
		$this->remove_column("devices","uid");
	}

	public function down(){

	}
}