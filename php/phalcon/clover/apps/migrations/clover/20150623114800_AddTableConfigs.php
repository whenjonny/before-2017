<?php

	class AddTableConfigs extends Ruckusing_Migration_Base{
		public function up(){
			$configs = $this->create_table("configs", array('id' => false, 'options' => 'Engine=InnoDB'));
			$configs->column('id', 'integer', array( 'limit' => 11, "null" => false, "primary_key"=>true,"auto_increment"=>true));
			$configs->column('name', 'string', array( 'limit' => 255, "default"=> '',));
			$configs->column('value', 'string', array( 'limit' => 255, "default"=> '',));
			$configs->column('create_time', 'integer', array( 'limit' => 11, "default"=> 0));
			$configs->column('update_time', 'integer', array( 'limit' => 11, "default"=> 0));
	        $configs->finish();
		}
		public function down(){
			$this->remove_table('configs');
		}
	}


