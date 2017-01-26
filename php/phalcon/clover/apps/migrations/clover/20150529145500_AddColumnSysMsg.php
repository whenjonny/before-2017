<?php

	class AddColumnSysMsg extends Ruckusing_Migration_Base{
		public function up(){
			$this->add_column('sys_msgs', 'msg_type', 'tinyinteger', array("limit"=>4, "null"=>false, ""));
		}
		public function down(){
			$this->remove_column('sys_msgs', 'msg_type');
		}
	}
