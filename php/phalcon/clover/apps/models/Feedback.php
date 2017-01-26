<?php
	namespace Psgod\Models;


	class Feedback extends ModelBase{
		const STATUS_DELETED = 'DELETED';
		const STATUS_SUSPEND = 'SUSPEND';
		const STATUS_FOLLOWED = 'FOLLOWED';
		const STATUS_RESOLVED = 'RESOLVED';
		const STATUS_REJECTED = 'REJECTED';
		const STATUS_DONE = 'DONE';

		static $status_name = array(
			'DELETED'  => '已删除',
			'SUSPEND'  => '待处理',
			'FOLLOWED' => '已跟进',
			'RESOLVED' => '已解决',
			'REJECTED' => '不回应'
		);

		static $next_status = array(
			'DELETED'  => self::STATUS_SUSPEND,
			'SUSPEND'  => self::STATUS_FOLLOWED,
			'FOLLOWED' => self::STATUS_RESOLVED,
			'RESOLVED' => self::STATUS_DONE,
			'REJECTED' => self::STATUS_FOLLOWED
		);

		public function getSource(){
			return 'feedbacks';
		}

		public static function get_status_name( $status_name ){
			$status = Feedback::$status_name;
			if( array_key_exists( $status_name, $status ) ){
				return $status[ $status_name ];
			}
			return false;
		}

		public static function change_status_to( $fb, $status, $uid ){
			if( !$uid ){
				return false;
			}
			$fb->status = $status;

			if( $status == Feedback::STATUS_DELETED ){
				$fb->del_time = time();
				$fb->del_by = $uid;
			}
			else{
				$fb->update_time = time();
				$fb->update_by = $uid;
			}


	        return $fb->save_and_return($fb);
		}

		/**
		 * 新反馈
		 * @param  integer $uid     用户ID
		 * @param  string $content 反馈内容
		 * @param  string $contact 联系方式
		 * @return [type]          [description]
		 */
		public function new_feedback( $content, $contact, $uid = 0){
			$data = new self();
			$data->uid = $uid;
			$data->content = $content;
			$data->contact = $contact;
			$data->opinion = '{}';
			$data->create_time = time();
			$data->update_time = time();
			$data->update_by = 0;
			$data->status = $this::STATUS_SUSPEND;

			return $this->save_and_return($data);
		}

		public static function post_opinion( $fbid, $uid, $opinion ){
			$fb = Feedback::findFirst( 'id='.$fbid );
			if( !$fb ){
				return 501;
			}

			if( $fb->status != Feedback::STATUS_FOLLOWED && $fb->status != Feedback::STATUS_SUSPEND ){
				return 502;
			}

			$user = User::findfirst('uid='.$uid);
			if( !$user ){
				return 503;
			}

			$content = trim($opinion);
			if(empty($content)){
				return 504;
			}


			$old_opinion = json_decode($fb->opinion, true);
			array_unshift($old_opinion, array('username'=>$user->username, 'comment_time'=> time(), 'opinion'=>$content ));
			$fb->opinion = json_encode($old_opinion);

			return $fb->save_and_return($fb);

		}
	}
