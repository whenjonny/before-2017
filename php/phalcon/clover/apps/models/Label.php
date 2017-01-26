<?php

namespace Psgod\Models;

class Label extends ModelBase
{
    const TYPE_ASK  = 1;
    const TYPE_REPLY= 2;
    const DIRE_LEFT = 1;
    const DIRE_RIGHT= 3;

    public function getSource()
    {
        return 'labels';
    }

    /**
     * 添加一个新标签
     *
     * @param string $content   标签内容
     * @param float  $x         标签位置横轴百分比
     * @param float  $y         标签位置纵轴百分比
     * @param integer$upload_id 文件上传id
     * @param integer$target_id    求pid
     * @return false | \psgod\models\label
     */
    public static function addNewLabel($content, $x, $y, $uid, $direction, $upload_id, $target_id, $type=self::TYPE_ASK)
    {
        $obj = new self();
        $obj->content   = $content;
        $obj->x         = $x;
        $obj->y         = $y;
        $obj->uid       = $uid;
        $obj->direction = $direction;
        $obj->upload_id = $upload_id;
        $obj->target_id = $target_id;
        $obj->type      = $type;
        $obj->assign(array(
            'create_time'   => time(),
            'update_time'   => time(),
            'status'        => self::STATUS_NORMAL
        ));

        return $obj->save_and_return($obj, true);
    }

    public function to_simple_array()
    {
        return array(
            'id'        => $this->id,
            'content'   => $this->content,
            'x'         => round(floatval($this->x), 3),
            'y'         => round(floatval($this->y), 3),
            'direction' => $this->direction
            //'uid'       => $this->uid,
            //'upload_id' => $this->upload_id,
            //'target_id'       => $this->target_id
        );
    }
}
