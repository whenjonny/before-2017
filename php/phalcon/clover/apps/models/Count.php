<?php

namespace Psgod\Models;

class Count extends ModelBase
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


    public function getSource()
    {
        return 'counts';
    }

    public static function addCount($uid, $target_id, $type, $action, $status) {
    	$rec = new self();
    	$rec->uid = $uid;
    	$rec->target_id = $target_id;
    	$rec->type = $type;
    	$rec->action = $action;
    	$rec->create_time = time();
    	$rec->status = $status;
    	return $rec->save_and_return($rec);
    }

    public static function updateCount($uid, $target_id, $type, $action, $status) {

        $rec = self::findFirst("uid = {$uid} AND target_id = {$target_id} AND type = {$type} AND action={$action}");
        if($rec) {
            if($rec->status == $status) {
                return true;
            }
            $rec->status = $status;
            return $rec->save_and_return($rec);
        } else {
            return self::addCount($uid, $target_id, $type, $action, $status);
        }
    }

    public static function up($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return self::updateCount($uid, $target_id, $type, self::ACTION_UP, $status);
    }


    public static function like($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return self::updateCount($uid, $target_id, $type, self::ACTION_LIKE, $status);
    }


    public static function collect($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return self::updateCount($uid, $target_id, $type, self::ACTION_COLLECT, $status);
    }


    public static function inform($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return self::updateCount($uid, $target_id, $type, self::ACTION_INFORM, $status);
    }


    public static function share($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return self::updateCount($uid, $target_id, $type, self::ACTION_SHARE, $status);
    }


    public static function wxshare($uid, $target_id, $type, $status=self::STATUS_NORMAL){
        return self::updateCount($uid, $target_id, $type, self::ACTION_WEIXIN_SHARE, $status);
    }

    public static function get_counts_by_uid($uid){
        $res = Count::count(array(
            'uid='.$uid,
            'group'=>'action'
        ));

        $counts = array_fill(0, 9, 0);
        foreach ($res as $key => $value) {
            $counts[$value['action']] =$value['rowcount'];
        }
        return $counts;
    }

    public static function get_uped_reply_counts_by_uid( $uid ){
        $builder = self::query_builder('c');
        $res = $builder->join('Psgod\Models\Reply','c.target_id = r.id', 'r')
                ->where(
                    'c.type='.self::TYPE_REPLY. //reply
                    ' AND c.status='.self::STATUS_NORMAL. //normal
                    ' AND c.action='.self::ACTION_UP.
                    ' AND r.uid='.$uid
                )
                ->columns('count(c.id) as c')
                ->limit(1)
                ->getQuery()
                ->execute();

        return $res->toArray()[0]['c'];
    }


    public static function has_uped( $target_type, $target_id, $uid ){
        if( !$uid ){
            return false;
        }
        $where = array( 'type=' . $target_type,
                    'target_id=' . $target_id,
                    'status=' . Count::STATUS_NORMAL,
                    'uid='.$uid,
                    'action='.Count::ACTION_UP
                );
        $builder = self::query_builder()
            ->where( implode(' AND ', $where) )
            ->columns('count(*) as c');
        $res = $builder->getQuery()
            ->execute()
            ->toArray();

        return (boolean)$res[0]['c'];
    }

    public static function has_uped_reply($id, $uid = 0){
        return Count::has_uped( Count::TYPE_REPLY, $id, $uid);
    }

    public static function has_uped_ask($id, $uid = 0){
        return Count::has_uped( Count::TYPE_ASK, $id, $uid);
    }

    public static function has_uped_comment($id, $uid = 0){
        return Count::has_uped( Count::TYPE_COMMENT, $id, $uid);
    }
}
