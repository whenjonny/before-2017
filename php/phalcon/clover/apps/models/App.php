<?php
	namespace Psgod\Models;
	use Psgod\Models\Upload;

	class App extends ModelBase{
		public function getSource(){
			return 'recommend_apps';
		}

		public function get_list(){
			$builder = self::query_builder();

			$cond = 'del_time IS NULL';
			$builder->columns('u.savename as savename, Psgod\Models\App.app_name as app_name, Psgod\Models\App.jumpurl as jumpurl');
			$builder->where($cond);
			$builder->orderBy('order_by ASC');
			$builder->join('Psgod\Models\Upload', 'u.id=logo_upload_id','u');

			$app_result = $builder->getQuery()
							      ->execute();
			if( empty($app_result) ){
				$appList = array();
			}
			else{
				$appList = $app_result->toArray();
			}

			return $appList;
		}
	}
