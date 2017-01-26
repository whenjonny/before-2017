<?php

namespace Psgod\Models;

class Evaluation extends ModelBase
{

    public function beforeSave()
    {
        // $this->image_url = basename($this->image_url);
        $this->update_time = time();
    }

    public function getSource()
    {
        return 'evaluations';
    }

    public static function set_evaluation($uid, $content){
        $evaluation = self::findFirst(array("uid = {$uid} AND content = '{$content}'"));
        if(!$evaluation) {
            $evaluation = new self;
            $evaluation->uid = $uid;
            $evaluation->content = is_null($content)?'': $content;
            $evaluation->create_time = time();
            $evaluation->update_time = time();
            $evaluation->status = Evaluation::STATUS_NORMAL;
            $evaluation->del_by = 0;
            $evaluation->del_time = 0;
            //if ($evaluation->save() == false) {
                //dump($evaluation->getMessages());
                //return false;
            //}
        }
        return $evaluation->save_and_return($evaluation, true);
    }
}
