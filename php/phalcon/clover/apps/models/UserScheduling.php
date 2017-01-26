<?php
namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class UserScheduling extends ModelBase
{
    //这个status是个坑，后面估计也同步不了了,为了跟userScore保持一致
    const STATUS_NORMAL = 0;
    const STATUS_PAID   = 1;
    const STATUS_COMPLAIN = 2;
    const STATUS_DELETED  = 3;

    const TYPE_ASK      = 1;
    const TYPE_REPLY    = 2;

    public function getSource()
    {
        return 'user_schedulings';
    }

    public static function current_schedulings($uid, $page, $limit) {
        $user_scores = self::result_page($page, $limit, array(
            'uid'=>$uid,
            'status'=>self::STATUS_NORMAL
        ));
        return $user_scores;
    }

    public static function isWorking($uid){
        $time = time();
        return UserScheduling::findFirst("uid=$uid AND end_time > $time AND start_time <= $time");
    }

    public static function get_balance($uid) {
        $sum = UserScheduling::sum(array(
            'column'    => "end_time-start_time",
            'conditions'=> "uid=".$uid." AND end_time < ".time(),
            'group'     => "status"

        ));
        $ret = array(0, 0);
        foreach($sum as $row) {
            $ret[$row->status] = $row->sumatory + 0;
        }
        return $ret;
    }

    public static function pay_scores($uid, $time = null){
        if(!$time)  $time = time();
        $sql = "UPDATE user_schedulings set status = ".self::STATUS_PAID.
            " WHERE uid = $uid".
            " AND end_time < $time".
            " AND status = ".self::STATUS_NORMAL;
        // Base model
        $user_score = new self();
        // Execute the query
        return new Resultset(null, $user_score, $user_score->getReadConnection()->query($sql));
    }

    public static function operTypes(){
        return array(
            'verify_count'=>array(
                ActionLog::TYPE_VERIFY_ASK,
                ActionLog::TYPE_VERIFY_REPLY,
                ActionLog::TYPE_REJECT_ASK,
                ActionLog::TYPE_REJECT_REPLY,
                //ActionLog::TYPE_DELETE_ASK,
                //ActionLog::TYPE_DELETE_REPLY
            ),
            'pass_count'=>array(
                ActionLog::TYPE_VERIFY_REPLY,
                ActionLog::TYPE_VERIFY_ASK
            ),
            'reject_count'=>array(
                ActionLog::TYPE_REJECT_REPLY,
                ActionLog::TYPE_REJECT_ASK
            ),
            'delete_count'=>array(
                ActionLog::TYPE_DELETE_ASK,
                ActionLog::TYPE_DELETE_REPLY
            ),
            'forbit_count'=>array(
                ActionLog::TYPE_FORBID_USER
            ),
            'delete_comment_count'=>array(
                ActionLog::TYPE_DELETE_COMMENT
            ),
            'post_ask'=>array(
                ActionLog::TYPE_POST_ASK
            ),
            'add_parttime'=>array(
                ActionLog::TYPE_ADD_PARTTIME
            ),
            'create_user_count' => array(
                ActionLog::TYPE_REGISTER
            )
        );
    }
}
