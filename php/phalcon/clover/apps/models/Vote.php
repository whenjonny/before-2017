<?php

namespace Psgod\Models;

class Vote extends ModelBase
{
    /**
     * 赞求助
     */
    const TYPE_ASK = 1;

    /**
     * 赞回复
     */
    const TYPE_REPLY = 2;

    /**
     * 赞评论
     */
    const TYPE_COMMENT = 4;


    public function getSource()
    {
        return 'votes';
    }


    /**
     * 添加新的赞
     * 
     * @param integer $uid  用户UID
     * @param integer $type 赞类型。取上面 TYPE_* 那些常量值
     * @param integer $id   被赞对象ID
     */
    public static function addNewVote($uid, $type, $id)
    {
        $obj = new self();
        $obj->uid       = $uid;
        $obj->type      = $type;
        $obj->target_id = $id;
        $obj->assign(array(
            'create_time'   => time(),
            'update_time'   => time(),
        ));
        $obj->options = "comments";
        
        return $obj->save_and_return($obj);
    }

    /**
     * 根据对象类型找出赞者
     * 
     * @param  integer  $type      赞类型。取上面 TYPE_* 那些常量值
     * @param  integer  $targer_id 对象ID
     * @param  integer  $limit     个数
     * @param  string   $order     排序方式
     * @return array set
     */
    public static function findVote($type, $targer_id, $limit=10, $order='id DESC')
    {
        return self::find(array(
            'conditions'    => sprintf('type=%d and target_id=%d', $type, $target_id),
            'limit'         => $limit,
            'order'         => $order,
        ));
    }
}
