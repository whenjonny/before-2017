<?php

class AddPlatformToDevice extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->rename_column('devices', 'os', 'platform');
    	$this->add_column('devices', 'os', 'string', array("limit"=>255, "null"=>false, "default"=>''));
    }

    public function down(){
    	$this->remove_column('devices', 'os');
    	$this->rename_column('devices', 'platform', 'os');
    }
}

