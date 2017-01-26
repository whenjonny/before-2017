<?php
namespace Psgod\Android\Controllers;

use Psgod\Models\ActionLog;
use Psgod\Models\Feedback;
use Psgod\Models\User;

class FeedbackController extends ControllerBase{
	public function saveAction(){
        $uid = $this->_uid;
        if(empty($uid)){
			return ajax_return(0, '用户id不能为空', 2);
        }

		$content = $this->post('content', 'string');
		if(empty($content)){
			return ajax_return(0, '反馈内容不能为空', 3);
        }

		$contact = $this->post('contact', 'string');

		if( empty( $contact ) ){
			$contact = User::findFirst(array('conditions'=>'uid='.$uid,'columns'=>'phone'))->phone;
		}


        $fbModel = new Feedback();
		$fb = $fbModel->new_feedback( $content, $contact, $uid );
		if( $fb ){
			ActionLog::log(ActionLog::TYPE_ADD_FEEDBACK, array(), $fb);
		}
		return ajax_return(1, '反馈成功', (bool)$fb);
	}

}
