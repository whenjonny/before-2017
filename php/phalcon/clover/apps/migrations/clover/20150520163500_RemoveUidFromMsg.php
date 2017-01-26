<?php

class RemoveUidFromMsg extends Ruckusing_Migration_Base{
	public function up(){
		$this->remove_column("messages","uid");
	}

	public function down(){

	}
}