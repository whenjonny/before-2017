<?php
	namespace Psgod\Main\Controllers;
	use Psgod\Models\Inform;
	use Psgod\Models\ActionLog;

	class InformController extends ControllerBase{
		public function report_abuseAction(){
			$errorText = array(
				501=> '请登录。',
				502=> '用户不存在。',
				503=> '请选择举报目标。',
				504=> '举报目标(求助)不存在。',
				505=> '举报目标(作品)不存在。',
				506=> '举报目标(评论)不存在。',
				507=> '举报目标(用户)不存在。',
				508=> '举报目标类型不对。',
				509=> '举报内容不能为空。',
				510=> '举报内容长度少于'.Inform::CONTENT_MIN_LENGTH.'或多于'.Inform::CONTENT_MAX_LENGTH.'字',
				511=> '您已举报过，请勿重复举报。',
			);

			$this->noview();
			if( !$this->request->isAjax() ){
				return false;
			}

			$uid = $this->_uid;
			$target_type = $this->post('target_type','int');
			$target_id = $this->post('target_id','int');
			$content = $this->post('content', 'string');

			$inform = Inform::report( $uid, $target_type, $target_id, $content );


			if( $inform instanceof Inform ){
				ActionLog::log(ActionLog::TYPE_REPORT_ABUSE, array(), $inform);
				return ajax_return(1, 'okay', true);
			}
			if( $inform === true ){
				return ajax_return(1, 'okay', true);
			}

			if( in_array($inform, array_keys($errorText))){
				$ret = $errorText[$inform];
			}
			else{
				$ret = false;
			}
			return ajax_return(1, 'error', $ret);
		}
	}

