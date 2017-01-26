<?php

namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Follow extends ModelBase
{

    public function getSource()
    {
        return 'follows';
    }

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->belongsTo('uid', 'Psgod\Models\User', 'uid', array(
            'alias' => 'the_fellow',
        ));
        $this->belongsTo('follow_who', 'Psgod\Models\User', 'uid', array(
            'alias' => 'the_fans',
        ));
    }

    public static function setUserRelation($uid, $me, $status)
    {
        $rela = self::findFirst(array(
            "uid = '$me' AND follow_who = '$uid'"
        ));
        if($rela) {
            if($rela->status == $status) {
                return true;
            }
            $rela->status = $status;
            $rela->update_time = time();
        }
        else {
            if($status == self::STATUS_DELETED) {
                return true;
            }
            $rela = new self();
            $rela->uid          = $me;
            $rela->follow_who   = $uid;
            $rela->create_time  = time();
            $rela->status = $status;
        }
        return $rela->save_and_return($rela);
    }

    public static function userFansCount($uid) {
        return self::count(array("follow_who = {$uid} AND status = ".self::STATUS_NORMAL));
    }

    public static function userFellowCount($uid) {
        return self::count(array("uid = {$uid} AND status = ".self::STATUS_NORMAL));
    }

    public static function updateMsg( $uid, $last_updated ){
        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_FOLLOW );
        $lasttime = $lasttime?$lasttime[Usermeta::KEY_LAST_READ_FOLLOW]: 0;

        $builder  = Follow::query_builder();
        $where = array(
            'create_time < '.$last_updated,
            'create_time > '.$lasttime,
            'status='.Follow::STATUS_NORMAL,
            'follow_who='.$uid
        );

        $res = $builder -> where( implode(' AND ',$where) )
                         -> getQuery()
                         -> execute();
        $follows = self::query_page($builder)->items;
        foreach( $follows as $row){
            Message::newFollower(
                $row->uid,
                $uid,
                'uid:'.$row->uid.' follows you.',
                $row->id
            );
        }

        if(isset($row)){
            Usermeta::refresh_read_notify(
                $uid,
                Usermeta::KEY_LAST_READ_FOLLOW,
                $row->create_time
            );
        }

        return $follows;
    }


    public static function count_new_followers( $uid ){
        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_FOLLOW );
        $lasttime = $lasttime?$lasttime[Usermeta::KEY_LAST_READ_FOLLOW]: 0;

        return Follow::count(array(
            'create_time > '.$lasttime,
            'status='.Follow::STATUS_NORMAL,
            'follow_who='.$uid
            )
        );
    }

    public static function list_unread_followers($lasttime, $page=1, $size=500){

        $follow = new self;

        $sql = 'select f.follow_who, count(1) as num'.
            ' FROM follows f'.
            ' WHERE f.status='.self::STATUS_NORMAL.
            ' AND f.create_time>'.$lasttime.
            ' GROUP BY f.follow_who';
        // Execute the query
        return new Resultset(null, $follow, $follow->getReadConnection()->query($sql));
    }

    public static function is_follower_of($uid, $me){
        //follow_who 表示关注了谁
        return Follow::findfirst('uid='.$uid.' AND follow_who='.$me.' AND status='.Follow::STATUS_NORMAL);
    }
}
