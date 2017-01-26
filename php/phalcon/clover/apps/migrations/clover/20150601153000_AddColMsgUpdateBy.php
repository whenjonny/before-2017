<?php
	class AddColMsgUpdateBy extends Ruckusing_Migration_Base{
		public function up(){
			$this->add_column('sys_msgs', 'update_by', 'biginteger', array("limit"=>20, "null"=>false));
		}
		public function down(){
			$this->remove_column('sys_msgs', 'update_by');
		}
	}

