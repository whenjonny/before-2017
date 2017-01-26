<?php

	class AddTableUserScheduling extends Ruckusing_Migration_Base{
		public function up(){
			$user_schedulings = $this->create_table("user_schedulings", array('id' => false, 'options' => 'Engine=InnoDB'));
			$user_schedulings->column("id", "integer", array("limit"=>11, "null"=>false, "primary_key"=> true,"auto_increment"=> true));
			$user_schedulings->column("uid", "biginteger", array("limit"=>20, "null"=>false));
			$user_schedulings->column("status", "tinyinteger", array("limit"=>2, "default"=>'0'));
			$user_schedulings->column("start_time", "integer", array("limit"=>11, "null"=>false, "default"=>'0'));
			$user_schedulings->column("end_time", "integer", array("limit"=>11, "null"=>false, "default"=>'0'));
			$user_schedulings->column("set_by", "biginteger", array("limit"=>20, "null"=>false));
			$user_schedulings->column("del_by", "biginteger", array("limit"=>20, "null"=>false));
			$user_schedulings->column("del_time", "integer", array("limit"=>11, "null"=>false, "default"=>'0'));
			$user_schedulings->column("create_time", "integer", array("limit"=>11, "default"=>'0'));
			$user_schedulings->column("update_time", "integer", array("limit"=>11, "default"=>'0'));
	        $user_schedulings->finish();
		}
		public function down(){
			$this->remove_table('user_schedulings');
		}
	}
