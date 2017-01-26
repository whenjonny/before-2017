<?php
	namespace Psgod\Admin\Controllers;
	use Psgod\Models\Feedback;
	use Psgod\Models\User;
	use Psgod\Models\ActionLog;

	class FeedbackController extends ControllerBase{
		public function indexAction(){

		}

		public function list_fbAction(){
			$this->noview();
			if( !$this->request->isAjax() ){
				return array();
			}

			$fbModel = new Feedback;

			$cond = array();
			$uid = $this -> post('uid', 'int');
			$status = $this -> get('status', 'string');
			switch($status){
				case 'suspend':
					$cond[get_class($fbModel).'.status']=Feedback::STATUS_SUSPEND;
					break;
				case 'following':
					$cond[get_class($fbModel).'.status']=Feedback::STATUS_FOLLOWED;
					break;
				//default:
					// $cond[get_class($fbModel).'.status']=array( Feedback::STATUS_DELETED, '!=');
			}
			$cond[get_class(new User).'.uid'] = $uid;
			$cond[get_class(new User).'.username']   = array(
				$this->post("username", "string"),
	            "LIKE",
	            "AND"
	        );
	        $cond[get_class(new User).'.nickname']   = array(
	            $this->post("nickname", "string"),
	            "LIKE",
	            "AND"
	        );


			$join = array();
			$join['User'] = 'uid';

			$bigmen = $this->page( $fbModel, $cond, $join );
			foreach ($bigmen['data'] as $bigman) {
				$bigman->create_time = date('Y-m-d H:i:s', $bigman->create_time);
				$bigman->del_time = date('Y-m-d H:i:s', $bigman->del_time);
				$bigman->avatar = '<img class="user-portrait" src='.$bigman->avatar.' alt="头像">';
				$opinions = json_decode($bigman->opinion);
				$opns = array();
				foreach( $opinions as $opinion ){
					$str  = '<li class="opinion_item">';
					$str .= $opinion->username.' '.date('Y-m-d H:i:s', $opinion->comment_time).'<br />';
					$str .= '·'.$opinion->opinion;
					$str .= '</li>';
					$opns[] = $str;
				}
				$opnBox = '<ul class="opinion_list">'.implode('', $opns).'</ul>';
				if( $bigman->status == Feedback::STATUS_FOLLOWED || $bigman->status == Feedback::STATUS_SUSPEND ){
					$opnBox .='<div name="post_opinion"><input type="hidden" name="fbid" value="'.$bigman->id.'"/><input type="text" name="opinion" class="opinion" placeholder="请填写备注" /><input type="button" class="submit_opinion" value="提交" /></div>';
				}
				else{
					$opnBox .= '<div name="post_opinion"><b>待处理 或 已跟进 时才能填写记录。</b></div>';
				}

				$bigman->opinion = $opnBox;
				$bigman->sex = get_sex_name($bigman->sex);
				$bigman->crnt_status = Feedback::get_status_name( $bigman->status );
				$bigman->oper = $this-> get_next_oper($bigman->status);
			}

			return $this->output_table($bigmen);
		}

		public function chg_statusAction(){
			$this->noview();
			if( !$this->request->isAjax() ){
				return array();
			}

			$fb_id = $this->post('fb_id', 'int');
			$status = $this->post('status', 'string');
			if( empty($fb_id) ){
				return ajax_return(2, 'error', '没有反馈id');
			}

			if( !array_key_exists($status, Feedback::$status_name) ){
				return ajax_return(3,'error', '状态不存在');
			}
			$fbModel = new Feedback();
			$fb = Feedback::findFirst($fb_id);
			$old = ActionLog::clone_obj($fb);
			$res = Feedback::change_status_to( $fb, $status, $this->_uid );

			if( $res ){
				ActionLog::log(ActionLog::TYPE_MODIFY_FEEDBACK_STATUS, $old, $res);
				return ajax_return( 1, 'ok', '状态更改成功');
			}
			else{
				return ajax_return(4,'error','状态更改失败');
			}
		}

		protected function get_next_oper( $current_status ){
			//dump($current_status);
			if(!array_key_exists($current_status, Feedback::$status_name)){
				return '';
			}

			$next_status = Feedback::$next_status[$current_status];

			$opers = array(
				$this->oper_button( $next_status )
			);

			if($current_status == Feedback::STATUS_SUSPEND){
				$opers[] = $this -> oper_button( Feedback::STATUS_DELETED );
			}

			return implode( ' ', $opers );
		}

		protected function oper_button( $status ){
			$oper_name = array(
				'DELETED'  => '删除',
				'SUSPEND'  => '回复',
				'FOLLOWED' => '跟进',
				'RESOLVED' => '解决',
				'REJECTED' => '拒绝'
			);

			if( !array_key_exists($status, $oper_name)){
				return '无';
			}

			return '<a href="#" class="chg_status" data-next-status="'.$status.'">'.$oper_name[$status].'</a>';
		}

		public function post_opinionAction(){
			$this->noview();
			if( !$this->request->isAjax() ){
				return false;
			}

			$fbid = $this->post('fbid','int');

			$uid = $this->_uid;
			if( !$uid ){
				return ajax_return(0, '请先登录！', false);
			}
			$opinion = $this->post('opinion', 'string');

			$fb = Feedback::findFirst( 'id='.$fbid );
			$old = json_decode($fb->opinion);
			$res = Feedback::post_opinion( $fbid, $uid, $opinion );

			if( is_integer($res) ){
				switch( $res ){
					case 501:
						$res = '此反馈不存在';
						break;
					case 502:
						$res = '此状态的反馈不能添加备注';
						break;
					case 503:
						$res = '你是幽灵？当前登陆账户居然不存在。';
						break;
					case 504:
						$res = '备注内容不能为空';
						break;
					case 505:
						$res = '备注错误';
						break;
				}
			}
			else{
				ActionLog::log(ActionLog::TYPE_ADD_FEEDBACK, $old, json_decode($res->opinion));
				$res = $res->toArray();
			}

			return ajax_return(1,'okay', $res);
		}
	}
