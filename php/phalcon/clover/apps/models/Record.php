<?php

namespace Psgod\Models;

class Record extends ModelBase
{

	const TYPE_ASK = 1;
	const TYPE_REPLY = 2;
	const TYPE_COMMENT = 3;

	const ACTION_UP             = 1;
	const ACTION_LIKE           = 2;
	const ACTION_COLLECT        = 3;
	const ACTION_DOWN           = 4;
	const ACTION_SHARE          = 5;
    const ACTION_WEIXIN_SHARE   = 6;
    const ACTION_INFORM         = 7;
	const ACTION_COMMENT        = 8;


    public function getSource()
    {
        return 'records';
    }

    public static function addRecord($uid, $target_id, $type, $action, $status) {
    	$rec = new self();
    	$rec->uid = $uid;
    	$rec->target_id = $target_id;
    	$rec->type = $type;
    	$rec->action = $action;
    	$rec->create_time = time();
    	$rec->status = $status;
    	return $rec->save_and_return($rec);
    }

    public static function updateRecord($uid, $target_id, $type, $action, $status) {
        return self::addRecord($uid, $target_id, $type, $action, $status);
    }

    public static function up($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return Record::updateRecord($uid, $target_id, $type, Record::ACTION_UP, $status);
    }


    public static function like($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return Record::updateRecord($uid, $target_id, $type, Record::ACTION_LIKE, $status);
    }


    public static function collect($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return Record::updateRecord($uid, $target_id, $type, Record::ACTION_COLLECT, $status);
    }


    public static function inform($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return Record::updateRecord($uid, $target_id, $type, Record::ACTION_INFORM, $status);
    }


    public static function share($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return Record::updateRecord($uid, $target_id, $type, Record::ACTION_SHARE, $status);
    }


    public static function wxshare($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return Record::updateRecord($uid, $target_id, $type, Record::ACTION_WEIXIN_SHARE, $status);
    }

    public static function comment($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return Record::updateRecord($uid, $target_id, $type, Record::ACTION_COMMENT, $status);
    }
}
