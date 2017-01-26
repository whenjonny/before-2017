<?php
	require_once 'UMeng.php';
	
	class iOSUMeng extends UMeng{
		protected $platform = 'IOS';

		public function __construct(){
			parent::__construct(UMENG_IOS_APPKEY, UMENG_IOS_MASTER_SECRET);
			$this->payload = array_merge( $this->payload, array(
				'badge' => 0,
				'sound' => 'chime',
				'alert' => ''
			));
		}

		protected function getSettings(){
			return $this->payload;
		}

		public function badge( $val ){
			return $this->setContent('badge', $val );
		}

		public function alert( $val ){
			return $this->setContent('alert', $val );
        }

        // 为了统一推送内容，增加一个text接口
		public function text( $val ){
			return $this->setContent('alert', $val );
		}

		public function sound( $val ){
			return $this->setContent('sound', $val );
		}

		public function send(){
			$this->beforeSend();

			try{
				$this->Notification->send();
				return true;
			}
			catch(Exception $e ){
				$this->errorMessages = $e->getMessages();
				return false;
			}
		}
	}
