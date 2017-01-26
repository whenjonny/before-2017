<?php
namespace Psgod\Mobile\Models;

use Psgod\Models\Reply as ReplyBase;
use Psgod\Models\Label as LabelBase;
use Psgod\Models\Count ;
use Psgod\Models\Collection ;

class Reply extends ReplyBase {

    public function toSimpleArray() {

		return array(
			'id'                 => $this->id,
			'uid'                => $this->uid,
			'ask_id'             => $this->ask_id,
			'desc'               => $this->desc,
			'click_count'        => $this->click_count,
			'share_count'        => $this->share_count,
			'weixin_share_count' => $this->weixin_share_count,
			'up_count'           => $this->up_count,
			'comment_count'      => $this->comment_count,
			'inform_count'       => $this->inform_count,
			'create_time'        => $this->create_time,
			'update_time'        => $this->update_time,
			'status'             => $this->status,
			'nickname'           => $this->replyer->nickname,
			'avatar'             => $this->replyer->avatar,
			'sex'                => $this->replyer->sex,
            //'image_url'          => $this->image_url,
			// 'ratio'              => $ratio,
			// 'scale'              => $scale
			//'ratio'              => $this->upload->ratio,
			//'scale'              => $this->upload->scale,
		);
	}

	public function getLabelRows() {
		$builder = LabelBase::query_builder('l');
		return $builder->columns('id, content, x, y, direction')
					   ->where("l.target_id = {$this->id} and ".' l.type = '.LabelBase::TYPE_REPLY.' and l.status = '.LabelBase::STATUS_NORMAL)
					   ->getQuery()
					   ->execute()
					   ->toArray();
	}

	public function getNewCommentRows($page=1, $limit=10) {
		$builder = Comment::query_builder('c');
		$users   = 'Psgod\Models\User';
		return $builder->join($users, 'c.uid = u.uid', 'u')
					   ->where("c.target_id = {$this->id} and ".' c.type = '.Comment::TYPE_REPLY.' and c.status = '.Comment::STATUS_NORMAL)
					   ->columns('u.uid, u.avatar, u.sex, c.id comment_id, u.nickname, c.content, c.up_count, c.down_count, c.create_time')
					   ->orderBy('c.create_time')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute()
					   ->toArray();
	}

	public function getHotCommentRows($limit=5) {
		$builder = Comment::query_builder('c');
		$users   = 'Psgod\Models\User';
		return $builder->join($users, 'c.uid = u.uid', 'u')
					   ->where("c.target_id = {$this->id} and ".' c.type = '.Comment::TYPE_REPLY.' and c.status = '.Comment::STATUS_NORMAL)
					   ->columns('u.uid, u.avatar, u.sex, c.id comment_id, u.nickname, c.content, c.up_count, c.down_count, c.create_time')
					   ->orderBy('c.up_count')
					   ->limit($limit)
					   ->getQuery()
					   ->execute()
					   ->toArray();
	}

	public static function userReplyList($uid, $last_updated, $page=1, $limit=10) {
        $builder = self::query_builder('r');
        $asks    = 'Psgod\Models\Ask';
        return $builder->where('r.status = '.self::STATUS_NORMAL.
            " AND r.uid = ".$uid.
            " AND r.create_time < ".$last_updated)
            //->join($asks, "r.ask_id= r.id", "a", 'left')
            //->columns('id, content, x, y, direction')
            ->orderBy('r.create_time desc')
            ->limit($limit, ($page-1)*$limit)
            ->getQuery()
            ->execute();
	}

	public static function collectionList($uid, $page=1, $limit=10) {
        $builder = self::query_builder('r');
        $coll   = 'Psgod\Models\Collection';

        return $builder->join($coll, "cl.reply_id = r.id", "cl", 'RIGHT')
                	   ->where("cl.uid = {$uid} AND cl.status = ".self::STATUS_NORMAL)
                	   ->orderBy('cl.create_time desc')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute();
    }

	public function toStandardArray( $uid = 0, $width = 480) {
        $data = $this->toSimpleArray();
        //todo: change to ask id for client side
        $data['id']         = $this->id;
        $data['ask_id']     = $this->ask_id;

		$data['hot_comments'] = $this->getHotCommentRows();
		$data['new_comments'] = $this->getHotCommentRows();
		$data['labels']       = $this->getLabelRows();
        $data['type'] = 2;

        $upload = $this->upload;
        $data['image_width']    = $width;
        $data['image_height']   = ($upload&&$upload->ratio)?intval($width*($upload->ratio)):intval($width*1.333);
        $data['image_url']      = get_cloudcdn_url($upload->savename, $width);

        $data['is_download']    = $this->be_downloaded_by($uid);
        $data['uped']           = Count::has_uped_reply( $this->id, $uid );
        $data['collected']      = Collection::has_collected_reply( $this->id, $uid );

		return $data;
	}

    public static function getMyFellowReplyList($uid, $page, $limit=10) {
    	$builder = self::query_builder('r');
    	$follows = 'Psgod\Models\Follow';
		return $builder->where('r.status = '.self::STATUS_NORMAL)
					   ->join($follows, "f.follow_who = r.uid and f.uid = {$uid} and f.status = ".self::STATUS_NORMAL, 'f')
					   ->orderBy('r.create_time desc')
					   ->limit($limit, ($page-1)*$limit)
					   ->getQuery()
					   ->execute();
    }


}
