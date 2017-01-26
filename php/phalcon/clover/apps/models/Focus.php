<?php

namespace Psgod\Models;

class Focus extends ModelBase
{
    /**
     * 取消的关注
     */
    const STATUS_CANCEL = 0;

    /**
     * 正常的关注
     */
    const STATUS_NORMAL = 1;


    public function getSource()
    {
        return 'focuses';
    }

    /**弃用
     * [focus 关注/取消关注 问题]
     * @param  [type] $uid [用户ID]
     * @param  [type] $aid [作品ID]
     * @return [type]      [description]
     */
    //TODO remove function focus
    public static function focus($uid, $aid, $status){
        $focus = new self();
        $focus->uid = $uid;
        $focus->ask_id = $aid;
        $focus->create_time = time();
        $focus->update_time = time();
        $focus->status = $status;

        return $focus->save_and_return($focus);
    }


    public static function setFocus($uid, $aid, $status)
    {
        $focus = self::findFirst(array(
            "uid = '$uid' AND ask_id = '$aid'"
        ));
        if($focus) {
            if($focus->status==$status) {
                return $focus;
            }
            $focus->status = $status;
            $focus->update_time = time();
        }
        else {
            $focus = new self();
            $focus->uid  = $uid;
            $focus->ask_id = $aid;
            $focus->status = $status;
            $focus->create_time = time();
            $focus->update_time = time();
        }
        return $focus->save_and_return($focus);
    }

    public static function checkUserAskFocus( $target_id, $uid = 0){
        $builder = Focus::query_builder();
        $res = $builder ->where('uid='.$uid.' AND status='.Focus::STATUS_NORMAL.' AND ask_id='.$target_id)
                        ->columns('count(*) as c ')
                        ->getQuery()
                        ->execute();

        if( $res->toArray()[0]['c'] ){
            return true;
        }
        else{
            return false;
        }
    }

    public static function has_focused_ask( $target_id, $uid = 0){
        if( !$uid ){
            return 0;
        }
        $where = array(
            'ask_id=' . $target_id,
            'status=' . self::STATUS_NORMAL,
            'uid='.$uid
        );
        $builder = self::query_builder()
            ->where( implode(' AND ', $where) )
            ->columns('count(*) as c');
        $res = $builder->getQuery()
            ->execute()
            ->toArray();

        return (boolean)$res[0]['c'];
    }
}
