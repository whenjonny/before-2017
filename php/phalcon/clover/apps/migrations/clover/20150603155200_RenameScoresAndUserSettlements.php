<?php

class RenameScoresAndUserSettlements extends Ruckusing_Migration_Base
{
	public function up()
	{
        $this->rename_table('user_settlements', 'user_scores');
		$this->rename_table("scores","user_settlements");

	}//up()

	public function down()
	{
		$this->rename_table("user_settlements","scores");
        $this->rename_table('user_scores', 'user_settlements');
	}//down()
}
