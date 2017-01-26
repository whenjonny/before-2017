<?php

class AddDelToEvaluation extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column('evaluations', 'del_by', 'biginteger', array("limit"=>20,"null"=>false, 'default'=> 0, "after"=>'update_time'));
    	$this->add_column('evaluations', 'del_time', 'integer', array("limit"=>11,"null"=>false, 'default'=> 0, "after"=>'del_by'));
    }//up()

    public function down()
    {
    	$this->remove_column('evaluations', 'del_by');
    	$this->remove_column('evaluations', 'del_time');
    }//down()
}
