<?php
namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class UserScore extends ModelBase
{
    //这个status是个坑，后面估计也同步不了了
    const STATUS_NORMAL = 0;
    const STATUS_PAID   = 1;
    const STATUS_COMPLAIN = 2;
    const STATUS_DELETED  = 3;

    const TYPE_ASK      = 1;
    const TYPE_REPLY    = 2;

    public function getSource()
    {
        return 'user_scores';
    }

    public static function get_balance($uid) {
        $sum = UserScore::sum(array(
            'column'    => "score",
            'conditions'=> "uid=".$uid,
            'group'     => "status"

        ));
        $ret = array(0, 0, 0 ,0);
        foreach($sum as $row) {
            $ret[$row->status] = $row->sumatory + 0;
        }
        return $ret;
    }

    public static function get_scores( $uid = 0 ){
        $cond = array(
            'column'    => "score",
            'group'     => "status"
        );
        if( $uid ){
            $cond['conditions'] = "oper_by=".$uid;
            $cond['group'] .= ', oper_by';
        }

        $sum = UserScore::sum( $cond );
        $ret = array(0, 0, 0, 0);
        foreach($sum as $row) {
            $ret[$row->status] = $row->sumatory + 0;
        }
        return $ret;
    }

    public static function current_score($uid, $page, $limit) {
        $user_scores = self::result_page($page, $limit, array(
            'uid'=>$uid,
            'status'=>self::STATUS_NORMAL
        ));
        return $user_scores;
    }

    public static function total_score($uid, $page, $limit) {
        //$sum = UserScore::sum("uid=".$this->_uid." AND status != ".UserScore::STATUS_COMPLAIN);
        //return $sum;
    }

    public static function pay_scores($uid){
        $sql = "UPDATE user_scores set status = ".self::STATUS_PAID." WHERE uid = $uid AND status = ".self::STATUS_NORMAL;
        // Base model
        $user_score = new self();
        // Execute the query
        return new Resultset(null, $user_score, $user_score->getReadConnection()->query($sql));
    }

    public static function update_score($uid, $type, $item_id, $data, $oper_by) {
        $score = self::findFirst(array("uid = {$uid} AND type = {$type} AND item_id = {$item_id}"));
        if(!$score) {
            $score = new self;
            $score->uid = $uid;
            $score->type= $type;
            $score->item_id = $item_id;
        }
        $score->content = '';
        $score->status  = 0;
        $score->score   = $data;
        $score->oper_by = $oper_by;
        $score->action_time = time();

        $user = User::findUserByUID($uid);
        $user->ps_score += floatval($data);
        $user->save();

        return $score->save_and_return($score, true);
    }

    public static function update_content($uid, $type, $item_id, $data, $oper_by) {
        $score = self::findFirst(array("uid = {$uid} AND type = {$type} AND item_id = {$item_id}"));
        if(!$score) {
            $score = new self;
            $score->uid = $uid;
            $score->type= $type;
            $score->item_id = $item_id;
        }
        $score->content = is_null($data)?'': $data;
        $score->oper_by = $oper_by;
        $score->action_time = time();
        $score->status  = 0;
        $score->score   = 0;

        return $score->save_and_return($score, true);
    }

    public static function oper_user($type, $item_id) {
        $sql = 'SELECT u.nickname,u.username, u.avatar, u.uid'.
            ' FROM user_scores s'.
            ' LEFT JOIN users u ON s.oper_by = u.uid '.
            ' WHERE s.type = ' . $type.
            ' AND s.item_id = ' . $item_id.
            ' LIMIT 1';

        $us = new self();
        return new Resultset(null, $us, $us->getReadConnection()->query($sql));
    }
}
