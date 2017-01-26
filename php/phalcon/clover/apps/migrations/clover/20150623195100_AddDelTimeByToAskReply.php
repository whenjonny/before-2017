<?php

	class AddDelTimeByToAskReply extends Ruckusing_Migration_Base{
		public function up(){
			$this->add_column('replies', 'del_by', 'biginteger', array("limit"=>20,  "default" => 0, "null"=>false, "after"=>'status'));
			$this->add_column('replies', 'del_time', 'integer', array("limit"=>11, "default" => 0,  "null"=>false, "after"=>'del_by'));

			$this->add_column('asks', 'del_by', 'biginteger', array("limit"=>20,  "default" => 0, "null"=>false, "after"=>'status'));
			$this->add_column('asks', 'del_time', 'integer', array("limit"=>11, "default" => 0,  "null"=>false, "after"=>'del_by'));
		}
		public function down(){
			$this->remove_column('asks', 'del_by');
			$this->remove_column('asks', 'del_time');

			$this->remove_column('replies', 'del_by');
			$this->remove_column('replies', 'del_time');
		}
	}
