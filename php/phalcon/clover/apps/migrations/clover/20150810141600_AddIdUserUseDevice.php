<?php

class AddIdUserUseDevice extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->remove_index('users_use_devices','uid',array('name'=>'PRIMARY'));
        //$this->remove_index('users_use_devices','device_id',array('name'=>'PRIMARY'));  //就算是复合主键，只删除一个就可以了
        //$this->add_column('users_use_devices', 'id', 'biginteger', array("limit"=>20,'primary_key' => true,"auto_increment"=> true, "before"=>'uid'));
        //上面的执行不了
        $this->execute('ALTER TABLE `users_use_devices` ADD `id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }//up()

    public function down()
    {
    	$this->remove_column('users_use_devices', 'id');
        $this->add_index('users_use_devices','device_id',array('name'=>'PRIMARY'));
        $this->add_index('users_use_devices','uid',array('name'=>'PRIMARY'));

    	//$this->change_column('users_use_devices', 'uid', 'biginteger', array("limit"=>20,'primary_key' => true, "before"=>'device_id'));
    	//$this->change_column('users_use_devices', 'device_id', 'integer', array("limit"=>11,'primary_key' => true, "before"=>'create_time'));

    }//down()
}
