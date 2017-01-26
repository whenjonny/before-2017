<?php
namespace Psgod\Android\Controllers;

use Psgod\Models\App;
use Psgod\Models\Label;
use Psgod\Models\Ask;
use Psgod\Models\Reply;

class AppController extends ControllerBase{
    public function get_app_listAction(){
        $this->noview();
        
        $app = new App();
        $app_list_json = json_decode(json_encode($app->get_list()));
        $app_list = array();
        foreach($app_list_json as $k => $v){
            $app_list[$k] = array();
            $app_list[$k]['app_name'] = $v->app_name;
            $app_list[$k]['jump_url'] = $v->jumpurl;
            $app_list[$k]['logo_url'] = get_cloudcdn_url($v->savename);
        }
        return ajax_return(1, 'ok',  $app_list );
    }

    public function shareAction() {
        // 类型: 普通分享，上传之后的分享
        $share_from = $this->get('share_from', 'string');
        $share_type = $this->get('share_type', 'string');
        $type       = $this->get('type', 'int');
        $target_id  = $this->get('target_id', 'int');
        $width      = $this->get('width', 'int', 320);

        if(!$target_id) {
            return ajax_return(0, '目标id不存在');
        }
        if(!$type) {
            return ajax_return(0, '请确定是要分享求助还是作品');
        }

        $data = array();
        $mobile_host = $this->config['host']['mobile'];

        if($type == Label::TYPE_ASK) {
            $item = Ask::findFirst($target_id);
        }
        else if($type == Label::TYPE_REPLY) {
            $item = Reply::findFirst($target_id);
        }

        $labels = $item->get_labels();
        $content= array();
        foreach($labels as $label){
            $content[] = $label->content;
        }
        

        switch( $share_type )  {
        case 'weibo':
            $data = $this->image($item, $width);
            $data['desc'] = implode(",", $content)." ".$data['url'];
            break;
        case 'wechat':
            $data = array();
            if($type == Label::TYPE_ASK && $item->reply_count == 0) {
                $data = $this->invite($item, $width);
            }
            else if($type == Label::TYPE_REPLY){
                $data = $this->image($item, $width);
            }
            else {
                $data = $this->url($item, $width);
            }
            break;
        case 'moments':
            if($type == Label::TYPE_ASK){
                $data = $this->url($item, $width);
            }
            else {
                $data = $this->image($item, $width);
            }
            break;
        default:
            $data = $this->url($item, $width);
            break;
        }

        if(!isset($data['desc']) || $data['desc'] == ''){
            $data['desc'] = implode(',', $content); 
        }
        $data['title'] = APP_NAME;

        return ajax_return(1, 'okay', $data);
    }

    private $host;
    public function initialize() {
        $this->host = $this->config['host']['mobile'];
    }

    private function url($item, $width){
        if(isset($item->ask_id) && $item->ask_id != ''){
            $url  = 'http://'.$this->host."/reply/share/".$item->id;
        }
        else {
            $url  = 'http://'.$this->host."/ask/share/".$item->id;
        }
        //$url  = 'http://'.$this->host."/ask/share/".$item->id;

        $image = $item->upload->resize($width);
        $data['type']   = 'url';
        $data['image']  = $image['image_url'];
        $data['url']    = $url;
        return $data;
    }

    private function image($item, $width) {
        if(isset($item->ask_id) && $item->ask_id != ''){
            $url  = 'http://'.$this->host."/reply/share/".$item->id;
        }
        else {
            $url  = 'http://'.$this->host."/ask/share/".$item->id;
        }

        $rlt = $this->http_get('http://'.$this->host.':8808/?url='.$url);
        if($rlt) {
            $rlt = json_decode($rlt);
            $data['type']  = 'image';
            $data['image'] = 'http://android.qiupsdashen.com/images/'.$rlt->image_url;
        }
        else {
            $image = $item->upload->resize($width);
            $data['type']   = 'url';
            $data['image']  = $image['image_url'];
        }
        $data['url']    = $url;

        return $data;
    }

    private function invite($item, $width) {
        $uid   = $item->uid;
        $user  = User::findFirst($uid);
        $image = $item->upload->resize($width);

        $data = array();
        $data['desc']   = $user->nickname.' 邀请你帮忙完成这个求助需求';
        $data['type']   = 'url';
        $data['image']  = $image['image_url'];
        $data['url']    = 'http://'.$this->host.'/ask/invite/'.$item->id;

        return $data;
    }

    private function http_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents() ;
        ob_end_clean();
         
        return $result;
    }

}
