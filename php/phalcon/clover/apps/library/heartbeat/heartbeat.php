<?php
class Heartbeat {
    const DB_PHP_SESSION    = 11;//该DB为session服务专用，切勿使用
    
    const TIMEOUT           = 2;//链接超时

    const DB_LOGON          = 1;    //后台登陆
    const DB_PROCESS        = 2;    //持有，解决并发
    #const DB_LOGON_SUP     = 2;    //sup登录
    #const DB_PAY           = 3;    //支付

    const CACHE_ASK     = 'ask';
    const CACHE_REPLY   = 'reply';
    const CACHE_COMMENT = 'comment';
    const CACHE_FEEDBACK= 'feedback';
    const CACHE_MESSAGE = 'message';
    const CACHE_INFORM  = 'inform';
    const CACHE_NOTIFICATION    = 'notification';
    const CACHE_SCHEDULING      = 'scheduling';
    const CACHE_SYSTEM  = 'system';

    public static function data() {
        return array(
            self::CACHE_ASK,
            self::CACHE_REPLY,
            self::CACHE_COMMENT,
            self::CACHE_FEEDBACK,
            self::CACHE_MESSAGE,
            self::CACHE_INFORM,
            self::CACHE_NOTIFICATION,
            self::CACHE_SCHEDULING,
            self::CACHE_SYSTEM
        );
    }
    static $ins = array(); //单例
    private $r;
    
    public static function init($db) {
        if (!isset(self::$ins[$db])) {
            self::$ins[$db] = new self($db);
        }
        return self::$ins[$db];
    }
    
    private function __construct($db) {
        if (!class_exists('Redis')) {
            return;
        }

        /**
         * cache 在service中实例化
         */
        $this->r = get_di('cache');
        $this->r->select($db);
        //$this->r->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);
    }
    
    /**
     * 心跳包操作
     * @input $id 账户ID
     * @input $key 用户登录唯一标识（建议使用session_id）
     */
    public function hello($id, $key = null) {
        if (!isset($this->r)) {
            return;
        }
        $time = $_SERVER['REQUEST_TIME'];
        $this->r->save(
            $id, 
            $key ? "$time|$key": $time, 
            HEARTBEAT_EXPIRE 
        );
    } 

    /**
     * 退出登录
     * @input $id 账户ID
     */
    public function logout ($id) {
        if (!isset($this->r)) {
            return;
        }

        $this->r->delete($id);
    }
    
    /**
     * 获取最近在线时间
     * @input $id 账户ID
     * @input $key 用户登录唯一标识（建议使用session_id），默认匹配任意用户
     */
    public function last_ontime ($id, $key = '') {
        if (!isset($this->r)) {
            return NULL;
        }
        
        $val = $this->r->get($id);
        if (false == $val || false === strstr($val, '|'. $key)) {
            $val = 0;
        }
        
        return strstr($val, '|'.$key, TRUE);
    }

    /**
     * 获取在线人数
     */
    public function online_count () {
        if (!isset($this->r)) {
            return 1;
        }
        
        $ids  = $this->r->queryKeys("*");
        return sizeof($ids);
    }

    /**
     * 剩余需要审核的数量
     */
    public function num ($key, $online_count) {
        $ids  = $this->r->queryKeys("{$key}_*");
        return sizeof($ids)/(($online_count)?$online_count: 1);
    }

    /**
     * 通过类型，获取未被锁定的订单
     * @input $key 搜索关键字
     */
    public function fetch ($key, $online_count) {
        if (!isset($this->r)) {
            return array();
        }
        $session_id = session_id();

        $data = array();
        $num  = 0;
        $ids  = $this->r->queryKeys("{$key}_*");
        $count= sizeof($ids)/(($online_count)?$online_count: 1);

        // replace as key
        foreach( $ids as $id ){
            $val = $this->r->get($id);
            $arr = explode("|", $val);
            $time   = $arr[0];

            // 超时丢回队列，并给其他人获取
            if( sizeof($arr) == 1 && $num < $count ) {
                $this->r->save($id, $_SERVER['REQUEST_TIME'].'|'.$session_id, EXPIRE_TIME);
                // 获取在数据库里面的键值
                $data[] = substr($id, strlen($key) + 1);
                $num ++;
            }
            else if( sizeof($arr) == 2 && time() > VERIFY_EXPIRE + $time ) {
                // 超时，可以被其他用户获取
                if( $num < $count ) {
                    pr($num);
                    $this->r->save($id, $_SERVER['REQUEST_TIME'].'|'.$session_id, EXPIRE_TIME);
                    // 获取在数据库里面的键值
                    $data[] = substr($id, strlen($key) + 1);
                    $num ++;
                }
                else {
                    $this->r->save ($id, $_SERVER['REQUEST_TIME'], EXPIRE_TIME);
                }
            }
            else if( sizeof($arr) == 2 && $session_id == $arr[1] ){
                $data[] = substr($id, strlen($key) + 1);
                $num ++;
            }
            
        }
        return $data;
    }
    
    /**
     * 添加单
     * @input $cache_type
     * @input $key
     */
    public function append($cache_type, $id) {
        if (!isset($this->r)) {
            return;
        }

        $time = $_SERVER['REQUEST_TIME'];
        return $this->r->save(
            $cache_type."_".$id, 
            $time, 
            EXPIRE_TIME
        );
    } 

    /**
     * 结束
     * @input $id 
     */
    public function remove($id) {
        if (!isset($this->r)) {
            return;
        }

        $this->r->delete($id);
    } 

}
