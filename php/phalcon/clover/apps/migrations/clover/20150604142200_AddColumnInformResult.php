<?php

	class AddColumnInformResult extends Ruckusing_Migration_Base{
		public function up(){
			$this->add_column('informs', 'oper_result', 'string', array("limit"=>500, "null"=>true, "after"=>'oper_by'));
		}
		public function down(){
			$this->remove_column('informs', 'oper_result');
		}
	}
