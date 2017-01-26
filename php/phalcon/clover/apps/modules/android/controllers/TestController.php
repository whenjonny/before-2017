<?php

namespace Psgod\Android\Controllers;

use Psgod\Models\User;
class TestController extends ControllerBase
{
    public $_allow = array(
        'index'
    );

    public function umengAction(){
        $Umeng = new \AndroidUMeng();
        $Umeng->text('吾乃推送消息之正文2')
               ->broadcast()
               ->after_open('go_app')
               ->send();

        $Umeng = new \iOSUMeng();
        $Umeng->text('我是内容')
              ->broadcast()
              ->send();

        echo "<pre>";
        var_dump( $Umeng->getError() );
    }

    public function indexAction()
    {
        $start = microtime(true);

        $arr = array();
        for($i = 0; $i < 100; $i ++){
            $arr[] = $this->cache->get("test$i");
        }
        $end = microtime(true);

        echo "Total Time: ".($end - $start)."<br />";
        exit;

        $qid = $this->queue->put(array('processVideo' => 4871));
        $job = $this->queue->reserve();
        $message = $job->getBody();
        pr($message);
/*
 *      $queue->put(
 *          array('processVideo' => 4871),
 *          array('priority' => 250, 'delay' => 10, 'ttr' => 3600)
 *      );
        while (($job = $this->queue->reserve())) {

            $message = $job->getBody();

            var_dump($message);

            $job->delete();
        } 
*/
        pr($this->_user->toArray());
        // //echo APP_DIR;
        $openid = 'A356839BD58D8AC0BF4E3C2A6090BB3A';
        $openkey= '80DDAA8AC4763A51BDA44D9CE480C53B';
        // $pf = 'qzone';
        // $ret = $this->txsdk->api('/v3/user/get_info', array('openid'=> $openid, 'openkey'=> $openkey, 'pf'=> $pf));
        // echo json_encode($ret);
        $info = '{"ret":0,"is_lost":0,"nickname":"\u76d6\u8328\u6697\u591c","gender":"\u7537","country":"\u4e2d\u56fd","province":"\u5e7f\u4e1c","city":"\u6df1\u5733","figureurl":"http:\/\/thirdapp1.qlogo.cn\/qzopenapp\/b025b6dd00a560fc308fe86602462a04c9837a652ed588c35bd2785c26391fc9\/50","is_yellow_vip":0,"is_yellow_year_vip":0,"yellow_vip_level":0,"is_yellow_high_vip":0}';
        $ret = json_decode($info, true);
        $user = Users::addQQOpenUser(array_merge($ret, array('openid'=>$openid)));
        # dump($user, true, 123);
        
        // echo ThirdParties::count(array('openid'=>$openid, 'party'=>'qqopen'));
    }

}
