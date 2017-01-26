<?php

	class AddColumnFocusesUptime extends Ruckusing_Migration_Base{
		public function up(){
			$this->add_column('focuses', 'update_time', 'integer', array("limit"=>11, "null"=>false, "after"=>'create_time'));
		}
		public function down(){
			$this->remove_column('focuses', 'update_time');
		}
	}
