<?php
	namespace Psgod\Admin\Controllers;
	use Psgod\Models\User,
		Psgod\Models\Master,
		Psgod\Models\ActionLog;

	class MasterController extends ControllerBase{

		public function indexAction(){

		}
		public function rec_listAction(){

		}

		public function list_recsAction(){
			$this->noview();
			if( !$this->request->isAjax() ){
				return;
			}

			$status = $this->post('status', 'string', '1');

			$Master = new Master;
			Master::update_masters();

			$order = array();
			// 检索条件
			$cond = array();
			if( $status == 1 ){
				$cond['start_time'] =array(time(), '<');  //已经开始的
				$cond['end_time'] =array(time(), '>');  //还未结束的
				$order=array('end_time DESC');  //先失效靠前
				$order=array('start_time ASC');  //先上的靠前
			}
			else{
				$cond['start_time'] =array(time(), '>');  //未开始的
				$order=array('start_time ASC');  //先生效的靠前
				$order=array('end_time ASC');  //先失效的靠前
			}

	        //Here SUCKS
	        $cond['Psgod\Models\Master.status'] = $status;

	        // 关联表数据结构
	        $join = array();
	        $join['User'] = 'uid';
	        //$join['Role']     = 'role_id';

	        // 用于遍历修改数据
	        $data  = $this->page($Master, $cond, $join);

			foreach($data['data'] as $row){
	            $row->sex = get_sex_name($row->sex);
	            $row->start_time = date('Y-m-d H:i', $row->start_time);
	            $row->end_time = date('Y-m-d H:i', $row->end_time);
            	$row->total_inform_count = User::get_all_inform_count($row->uid);
	            $row->oper = '<a href="#" class="cancel" data-id="'.$row->id.'">取消推荐</a>';
	        }

	        return $this->output_table($data);
		}

		public function cancelAction(){
			$this->noview();
			if( !$this->request->isAjax() ){
				return;
			}

			$uid = $this->post('id', 'int', 0);
			if( !$master = Master::findFirst($uid) ){
				return ajax_return(2,'用户不存在');
			}
			$old = ActionLog::clone_obj($master);

			$master->status = Master::STATUS_DELETE;
			$master->del_by = $this->_uid;
			$master->del_time = time();

			$save = $master->save_and_return($master);
			if( $save ){
				ActionLog::log(ActionLog::TYPE_CANCEL_RECOMMEND, $old, $save);
				return ajax_return(1, '取消成功');
			}
			else{
				return ajax_return(3, '取消失败');
			}
		}

		/**
		 * 大神列表
		 * @return [type] [description]
		 */
		public function master_listAction(){
			$this->assets->addCss('theme/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');
            $this->assets->addJs('theme/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js');

            //$this->assets->addJs('theme/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js');
		}

		/**
		 * 搜索大神
		 * @return [type] [description]
		 */
		public function list_mastersAction(){
			$this->noview();
			if( !$this->request->isAjax() ){
				return;
			}

			$user = new User;
	        // 检索条件
	        $cond = array();
	        $cond['role_id']    = $this->get("role_id", "int");
	        $cond['uid']        = $this->post("uid", "int");
	        $cond['username']   = array(
	            $this->post("username", "string"),
	            "LIKE",
	            "AND"
	        );
	        $cond['is_god'] = 1;

	         // 关联表数据结构
	        $join = array();
	        //$join['Master'] = 'uid';
	        //$join['Role']     = 'role_id';

	        // 用于遍历修改数据
	        $data  = $this->page($user, $cond, $join);
	        foreach($data['data'] as $row){
	            $uid = $row->uid;
	            $row->sex = get_sex_name($row->sex);
	            $row->create_time = date('Y-m-d H:i', $row->create_time);
            	$row->total_inform_count = $user->get_all_inform_count($uid);
	            $row->oper = '<a href="#" role="dialog" class="recommend" data-target="#recommend" data-toggle="modal" >推荐</a>';
	        }
	        // 输出json
	        return $this->output_table($data);
		}

		public function recommendAction(){
			$this->noview();
			if( !$this->request->isAjax() ){
				return array();
			}

			$master_id = $this->post('master_id', 'int', 0);
			$start_time = $this->post('start_time', 'int', 0);
			$end_time = $this->post('end_time', 'int', 0);
			if( $start_time < time() ){
				return ajax_return(5, '不能设置过去的时间');
			}
			if( $start_time > $end_time ){
				return ajax_return(4,'开始时间不能晚于结束时间');
			}

			$master = new Master();
			$master->uid = $master_id;
			$master->start_time = $start_time;
			$master->end_time = $end_time;
			$master->set_time = time();
			$master->set_by = $this->_uid;
			$master->del_by = 0;
			$master->del_time = 0;
            $master->status = Master::STATUS_PENDING;

            $data = $master->save_and_return($master);
			if( $data ){
				ActionLog::log(ActionLog::TYPE_SET_RECOMMEND, array(), $data);
				return ajax_return(1,'添加成功');
			}
			else{
				return ajax_return(2,'添加失败');
			}
		}

	}
