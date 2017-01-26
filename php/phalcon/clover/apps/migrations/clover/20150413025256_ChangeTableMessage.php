<?php

class ChangeTableMessage extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column('messages', 'uid', 'biginteger', array("limit"=>20,"null"=>false, "after"=>'id'));
    }//up()

    public function down()
    {
    	$this->remove_column('messages', 'uid');
    }//down()
}
