<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\User;
use Psgod\Models\Usermeta;
use Psgod\Models\Comment;
use Psgod\Models\Ask;
use Psgod\Models\Reply;
use Psgod\Models\Inform;
use Psgod\Models\ActionLog;

class InformController extends ControllerBase
{

	public function indexAction()
	{

	}

	public function list_reportsAction(){
		$this->noview();
		if( !$this->request->isAjax() ){
			return false;
		}

		$inform = new Inform;
		$cond = array();
		$order = array();
		$join = array('User'=>'uid');
		$group = array();


		$type = $this->get('type', 'string', 'pending');
		if( $type == 'pending' ){
			$cond[get_class($inform).'.status'] = Inform::INFORM_STATUS_PENDING;
		}
		else if( $type == 'resolved' ){
			$cond[get_class($inform).'.status'] = Inform::INFORM_STATUS_SOLVED;
		}
		else {// if( $type == 'all' ){
			//
		}

		$cond[get_class($inform).'.uid']		= $this->post("uid", "int");
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
		$pc_host = $this->config['host']['pc'];

		$data  = $this->page($inform, $cond, $join, $order, $group);
		foreach($data['data'] as $row){
			$reporter = User::findFirst('uid='.$row->uid);
			$genderColor = get_sex_name($reporter->sex) == '男' ?'lightsteelblue':'pink';
			$avatar = $reporter->avatar ? '<img class="user-portrait" src="'.$reporter->avatar.'" alt="'.$reporter->username.'" style="border: 3px solid '.$genderColor.'"/>':'无头像';
			$row->content = '<a target="_blank" href="http://'.$pc_host.'/user/profile/'.$row->uid.'">'.$avatar.'</a>'.$reporter->username.' '.date('Y-m-d H:i', $row->create_time).'<br />'.$row->content;
			switch( $row->target_type ){
				case Inform::TARGET_TYPE_ASK:
					$row->object = '<a target="_blank" href="http://'.$pc_host.'/ask/show/'.$row->target_id.'">查看被举报求助</a>';
					break;
				case Inform::TARGET_TYPE_REPLY:
					$row->object = '<a target="_blank" href="http://'.$pc_host.'/comment/show/?target_type=2&target_id='.$row->target_id.'">查看被举报作品</a>';
					break;
				case Inform::TARGET_TYPE_COMMENT:
					$comment = Comment::findFirst('id='.$row->target_id);
					if( $comment->status == Comment::STATUS_DELETED ){
						$row->object = '已被删除';
					}
					else{
						$row->object = '<a target="_blank" href="http://'.$pc_host.'/comment/show/?target_type='.$comment->type.'&target_id='.$comment->target_id.'">查看被举报评论所在对象</a>';
					}
					break;
				case Inform::TARGET_TYPE_USER:
					$row->object = '<a target="_blank" href="http://'.$pc_host.'/user/profile/'.$row->target_id.'">查看被举报用户</a>';
					break;
				default:
					break;
			}
			if( $row->status == Inform::INFORM_STATUS_PENDING ){
				$oper = array(
					'<a href="#" data-type="block_reporter" data-id="'.$row->id.'" class="deal_inform">禁言举报者</a>',
					'<a href="#" data-type="block_author" data-id="'.$row->id.'" class="deal_inform">禁言被举报对象作者</a>',
					'<a href="#" data-type="ignore" data-id="'.$row->id.'" class="deal_inform">忽略</a>',
					'<a href="#" data-type="false_report" data-id="'.$row->id.'" class="deal_inform">误报/慌报</a>',
				);
			}
			else{
                $oper = array();
                if($row->oper_by){
				    $processor = User::findfirst('uid='.$row->oper_by);
				    if( $processor ){
				    	$oper = array('<a target="_blank" href="http://'.$pc_host.'/user/profile/'.$row->oper_by.'">'.$processor->username.'</a> 在 '.date('Y-m-d H:i:s', $row->oper_time), $row->oper_result);
				    }
				    else{
				    	$oper = array('什么？处理者用户信息不存在？赶紧找只程序猿报Bug！');
                    }
                }
			}
			$row->oper = implode('<br />', $oper);

			switch ($row->status) {
				case Inform::INFORM_STATUS_IGNORED:
					$statusName = '已忽略';
					$color = 'lightgray';
					break;
				case Inform::INFORM_STATUS_PENDING:
					$statusName = '待处理';
					$color = 'dodgerblue';
					break;
				case Inform::INFORM_STATUS_SOLVED:
					$statusName = '已处理';
					$color = 'springgreen';
					break;
				case Inform::INFORM_STATUS_REPLACED:
					$statusName = '重复举报';
					$color = 'plum';
					break;

				default:
					$statusName = '状态名称不存在？？';
					$color = 'orangered';
					break;
			}
			$row->status = '<span style="color:'.$color.'">'.$statusName.'</span>';
		}


		return $this->output_table($data);
	}

