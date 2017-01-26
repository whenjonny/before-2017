<?php
	namespace Psgod\Admin\Controllers;

	use Psgod\Models\User,
		Psgod\Models\Reply,
		Psgod\Models\Ask,
		Psgod\Models\Device,
		Psgod\Models\Download;

	use Phalcon\Mvc\Model\Resultset\Simple as Resultset;


	class StatController extends ControllerBase{
		public function initialize(){
			parent::initialize();
			$this->assets->addJs('HighCharts/js/highcharts.js');
			$this->assets->addCss('theme/assets/global/css/stat.css');
			$this->assets->addJs('theme/assets/global/scripts/stat.js');
		}

		private function phqlFetch( $phql ){
			$pdo = \Phalcon\DI::getDefault()->getDb();
			$sql = $pdo->prepare($phql);
			$sql ->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
			$sql->execute();
			$res = $sql->fetchAll();
			return $res;
		}

		public function get_threadsActionFEIQI(){
			$this->noview();
			$counts = array();
			$counts['asks'] = Ask::sum_stats();
			$counts['replies'] = Reply::sum_stats();

			$unit = $this->get('unit', 'string', 'day');
			$pace = $this->get('pace', 'int', '1');
			$endAt = $this->get('endAt','int', time()+60*60*24 );
			$startFrom = $this->get('startFrom','int', time()-7*60*60*24);

			$unitSec = 60*60;
			switch( $unit ){
				// case 'week':
				//  $unitSec *= 7;
				case 'day':
					$unitSec *= 24;
				// case 'hours':
				//  $unitSec *= 60;
				// case 'minutes':
				//  $unitSec *= 60;
			}

			foreach( $counts as $name => $value ){
				$stat_array = array();
				for( $dayDelta = 7; $dayDelta > 0; $dayDelta-- ){
					$crntDaySec = $endAt - $dayDelta*$unitSec;

					$crntDayStr = date('Y-m-d', $crntDaySec);


					if( isset( $counts[$name][$crntDayStr] ) ){
						$stat_array[$crntDayStr] = (int)$counts[$name][$crntDayStr];
					}
					else{
						$stat_array[$crntDayStr]=0;
					}
				}

				$stats[$name]= array_values($stat_array);
			}

			return ajax_return(1,'okay', $stats);
		}

		public function statsAction(){

		}
		public function analyzeAction(){

		}

		public function get_usersAction(){
			$counts = array();
			//$counts['users'] = User::stats();
		}

		/**
		 * 分析，与时间有关，用于分析趋势
		 * @return [type] [description]
		 */
		public function sum_analyzeAction(){
			$this->noview();
			if( !$this->request->isAjax() ){
				return false;
			}
			$type = $this->get('type', 'string');

			$start_date = strtotime( $this->get('stdate', 'string', date('Y-m-d') ) );
			$end_date   = strtotime( $this->get('etdate', 'string', date('Y-m-d') ) );
			$quota = $this->get('quota', 'string');

			$sum = array();
			switch( $type ){
				case 'users':
					$sum[] = $this->analyze_users();
					break;
				case 'asks':
					$sum[] = $this->analyze_asks();
					break;
				case 'reply':
					$sum[] = $this->analyze_replies();
					break;
			}

			return ajax_return(1,'okay', $sum);
		}

		protected function analyze_asks(){
			$period = $this->get('period', 'string');
			$total = $this->get('total', 'boolean', false);

			$field = 'create_time';
			$where = array('true');
			$div = 60/*seconds*/;

			switch( $period ){
				case 'hour':
					$div *= 60; /* minutes */
					break;
				case 'day':
					$div *=24; /* hours */
					break;
				case 'week':
					$div *= 7; /* days */
					break;
				// case 'month':
				//  break;
				// case 'hour':
				//  break;
			}

			echo $phql = "SELECT count(*) as c, (
						$field - (
							UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP()
						)
					) DIV ($div) AS dd
				FROM
					asks
				WHERE ".
					implode(' AND ',$where).
				" GROUP BY
					dd
				ORDER BY
					dd";

			$res = $this->phqlFetch($phql);

			var_dump($res);
			exit;
			return $res;
		}

