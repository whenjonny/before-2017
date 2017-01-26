<?php

class AlterDeviceTable extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->change_column("devices", "create_time", "integer", array('null'=>false, 'limit'=>11));
    	$this->change_column("devices", "token", "string", array('null'=>false, 'limit'=>1024));

    }//up()

}