	public function dealAction(){
		$this->noview();
		if( !$this->request->isAjax() ){
			return false;
		}

		$uid = $this->_uid;
		if( !$uid ){
			return ajax_return(0,'error','请先登录');
		}

		$report_id = $this->post( 'id', 'int' );
		$report = Inform::findfirst( 'id='.$report_id );
		if( !$report ){
			return ajax_return(0,'error', '举报记录不存在');
		}
		if( $report->status != Inform::INFORM_STATUS_PENDING ){
			return ajax_return(0,'error','该举报已被处理。');
		}
		$old = ActionLog::clone_obj($report);

		$type = $this->post( 'type', 'string' );
		if( !$type ){
			return ajax_return(0, 'error', '请选择要处理的举报');
		}

		switch( $type ){
			case 'block_reporter':
				$ret = $this->block_reporter($report->uid);
				if( $ret ){
					ActionLog::log(ActionLog::TYPE_FORBID_USER, NULL, $ret);
				}
				$content = '已对举报者实施禁言。';
				$status = Inform::INFORM_STATUS_SOLVED;
				break;
			case 'block_author':
				$ret = $this->block_author($report);
				if( $ret ){
					//获取以前的禁言设置？
					//如果用户不存在……
					ActionLog::log(ActionLog::TYPE_FORBID_USER, NULL, $ret);
				}
				$content = '已对被举报对象作者实施禁言。';
				$status = Inform::INFORM_STATUS_SOLVED;
				break;
			case 'ignore':
				$ret = true;//$this->ignore_report($report);
				$content = '已忽略该举报。';
				$status = Inform::INFORM_STATUS_IGNORED;
				break;
			case 'false_report':
				$ret = true;//$this->false_report($report);
				$content = '已标记该举报为误报。';
				$status = Inform::INFORM_STATUS_IGNORED;
				break;
			default:
				ajax_return(0,'error','不存在的处理类型');
				break;
		}
		if( $ret ){
			$res = $report -> deal_report($report->id, $uid, $content, $status);
			if( $res ){
				ActionLog::log(ActionLog::TYPE_DEAL_INFORM, $old, $res);
			}else{
				$content = '处理失败';
			}
		}
		else{
			$content = '处理失败';
		}

		return ajax_return(1,'okay', $content);
	}

	public function block_reporter($uid){
		$value = 60/*sec*/ * 60/*min*/ * 24/*hours*/ * 7/*days*/;

		if(!$uid) {
			return ajax_return(0, '用户不存在');
		}
		$user = User::findUserByUID($uid);
		if(!$user) {
			return ajax_return(0, '用户不存在');
		}

		return  Usermeta::write_user_forbid($uid, $value);
	}

	public function block_author($report){
		$value = 60/*sec*/ * 60/*min*/ * 24/*hours*/ * 7/*days*/;

		if( !$report ){
			return false;
		}

		$target_type = $report->target_type;
		$target_id = $report->target_id;

		switch( $target_type ){
			case Inform::TARGET_TYPE_ASK:
				$ask = Ask::findfirst('id='.$target_id);
				if( !$ask ){
					return false;
				}
				$uid = $ask -> uid;
				break;
			case Inform::TARGET_TYPE_REPLY:
				$reply = Reply::findfirst('id='.$target_id);
				if( !$reply ){
					return false;
				}
				$uid = $reply -> uid;
				break;
			case Inform::TARGET_TYPE_COMMENT:
				$comment = Comment::findfirst('id='.$target_id);
				if( !$comment ){
					return false;
				}
				$uid = $comment -> uid;
				break;
			case Inform::TARGET_TYPE_USER:
				$uid = $target_id;
				break;
		}


		if(!$uid) {
			return ajax_return(0, '用户不存在');
		}
		$user = User::findUserByUID($uid);
		if(!$user) {
			return ajax_return(0, '用户不存在');
		}

		return  Usermeta::write_user_forbid($uid, $value);
	}
}
