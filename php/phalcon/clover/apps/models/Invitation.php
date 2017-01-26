<?php

namespace Psgod\Models;
use \Psgod\Models\Usermeta;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Invitation extends ModelBase
{

    public function getSource()
    {
        return 'invitations';
    }

    public function initialize()
    {
        $this->useDynamicUpdate(true);

    }

    private static function addNewInvitation($ask_id, $invite_uid) {
        $inv = new self();
        $inv->ask_id = $ask_id;
        $inv->invite_uid = $invite_uid;
        $inv->status = self::STATUS_READY;
        $inv->create_time = time();
        $inv->update_time = time();
        return $inv->save_and_return($inv);
    }

    public static function getInvitation($ask_id, $invite_uid) {
        return self::findFirst(
            array(
                "conditions" => "ask_id = ?1 and invite_uid = ?2",
                "bind"       => array(1 => $ask_id, 2 => $invite_uid),
            )
        );
    }

    public static function updateInvitation($ask_id, $invite_uid, $status) {
        $inv = self::getInvitation($ask_id, $invite_uid);
        if($inv) {
            if($inv->status == $status)
                return true;
            $inv->status = $status;
            $inv->update_time = time();
            return $inv->save_and_return($inv);
        } else {
            return self::addNewInvitation($ask_id, $invite_uid);
        }
    }

    public static function updateMsg( $uid, $last_updated ){
        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_INVITE);
        $lasttime = $lasttime?$lasttime[Usermeta::KEY_LAST_READ_INVITE]: 0;

        $builder = Invitation::query_builder('i');
        $where = array(
            'i.create_time < '.$last_updated,
            'i.create_time > '.$lasttime,
            'i.status='.Invitation::STATUS_NORMAL,
            'i.invite_uid='.$uid
        );

		$ask = 'Psgod\Models\Ask';
        $res = $builder -> where( implode(' AND ',$where) )
                        -> join($ask, 'a.id=i.ask_id', 'a', 'LEFT')
                        -> columns('a.uid, i.invite_uid, i.id')
                        -> getQuery()
                        -> execute();
        $invites = self::query_page($builder)->items;

        foreach( $invites as $row){
            Message::newInvitation(
                $row->uid,
                $uid,
                'uid:'.$row->uid.' invites you to help him/her.',
                $row->id);
        }

        if(isset($row)){
            Usermeta::refresh_read_notify(
                $uid,
                Usermeta::KEY_LAST_READ_INVITE,
                $lasttime
            );
        }
        return $invites;
    }

    public static function count_new_invitation($uid){
        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_INVITE );
        if( $lasttime ){
            $lasttime = $lasttime[Usermeta::KEY_LAST_READ_INVITE];
        }
        else{
            $lasttime = 0;
        }

        return Invitation::count( array(
            'create_time>'.$lasttime,
            'status='.Invitation::STATUS_NORMAL,
            'invite_uid='.$uid
        ) );
    }

    public static function list_unread_invites( $lasttime, $page = 1, $size = 500 ){

        $invite = new self;
        $sql = 'select i.invite_uid, count(1) as num'.
            ' FROM invitations i'.
            ' WHERE i.status='.self::STATUS_NORMAL.
            ' AND i.create_time>'.$lasttime.
            ' GROUP BY i.invite_uid';
        return new Resultset(null, $invite, $invite->getReadConnection()->query($sql));
    }

}
