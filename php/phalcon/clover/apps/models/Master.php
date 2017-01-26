<?php
	namespace Psgod\Models;
	use Psgod\Models\User;
	use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

	class Master extends ModelBase{
		const STATUS_DELETE = -1;
		const STATUS_PENDING = 0;
		const STATUS_VALID = 1;

		public function getSource(){
			return 'masters';
		}

		public static function update_masters(){
			$master = new self();
			$phql = 'UPDATE '.$master->getSource().' SET status='.self::STATUS_VALID.' WHERE start_time<UNIX_TIMESTAMP() AND end_time>UNIX_TIMESTAMP() AND status='.self::STATUS_PENDING;
			return new Resultset(null, $master, $master->getReadConnection()->query($phql));
		}

		public static function get_master_list($page = 1, $size = 15){
			self::update_masters();
			$master = new self();
			$user = 'Psgod\Models\User';

			$cond = array(
					'm.start_time < '.time(),
					'm.end_time > '.time(),
					'm.status = '.self::STATUS_VALID
					);
			if(($offset = ($page-1)*$size) < 0){
				$offset = 0;
			}

			$builder = $master->query_builder('m');
			$res = $builder ->columns('u.uid, u.username, u.nickname, u.avatar, u.sex, u.asks_count as ask_count, u.replies_count as reply_count ')
							->join( $user, 'u.uid = m.uid', 'u')
							->where( implode(' AND ', $cond) )
							->orderBy('m.start_time ASC, m.start_time ASC')
							->limit($size)
							->offset($offset)
							->getQuery()
							->execute();
			return $res->toArray();


		}
	}
