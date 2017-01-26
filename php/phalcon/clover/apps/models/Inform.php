<?php

namespace Psgod\Models;

use Psgod\Models\User;
use Psgod\Models\Ask;
use Psgod\Models\Reply;
use Psgod\Models\Comment;
use Psgod\Models\Count;


class Inform extends ModelBase{
	const TARGET_TYPE_ASK = 1;
	const TARGET_TYPE_REPLY = 2;
	const TARGET_TYPE_COMMENT = 3;
	const TARGET_TYPE_USER = 4;

	const CONTENT_MIN_LENGTH = 15;
	const CONTENT_MAX_LENGTH = 5000;

	const INFORM_STATUS_IGNORED = 0; //删除
	const INFORM_STATUS_PENDING = 1; //已举报，待处理
	const INFORM_STATUS_SOLVED = 2;  //已处理
	const INFORM_STATUS_REPLACED = 3; //重复举报

	public function getSource(){
		return 'informs';
	}

	public static function report( $uid, $target_type, $target_id, $content ){
		$report = new Inform();

		if( !$uid ){
			return 501;
		}

		$myself = User::findFirst('uid='.$uid);
		if( !$myself ){
			return 502;
		}

		if( !$target_id ){
			return 503;
		}


		switch( $target_type ){
			case Inform::TARGET_TYPE_ASK:
				$ask = Ask::findFirst('id='.$target_id.' AND status='.Ask::STATUS_NORMAL);
				if( !$ask ){
					return 504;
				}
				break;
			case Inform::TARGET_TYPE_REPLY:
				$reply = Reply::findFirst('id='.$target_id.' AND status='.Reply::STATUS_NORMAL);
				if( !$reply ){
					return 505;
				}
				break;
			case Inform::TARGET_TYPE_COMMENT:
				$comment = Comment::findFirst('id='.$target_id.' AND status='.Comment::STATUS_NORMAL);
				if( !$comment ){
					return 506;
				}
				break;
			case Inform::TARGET_TYPE_USER:
				$user = User::findFirst('id='.$target_id.' AND status='.User::STATUS_NORMAL);
				if( !$user ){
					return 507;
				}
				break;
			default:
				return 508;
				break;
		}

		$content=  trim($content);
		if( !$content ){
			return 509;
		}

		if( mb_strlen($content) <  Inform::CONTENT_MIN_LENGTH && mb_strlen($content) > Inform::CONTENT_MAX_LENGTH ){
			return 510;
		}

		$prevCond = array(
			'uid='.$uid,
			'target_type='.$target_type,
			'target_id='.$target_id,
			'status='.Inform::INFORM_STATUS_PENDING
		);
		$prev = Inform::findFirst(implode(' AND ', $prevCond));
		if( $prev ){
			if( $prev->content == $content ){
				return $prev; //511 重复举报相同内容
			}
			$prev->status = Inform::INFORM_STATUS_REPLACED;
			$prev->save_and_return($prev, false);
		}

		$report->uid = $uid;
		$report->target_type = $target_type;
		$report->target_id = $target_id;
		$report->content = $content;
		$report->create_time = time();
		$report->status = Inform::INFORM_STATUS_PENDING;

		$ret = $report->save_and_return($report, false);

		switch( $target_type ){
			case Inform::TARGET_TYPE_ASK:
				$res = Count::inform($uid, $target_id, Count::TYPE_ASK);
				if( is_object( $res ) ) {
		            Ask::count_add($target_id, 'inform');
		        }
				break;
			case Inform::TARGET_TYPE_REPLY:
				$res = Count::inform($uid, $target_id, Count::TYPE_REPLY);
				if( is_object( $res ) ) {
		            REPLY::count_add($target_id, 'inform');
		        }
				break;
			case Inform::TARGET_TYPE_COMMENT:
				$res = Count::inform($uid, $target_id, Count::TYPE_COMMENT);
				if( is_object( $res ) ) {
		            Comment::count_add($target_id, 'inform');
		        }
				break;
			case Inform::TARGET_TYPE_USER:
				// $res = Count::inform($uid, $target_id, Count::TYPE_USER);
				// if($res == 1) {
		        // $msg    = 'okay!';
		        //       $res = true;
		        //     User::count_add($target_id, 'inform');
		        // }
				break;
		}
		return $ret;
	}

	public function deal_report( $id, $uid, $result, $status = Inform::INFORM_STATUS_SOLVED ){
		if( $this->status != $this::INFORM_STATUS_PENDING ){
			return false;
		}

		$this -> status = $status;
		$this -> oper_time = time();
		$this -> oper_by = $uid;
		$this -> oper_result = $result;
		return $this->save_and_return($this);
	}
}
