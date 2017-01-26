<?php

namespace Psgod\Models;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class Review extends ModelBase
{
    const STATUS_DELETE     = 0;
    const STATUS_NORMAL     = 1;
    const STATUS_REJECT     = 2;
    const STATUS_RELEASE    = 3;
    const STATUS_PREVIEW    = 4;

    const TYPE_ASK      = 1;
    const TYPE_REPLY    = 2;

    public function beforeSave()
    {
        $this->update_time = time();
    }

    public function getSource()
    {
        return 'reviews';
    }

    //raw sql for replies
    public static function get_replies_by($review_id, $params=null){
        $phql = "SELECT * FROM reviews left join uploads on reviews.upload_id=uploads.id where reviews.type=1 and reviews.review_id=$review_id";
        $review = new Review;
        return new Resultset(null, $review, $review->getReadConnection()->query($phql));
    }

    /**
     * [get_review_list 获取review列表(审核通过的)]
     * @return [type] [description]
     */
    public static function get_review_list($time){
        $phql = "SELECT r.*, u.id upload_id, u.savename FROM reviews r ".
            "LEFT JOIN uploads u ON r.upload_id = u.id ".
            "WHERE r.release_time < '$time' ".
            "AND r.status = ". Review::STATUS_NORMAL;
        $review = new Review;
        return new Resultset(null, $review, $review->getReadConnection()->query($phql));
    }

    public static function update_status($review, $status, $data="")
    {
        $review->status = $status;
        switch($status){
        case self::STATUS_NORMAL:
            $review->score = $data;
            break;
        case self::STATUS_REJECT:
            $review->evaluation = $data;
            break;
        case self::STATUS_RELEASE:
            //logger about release
            break;
        case self::STATUS_DELETE:
            break;
        }

        return $review->save_and_return($review, 1);
    }


    public static function addNewReview($type, $parttime_uid, $uid, $review_id, $labels, $upload_obj, $release_time)
    {
        $review                         = new self();
        $review->type                   = $type;
        $review->parttime_uid           = $parttime_uid;
        $review->review_id              = $review_id;
        $review->ask_id                 = 0;
        $review->uid                    = $uid;
        $review->upload_id              = $upload_obj->id;
        $review->labels                 = $labels;
        $review->status                 = self::STATUS_NORMAL;
        $review->score                  = 0;
        $review->evaluation             = '';
        $review->create_time            = time();
        $review->update_time            = time();
        $review->release_time           = $release_time;
        //$ask->asker_ip               = get_client_ip();

        return $review->save_and_return($review, 1);
    }

    /**
     * [setReviewAskId 更新Reply的Review ask_id]
     */
    public static function setReviewAskId($review_id, $ask_id){
        if (empty($review_id) || empty($ask_id)) return;
        $phql = "update reviews set ask_id = $ask_id, update_time = now() where review_id = $review_id";
        $review = new Review;
        return $review->getReadConnection()->query($phql);
    }
}
