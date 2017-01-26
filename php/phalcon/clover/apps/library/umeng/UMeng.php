<?php
	define('UMENG_LIB_PATH', pathGen(array(dirname(__FILE__), 'notification' ) ) );
	abstract class UMeng{
		protected $appkey           = NULL; 
		protected $appMasterSecret  = NULL;
		protected $timestamp        = NULL;
		protected $validation_token = NULL;
		protected $production_mode  = 'false';
		protected $Notification;
		protected $platform;

		protected $castType  = '';
		protected $extra = array();
		protected $payload = array();

		protected $errorMessages = '';

		const TYPE_BROADCAST = 'Broadcast';
		const TYPE_UNICAST	 = 'Unicast';
		const TYPE_GROUPCAST = 'Groupcast';
		const TYPE_LISTCAST  = 'Listcast';

		public function __construct($key , $secret) {
			if( empty($key) ){
				$this->errorMessages = 'Key不能为空';
				return false;
			}
			$this->appkey = $key;
			$this->appMasterSecret = $secret;
			$this->timestamp = strval(time());

			$this->castType = self::TYPE_BROADCAST;
		}

		public function getError(){
			return $this->errorMessages;
		}

		public function debug( $debug = false ){
			return $this->setContent('production_mode', $debug==true?"true":"false" );
		}
		/**
		 * [setCastType 设置广播方式]
		 * @param [constant] $castType [广播类型]
		 */
		private function setCastType( $castType ){
			$this->castType = $castType;
			return $this;
		}

		public function broadcast(){
			return $this->setCastType( self::TYPE_BROADCAST );
		}

		public function unicast( $device_token ){
			return $this->listcast( $device_token );
		}

		public function groupcast( $filter ){
			if(!is_array($filter) ){
				return false;
			}
			$this->setContent('filter', $filter );
			return $this->setCastType( self::TYPE_GROUPCAST );
        }

		public function listcast( $device_tokens ){
			if( is_string($device_tokens) ){
				$device_tokens = explode(',', $device_tokens );
			}

			if( is_array($device_tokens) ){
				if( count($device_tokens) > 500 ){
					$this->errorMessages = '列播时，token个数过多';
					return false;
				}
				$device_tokens = implode(',', $device_tokens );
				$this->setContent('device_tokens', $device_tokens);
			}
			else{
				$this->errorMessages = '参数错误';
				return false;
			}


			//listcast==unicast
			return $this->setCastType( self::TYPE_UNICAST );
		}

		public function setContent( $key, $value ){
			$this->payload[$key] = $value;
			return $this;
		}

		/**
		 * 额外数据
		 * @param  [type] $key   [description]
		 * @param  [type] $value [description]
		 * @return [type]        [description]
		 */
		public function extra( $key, $value = null ){
			$extra = $this->extra;
			if( is_array($key) ){
				array_merge($extra, $key );
			}
			else if( is_string($key) ){
				if( $value == null && in_array($key, $extra) ){
					 unset($extra[$key]);
				}
				else{
					$extra[$key] = $value;
				}
			}
			else{
				$this->errorMessages = '不支持往extra插入的数据类型'.gettype($key);
				return false;
			}
			$this->extra = $extra;
			return $this;
		}

		protected abstract function getSettings();

		protected function beforeSend(){
            $className = $this->importFile();
            
            //初始化Notification
            $Notification = new $className();
			$Notification->setAppMasterSecret( $this->appMasterSecret );
			$Notification->setPredefinedKeyValue("appkey",    $this->appkey    );
			$Notification->setPredefinedKeyValue("timestamp", $this->timestamp );
			$Notification->setPredefinedKeyValue("production_mode", $this->production_mode );

			//配置参数
            $settings = $this->getSettings( );
			foreach( $settings as $name => $value){
				$Notification->setPredefinedKeyValue( $name, $value );
			}

			switch( $this->castType ){
				case self::TYPE_UNICAST:
				case self::TYPE_LISTCAST:
					$Notification->setPredefinedKeyValue("device_tokens", $this->payload['device_tokens']);
					break;
				case self::TYPE_GROUPCAST:
					$Notification->setPredefinedKeyValue("filter", $this->payload['filter']);
					break;
			}

			//配置额外参数
			$settings = $this->extra;
			foreach( $settings as $name => $value){
				$Notification->setExtraField( $name, $value );
			}

			$this->Notification = $Notification;
		}

		protected function importFile(){
			//请求文件
            $className = $this->platform . $this->castType; 
            $filename =  $className . '.php';
            $path = pathGen( array( UMENG_LIB_PATH, strtolower($this->platform), $filename ) );
			if( file_exists($path) ){
				require_once($path);
				return $className;
			}
			
			$this->errorMessages = '文件'.$path.'不存在';
			return false;
		}

		public function desc($text){
			if(!is_string($text)){
				return false;
			}

			return $this->setContent('description', $text );
		}


		/**
		 * 设定显示类型
		 * notify为提示，
		 * msg为消息
		 */
		private function setDisType( $type ){
			return  $this->setContent('display_type', $type);
		}
		public function notify(){
			return $this->setDisType( 'notification' );
		}
		public function msg(){
			return $this->setDisType( 'message' );
		}
	}

	function pathGen( $folders = array() ){
		return implode( DIRECTORY_SEPARATOR, $folders);
    }
