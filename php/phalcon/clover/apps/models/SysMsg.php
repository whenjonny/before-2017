<?php

	namespace Psgod\Models;


	class SysMsg extends ModelBase{
		//消息类型
		const MSG_TYPE_NOTICE   = 1; //普通
		const MSG_TYPE_ACTIVITY = 2; //活动

		//Target类型
		const TARGET_TYPE_URL = 0; //跳转URL
		const TARGET_TYPE_ASK = 1;
		const TARGET_TYPE_REPLY = 2;
		const TARGET_TYPE_COMMENT = 3;
		const TARGET_TYPE_USER = 4;

		public function getSource(){
			return 'sys_msgs';
		}

		public static function post_msg( $uid,  $title, $target_type, $target_id, $jump_url, $post_time, $receiver_uids, $msg_type, $pic_url ){
			$sysmsg = new self();

            $title = trim($title);
			if( empty($title) ){
				return 501;
			}
			$sysmsg -> title = $title;

			if( $target_type == SysMsg::TARGET_TYPE_URL ){
				$target_id = 0;
				if( empty($jump_url) && !match_url_format($jump_url)){
					return 502;
				}
			}
            else{
				if( empty( $target_id ) ){
					return 503;
				}
				if( empty($jump_url) ){
					$jump_url = '-';
				}
			}
			if( !$sysmsg->post_time = strtotime( $post_time ) ){
				return 504;
			}

			if( is_string( $receiver_uids ) ){
				$receiver_uids = explode(',', $receiver_uids);
			}
			if( !is_array($receiver_uids) ){
				return 505;
            }
			$receiver_uids = array_unique( $receiver_uids );
			if( empty( $receiver_uids ) ){
				return 506;
			}


			if( !empty($pic_url) ){
				if( !match_url_format($pic_url) ){
					return 507;
				}
			}
			else{
				$pic_url = '-';
			}

			$sysmsg -> pic_url = $pic_url;


			$sysmsg -> receiver_uids = implode(',', $receiver_uids);
			$sysmsg -> target_id = $target_id;
			$sysmsg -> target_type = $target_type;
			$sysmsg -> jump_url = $jump_url;

			$sysmsg -> status = self::STATUS_NORMAL;
			$sysmsg -> msg_type = $msg_type;

			$sysmsg -> create_time = time();
			$sysmsg -> update_time = time();
			$sysmsg -> create_by = $uid;
			$sysmsg -> update_by = $uid;

			return $sysmsg->save_and_return($sysmsg, true);
		}

		public static function get_sys_msg_list( $type = 'pending'){
			$user = 'Psgod\Models\User';

			$cond = array();
			switch( $type ){
				case 'pending':
					$cond = array(
						's.post_time > '.time(),
						's.status = '.self::STATUS_NORMAL
					);
					break;
				case 'sent':
					$cond = array(
						's.post_time < '.time(),
						's.status = '.self::STATUS_NORMAL
					);
					break;
				case 'deleted':
					$cond = array(
						's.status = '.self::STATUS_DELETED
					);
					break;
				default:
					break;
			}

			$builder = self::query_builder('s');
			$res = $builder ->columns('u.uid, u.username, u.nickname, u.avatar, u.sex, s.id, s.title, s.target_type, s.target_id, s.jump_url, s.post_time, s.receiver_uids, s.create_time, s.status, s. create_by, s.msg_type, s.pic_url')
							->join( $user, 'u.uid = s.create_by', 'u', 'LEFT')
							->where( implode(' AND ', $cond) )
							->orderBy('s.create_time ASC')
							->getQuery()
							->execute();

			return $res->toArray();
		}

		public static function updateMsg($uid, $last_updated, $page=1, $limit=10) {
	        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_NOTICE );
	        $lasttime = $lasttime?$lasttime[Usermeta::KEY_LAST_READ_NOTICE]: 0;

	        $builder = self::query_builder('s');
	        $where = array(
	            's.post_time < '.$last_updated,
	            's.post_time > '.$lasttime,
	            's.status='.SysMsg::STATUS_NORMAL,
	            '(FIND_IN_SET('.$uid .', s.receiver_uids) OR s.receiver_uids=0)'
	        );

	        $res = $builder -> where( implode(' AND ',$where) );
	        $sysmsgs = self::query_page($builder, $page, $limit)->items;
	        foreach ($sysmsgs as $row) {
	            Message::newSystemMsg(
	                $row->create_by,
	                $uid,
	                'xxx您有一条消息xxx',
	                Message::TYPE_SYSTEM,
	                $row->id
	            );
	        }

	        if(isset($row)){
	            Usermeta::refresh_read_notify(
	                $uid,
	                Usermeta::KEY_LAST_READ_NOTICE,
	                $last_updated
	            );
	        }

	        return $sysmsgs;
		}

		public static function count_unread_sysmsgs( $uid ){
	        $lasttime = Usermeta::readUserMeta( $uid, Usermeta::KEY_LAST_READ_NOTICE );
	        if( $lasttime ){
	            $lasttime = $lasttime[Usermeta::KEY_LAST_READ_NOTICE];
	        }
	        else{
	            $lasttime = 0;
	        }

	        return SysMsg::count(array(
	            'post_time>'.$lasttime,
	            'status='.SysMsg::STATUS_NORMAL,
	            '(FIND_IN_SET('.$uid .', receiver_uids) OR receiver_uids=0)'
	        ));
	    }

	    public static function get_unread_sysmsg(){

	    }
	}
