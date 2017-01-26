<?php
	namespace Psgod\Admin\Controllers;

	use Psgod\Models\SysMsg;
	use Psgod\Models\User;
	use Psgod\Models\ActionLog;
	use Psgod\Models\Comment;

	class SysMsgController extends ControllerBase{

		public function new_msgAction(){
			$this->assets->addCss('theme/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css');
			$this->assets->addCss('theme/assets/global/plugins/jquery-tokeninput/css/token-input.css');
			$this->assets->addCss('theme/assets/global/plugins/jquery-tokeninput/css/token-input-facebook.css');
			$this->assets->addCss('theme/assets/global/plugins/jquery-tokeninput/css/token-input-mac.css');

			$this->assets->addJs('theme/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js');
			$this->assets->addJs('theme/assets/global/plugins/jquery-tokeninput/js/jquery.tokeninput.js');
		}

		public function msg_listAction(){

		}


		public function get_msg_listAction(){
			$msg_type_text = array('-','通知','活动');
			$target_type_text = array('跳转URL','求助','作品','评论','用户');

			$this->noview();
			$type = $this->get('type','string');

			$cond = array();
			$cond['title'] = array(
				$this->post('title','string'),
				'LIKE',
				'AND'
			);

			switch( $type ){
				case 'pending':
					$cond['post_time'] = array(time(),'>');
					$cond['status']    = SysMsg::STATUS_NORMAL;
					break;
				case 'sent':
					$cond['post_time'] = array(time(),'<');
					$cond['status']    = SysMsg::STATUS_NORMAL;
					break;
				case 'deleted':
					$cond['status'] = SysMsg::STATUS_DELETED;
					break;
				default:
					break;
			}

			$join = array();
			$order = 'create_time ASC ';
			$msg_list = $this->page(new SysMsg, $cond, $join, $order);


			//$msg_list = SysMsg::get_sys_msg_list( $type );

			$i=0;
			foreach( $msg_list['data'] as $msg ){
				$receiver_usernames =array();
				if( $msg->receiver_uids ){
					$receivers = User::getUserByUIDArray(explode(',',$msg->receiver_uids))->toArray();
					$receiver_usernames[] = array_column( $receivers, 'username' );
				}
				else{
					$receiver_usernames[] = array('全体');
				}

				switch($msg->target_type){
					case SysMsg::TARGET_TYPE_URL:
						$msg_list['data'][$i]->jump = '<a href="'.$msg->jump_url.'">链接</a>';
						break;
					case SysMsg::TARGET_TYPE_ASK:
						$msg_list['data'][$i]->jump = '<a href="http://'.$this->config['host']['pc'].'/ask/show/'.$msg->target_id.'" target="_blank">查看原图</a>';
						break;
					case SysMsg::TARGET_TYPE_REPLY:
						$msg_list['data'][$i]->jump = '<a href="http://'.$this->config['host']['pc'].'/comment/show/?target_type='.Comment::TYPE_REPLY.'&target_id='.$msg->target_id.'" target="_blank">查看原图</a>';
						break;
					case SysMsg::TARGET_TYPE_COMMENT:
						$msg_list['data'][$i]->jump = '评论详情页赞无链接';
						//$msg_list['data'][$i]->jump = '<a href="http://'.$this->config['host']['pc'].'/ask/show/'.$msg->target_id.'" target="_blank">查看原图</a>';
					break;
					case SysMsg::TARGET_TYPE_USER:
						$msg_list['data'][$i]->jump = '<a href="http://'.$this->config['host']['pc'].'/user/my_works/'.$msg->target_id.'" target="_blank">查看用户信息</a>';
						break;
					default:
						$msg_list['data'][$i]->jump = '无跳转';
						break;
				}
				if( match_url_format($msg->jump_url)  ){
					$msg_list['data'][$i]->jump = '<a href="'.$msg->jump_url.'">链接</a>';
				}

				if( $msg->pic_url != '-' ){
					$msg_list['data'][$i]->title .= '<img src="'.$msg->pic_url.'"/>';
				}

				$msg_list['data'][$i]->msg_type = $msg_type_text[$msg->msg_type];
				$msg_list['data'][$i]->target_type = $target_type_text[$msg->target_type];
				$msg_list['data'][$i]->receivers = implode(',', $receiver_usernames[0]);
				$msg_list['data'][$i]->create_time = date('Y-m-d H:i', $msg->create_time);
				$msg_list['data'][$i]->update_time = date('Y-m-d H:i', $msg->update_time);
				$msg_list['data'][$i]->post_time = date('Y-m-d H:i', $msg->post_time);
				if( $msg->create_by >0 ){
					$msg_list['data'][$i]->create_by = User::findFirst('uid='.$msg->create_by)->toArray()['username'];
				}
				else{
					$msg_list['data'][$i]->create_by = '系统';
				}

				if( $msg->post_time >= time() ){
					$msg_list['data'][$i]->oper = '<a href="/sysmsg/del_msg?id="'.$msg->id.'" class="del_msg">取消发布</a>';
				}
				else{
					$msg_list['data'][$i]->oper = '无';
				}
				++$i;
			}


			return $this->output_table($msg_list);
		}

		public function post_msgAction(){
			$this->noview();
            $uid = $this->_uid;
			if( !$uid ){
				return false;
			}
            $sender = $uid;

			$msg_type = $this->post('msg_type','int');
            $title = $this->post('title', 'string');
			$target_type = $this->post('target_type', 'int');
			$target_id = $this->post('target_id', 'int');
			$pic_url = $this->post('pic_url','string','');
			$jump_url = $this->post('jump_url','string' ,'-');
			$post_time = $this->post('post_time', 'string');
			$receiver_uids = $this->post('receiver_uids','string');
			$send_as_system = (bool)$this->post('send_as_system','string', false);
			if( $send_as_system ){
				$sender = 0;
			}

            $ret = SysMsg::post_msg($sender,  $title, $target_type, $target_id, $jump_url, $post_time, $receiver_uids, $msg_type, $pic_url );

			if( $ret instanceof SysMsg ){
				ActionLog::log(ActionLog::TYPE_POST_SYSTEM_MESSAGE, NULL, $ret);
				$msg = '发送成功';
				$code = 1;
			}
			else{
				$code = 0;
				$msgText = array(
					501 => '标题不能为空',
					502 => '跳转链接不能为空',
					503 => '目标ID为空',
					504 => '发送时间无法转换',
					505 => '非法的接收者id',
					506 => '接收者id不能为空',
					507 => '跳转链接格式非法'
				);
				if( array_key_exists($ret, $msgText) ){
					$msg = $msgText[$ret];
				}
				else{
					$msg = '发送失败';
				}
			}

			return ajax_return($code,'error',$msg);
		}

		public function getUserListAction(){
			$this->noview();
			// if( !$this->request->isAjax() ){
			// 	return ;
			// }
			$q = $this->get('q','string');
			$cond = '';
			if( $q ){
				$cond = 'uid="'.$q.'" OR username LIKE "%'.$q.'%" OR nickname LIKE "%'.$q.'%"';
			}

			$user = User::find(array(
				'conditions'=> $cond,
				'columns' => 'uid, nickname, username, status, sex, avatar'
			))->toArray();

			foreach( $user as $key => $value){
				$user[$key]['avatar'] = get_cloudcdn_url($value['avatar']);
			}
			array_push($user, array('uid'=>0, 'nickname'=>'全体', 'username'=>'全体', 'status' => 1, 'sex'=>1, 'avatar'=>'/img/avatar.jpg'));

			return ajax_return(1,'okay',$user);
		}
	}
