<?php

	class AddColumnUserScores extends Ruckusing_Migration_Base{
		public function up(){
			$this->add_column('user_scores', 'oper_by', 'biginteger', array("limit"=>20,  "default" => 0, "null"=>false, "after"=>'status'));
		}
		public function down(){
			$this->remove_column('user_scores', 'oper_by');
		}
	}
