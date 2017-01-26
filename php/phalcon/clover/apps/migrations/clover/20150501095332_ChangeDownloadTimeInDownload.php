<?php

class ChangeDownloadTimeInDownload extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->rename_column("downloads", "download_time", "create_time");
    }//up()

    public function down()
    {
    	$this->rename_column("downloads", "create_time", "download_time");
    }//down()
}
