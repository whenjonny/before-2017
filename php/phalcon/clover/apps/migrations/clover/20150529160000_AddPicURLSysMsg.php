<?php

	class AddPicURLSysMsg extends Ruckusing_Migration_Base{
		public function up(){
			$this->add_column('sys_msgs', 'pic_url', 'string', array("limit"=>2000, "null"=>false, ""));
		}
		public function down(){
			$this->remove_column('sys_msgs', 'pic_url');
		}
	}
