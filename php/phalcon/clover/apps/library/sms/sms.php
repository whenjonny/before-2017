<?php

	class Msg{
		protected $WSDL = "http://211.147.239.62/Service/WebService.asmx?wsdl";
		const SEND_MANY = 0;
		const SEND_ONE = 1;
		const SEND_TYPE_SMS = 1; //短信
		const SEND_TYPE_MMS = 2; //彩信
		const SEND_TYPE_WAP = 3; //WAPPUSH

		private $client;
		protected $account = '' ;
		protected $password = '';

		protected $phone = '';
		protected $content = '';

		protected $uuid = '';
		protected $batchName = '';
		protected $remark = '';

		protected $errorMessages = NULL;

		public function getError(){
			return $this->errorMessages;
		}

		private function guid(){
		    if (function_exists('com_create_guid')){
		        return com_create_guid();
		    }else{
		        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		        $charid = strtoupper(md5(uniqid(rand(), true)));
		        $hyphen = chr(45);// "-"
		        $uuid = substr($charid, 0, 8).$hyphen
		                .substr($charid, 8, 4).$hyphen
		                .substr($charid,12, 4).$hyphen
		                .substr($charid,16, 4).$hyphen
		                .substr($charid,20,12);
				
		        return $uuid;
		    }
		}

		public function __construct( $account = XW_USERNAME, $password = XW_PASSWORD ){
			$this->client = new SoapClient( $this->WSDL );
			$this->account = $account;
			$this->password = $password;
			$this->uuid = $this->guid();
		}

		public function to( $num ){
			return $this->phone( $num );
		}

		public function phone( $num ){
			$this->phone = $num;
			return $this;
		}

		public function remark( $remark ){
			$this->remark = $remark;
			return $this;
		}

		public function batchName( $batchName ){
			$this->batchName = $batchName;
			return $this;
		}

		public function content( $content ){
			$this->content = $content;
			return $this;
		}

		private function getMsgData() {
			if( empty( $this->phone ) ){
				$this->errorMessages = '手机号不能为空';
				return false;
			}

			if( empty( $this->content) ){
				$this->errorMessages = '发送内容不能为空';
				return false;
			}

			return array('MessageData'=>
					array(
					'Phone' => $this->phone,
					'Content' => $this->content,
					'vipFlag' => false,
					'customMsgID' => '',
					'customNum' => ''
				)
			);
		}

		private function getPacket(){
			return array(
				'uuid' => $this->uuid,
				'batchID' => $this->uuid,
				'batchName' => $this->batchName,
				'sendType' => self::SEND_ONE,
				'msgType' => self::SEND_TYPE_SMS,
				'msgs' => $this->getMsgData(),
				'bizType' => '',
				'distinctFlag' => true,
				'scheduleTime' => '',
				'deadline' => ''
			);
		}

		private function getPoints(){
			return array(
				'account' => $this->account,
				'password' => $this->password,
				'mtpack' => $this->getPacket()
			);
		}
		public function send(){
			$points = $this->getPoints();
			$res = $this->client->Post( $points );

			if( $res->PostResult->result == 0){
				return true;
			}
			else{
				$this->errorMessages = $res->PostResult->result . $res->PostResult->message;
				return false;
			}
		}
	}
