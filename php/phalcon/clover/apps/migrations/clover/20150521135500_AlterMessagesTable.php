<?php

class AlterMessagesTable extends Ruckusing_Migration_Base{
	public function up(){
		$this->rename_column("messages","type","msg_type");
		$this->add_column("messages","target_type", "tinyinteger", array("limit"=> 2));
		$this->add_column("messages","target_id", "integer", array("limit"=> 11));
	}

	public function down(){

	}
}