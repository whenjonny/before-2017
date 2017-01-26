<?php

	class AddTablePushes extends Ruckusing_Migration_Base{
		public function up(){
			$pushes = $this->create_table("pushes", array('id' => false, 'options' => 'Engine=InnoDB'));
			$pushes->column('id', 'integer', array("limit"=>11, "auto_increment"=> true, "primary_key"=>true ));
			$pushes->column('type', 'tinyinteger', array("limit"=>2, "default"=> 0));
			$pushes->column('data', 'string', array("limit"=>255, "default"=> ''));
			$pushes->column('create_time', 'integer', array("limit"=>11, "default"=> 0));
	        $pushes->finish();
		}
		public function down(){
			$this->remove_table('pushes');
		}
	}
