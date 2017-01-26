<?php

class AddOpinionColtoFeedback extends Ruckusing_Migration_Base{
	public function up(){
		$this->add_column('feedbacks', 'opinion', 'string', array("limit"=>5000, "null"=>false, "default"=>"{}", "after"=>"contact"));
		$this->add_column('feedbacks','update_time', 'integer', array("limit"=>11, "null"=>false, "default"=>'0', "after"=>"create_time"));
		$this->add_column('feedbacks','update_by', 'biginteger', array("limit"=>20, "null"=>false,  "after"=>"update_time"));
	}

	public function down(){
		$this->remove_column('feedbacks', 'opinion');
	}
}