// 计算每天的总量
// SELECT
// CONCAT_WS(';',
//  count(case when id<=10 then id end ),
// count(case when id<=20 then id end ),
// count(case when id<=30 then id end ),
// count(case when id<=40 then id end ),
// count(case when id<=50 then id end )
// )
// FROM
//  asks


		/**
		 * 统计，单纯统计数量，与时间无关
		 * @return [type] [description]
		 */
		public function sum_statsAction(){
			$this->noview();
			if( !$this->request->isAjax() ){
				return false;
			}

			$type = $this->get('type', 'string');

			switch( $type ){
				case 'os':
					$sum = $this->sum_os();
					break;
				case 'users':
					$sum = $this->sum_users();
					break;
				case 'threads':
					$sum = $this->sum_thread();
					break;
			}

			return ajax_return(1,'okay', $sum);
		}

		protected function sum_os(){
			$phql = "SELECT os, count(*) as c FROM devices GROUP BY os";
			$res = $this->phqlFetch($phql);
			$res = array_combine(array_column($res,'os'), array_column($res,'c'));

			$sum =  array(
				array( 'Android', (int)$res[Device::TYPE_ANDROID] ),
				array( 'iOS', (int)$res[Device::TYPE_IOS] )
			);
			return $sum;
		}

		protected function sum_users(){
			$phql = "SELECT sex, count(*) as c FROM users GROUP BY sex";
			$res = $this->phqlFetch($phql);
			$res = array_combine( array_column($res,'sex'), array_column($res,'c'));

			$sum = array(
				array( '男', (int)$res[User::SEX_MAN] ),
				array( '女', (int)$res[User::SEX_FEMALE] ),
			);
			return $sum;
		}

		protected function sum_thread(){
			$phql = 'SELECT \'asks\' as type,count(*) as c FROM asks WHERE status='.Ask::STATUS_NORMAL;
			$phql.= ' UNION SELECT \'replies\' as type, count(*) as c FROM replies WHERE status='.Reply::STATUS_NORMAL;
			$res = $this->phqlFetch($phql);
			$res = array_combine( array_column($res,'type'), array_column($res,'c'));

			$sum = array(
				array( '求助数', (int)$res['asks'] ),
				array( '作品数', (int)$res['replies'] ),
			);
			return $sum;
		}


		public function sum_statsFEIQI($fields = 'create_time', $where = 'TRUE ' ){
			// $clauses = array(
			//  'users' => array(),
			//  'asks'  => array(),
			//  'replies' => array(),
			//  'comments' => array(),
			//  'msgs' => array(),
			//  'devices' => array(
			//      'os' => array('')
			//  ),
			// );
		 //    if( empty($where) ){
		 //        $where = 'status='. self::STATUS_NORMAL;
		 //    }

		 //    $phql = "SELECT
		 //            count(*) as c, (
		 //                create_time - (
		 //                    UNIX_TIMESTAMP(UTC_TIMESTAMP()) - UNIX_TIMESTAMP()
		 //                )
		 //            ) DIV (60 * 60 *24) AS dd
		 //        FROM
		 //            $table
		 //        WHERE
		 //            $where
		 //        GROUP BY
		 //            dd
		 //        ORDER BY
		 //            dd";
		 //    $review = new Review;
		 //    $res = new Resultset(null, $review, $review->getReadConnection()->query($phql));
		 //    var_dump($res);



		 //    $res = $builder->getQuery()->execute()->toArray();
		 //    $keys = array_column( $res, 'd');
		 //    $values = array_column( $res, 'c');
		 //    return array_combine($keys, $values);
		}

	}

