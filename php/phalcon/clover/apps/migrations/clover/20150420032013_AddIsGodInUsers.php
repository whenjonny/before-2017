<?php

class AddIsGodInUsers extends Ruckusing_Migration_Base
{
     public function up()
    {
    	$this->add_column('users', 'is_god', 'tinyinteger', array("limit"=>1,"null"=>false, "default"=>0, "after"=>'avatar'));
    }//up()

    public function down()
    {
    	$this->remove_column('users', 'is_god');
    }//down()
}
