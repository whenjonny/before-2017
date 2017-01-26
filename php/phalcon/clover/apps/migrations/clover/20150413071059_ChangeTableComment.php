<?php

class ChangeTableComment extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->rename_column("comments", "up", "up_count");
    	$this->rename_column("comments", "down", "down_count");
    	$this->rename_column("comments", "inform", "inform_count");
    	$this->add_column('comments', 'for_comment', 'biginteger', array("limit"=>20,"null"=>false, "after"=>'reply_to'));
    }//up()

    public function down()
    {
    	$this->rename_column("comments", "up_count", "up");
    	$this->rename_column("comments", "down_count", "down");
    	$this->rename_column("comments", "inform_count", "inform");
    	$this->remove_column('comments', 'for_comment');
    }//down()
}
