<?php
	class RenameTableUserScoreRecords extends Ruckusing_Migration_Base{
		public function up(){
			$this->rename_table('user_scores', 'user_settlements');
		}
		public function down(){
			$this->remove_column('user_settlements', 'user_scores');
		}
	}
