<?php

class ChangeTableName extends Ruckusing_Migration_Base
{
    public function up()
    {
    	 $this->rename_table("feedback", "feedbacks");
    }//up()

    public function down()
    {
    	$this->rename_table("feedbacks", "feedback");
    }//down()
}
