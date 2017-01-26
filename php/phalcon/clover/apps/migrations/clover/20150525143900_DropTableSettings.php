<?php

class DropTableSettings extends Ruckusing_Migration_Base{
	public function up(){
		$this->drop_table("settings");
	}

	public function down(){
	}
}
