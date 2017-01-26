<?php

class AddStatusEvaluation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column('evaluations', 'status', 'integer', array("limit"=>1,"null"=>false, 'default'=> 1, "after"=>'update_time'));
    }//up()

    public function down()
    {
    	$this->remove_column('evaluations', 'status');
    }//down()
}
