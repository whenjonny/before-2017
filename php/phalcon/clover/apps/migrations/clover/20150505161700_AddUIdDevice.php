<?php

class AddUIdDevice extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->add_column('devices', 'uid', 'biginteger', array("limit"=>20,"null"=>false, "after"=>'id'));
    }//up()
}
