<?php

class AlterUserSettlements extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->change_column("user_settlements", "score", "float", array('default'=>0,"null"=>false) );
    }//up()

    public function down(){
        $this->change_column("user_settlements", "score", "integer", array('default'=>0,"null"=>false) );
    }

}
