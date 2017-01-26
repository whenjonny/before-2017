<?php
	require_once 'UMeng.php';
	class AndroidUMeng extends UMeng{
		protected $platform = 'Android';

		public function __construct(){
			parent::__construct(UMENG_ANDROID_APPKEY, UMENG_ANDROID_MASTER_SECRET);
			$this->payload = array_merge( $this->payload, array(
				'ticker' => '',
				'title' => APP_NAME,
				'text' => '',
				'after_open' => 'go_app'
            ));
		}
		/**
		 * 设置发送内容
		 * @param [string] $key   [description]
		 * @param [mixed] $value [description]
		 */
		public function ticker( $val ){
			$this->setContent('ticker', $val );
			return $this;
		}
		public function title( $val ){
			$this->setContent('title', $val );
			return $this;
		}
		public function text( $val ){
			$this->setContent('text', $val );
			return $this;
		}
		public function after_open( $val ){
			$this->setContent('after_open', $val );
			return $this;
		}
		public function set_url( $val ){
			$this->setContent('url', $val );
			return $this;
		}
		public function set_activity( $val ){
			$this->setContent('activity', $val );
			return $this;
		}
		public function set_custom( $val ){
			$this->setContent('custom', $val );
			return $this;
		}

		protected function getSettings(){
			$data = array(
				'text' => $this->payload['text'],
				'title' => APP_NAME,
				'ticker' => $this->payload['text'],
				'after_open' => $this->payload['after_open']
            );

            // 默认title为APP_NAME
            if($this->payload['title'] != '') {
				$data['title'] = $this->payload['title'];
            }
            // 默认ticker为text
            if($this->payload['ticker'] != '') {
				$data['ticker'] = $this->payload['ticker'];
            }
            if($this->payload['after_open'] == 'go_url'){
                $data['url'] = $this->payload['url'];
            }
            if($this->payload['after_open'] == 'go_activity'){
                $data['actiity'] = $this->payload['activity'];
            }
            if($this->payload['after_open'] == 'go_custom'){
                $data['custom'] = $this->payload['custom'];
            }

            return $data;
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
