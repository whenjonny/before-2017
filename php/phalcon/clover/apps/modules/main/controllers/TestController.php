<?php
namespace Psgod\Main\Controllers;

use Psgod\Models\User;
use Psgod\Models\Role;
use Psgod\Models\UserRole;

class TestController extends ControllerBase
{

    public function indexAction()
    {
        $this->noview();
        echo 'from main test';
        $uid = $this->_uid;
        dump(UserRole::check_authentication($uid, Role::TYPE_PARTTIME));
        exit;
        # $ret = \Psgod\Models\Usermeta::writeUserMeta(2, 'ask_count', 110, true);
        $ret = \Psgod\Models\Usermeta::readUserMeta(2);

        dump($ret);

        echo base64_encode($this->crypt->encrypt('Anye'));
        $u = new User();
        $meta = $u->getReadConnection()->describeColumns($u->getSource());
        dump($meta);
        // $dump($ret);
        // $this->logger->log('hello');
        // echo User::count();
        // // //echo APP_DIR;
        // $openid = 'A356839BD58D8AC0BF4E3C2A6090BB3A';
        // $openkey= '80DDAA8AC4763A51BDA44D9CE480C53B';
        // // $pf = 'qzone';
        // // $ret = $this->txsdk->api('/v3/user/get_info', array('openid'=> $openid, 'openkey'=> $openkey, 'pf'=> $pf));
        // // echo json_encode($ret);
        // $info = '{"ret":0,"is_lost":0,"nickname":"\u76d6\u8328\u6697\u591c","gender":"\u7537","country":"\u4e2d\u56fd","province":"\u5e7f\u4e1c","city":"\u6df1\u5733","figureurl":"http:\/\/thirdapp1.qlogo.cn\/qzopenapp\/b025b6dd00a560fc308fe86602462a04c9837a652ed588c35bd2785c26391fc9\/50","is_yellow_vip":0,"is_yellow_year_vip":0,"yellow_vip_level":0,"is_yellow_high_vip":0}';
        // $ret = json_decode($info, true);
        // $user = Users::addQQOpenUser(array_merge($ret, array('openid'=>$openid)));
        // # dump($user, true, 123);
        
    }

    public function replyAction()
    {
        $this->noview();

        // $upload = \Psgod\Models\Upload::findFirst(8);
        // $ret = \Psgod\Models\Reply::addNewReply(current_user('uid'), '请过目', 47, $upload);
        $r = \Psgod\Models\Reply::findFirst(2);


        dump(basename($r->image_url));
    }

    public function metaAction()
    {
        $this->noview();

        $ask = \Psgod\Models\Ask::findFirst();
        \Psgod\Models\Askmeta::writeMeta($ask->id, 'xx_inform_count', 110);
        dump(\Psgod\Models\Askmeta::readMeta($ask->id, 'xx_inform_count'));
    }

    public function dataAction()
    {
        $this->noview();
        // $users = \Psgod\Models\User::find();
        // $upload= \Psgod\Models\Upload::findFirst();
        // foreach ($users as $user) {
        //     for ($i=0; $i < mt_rand(2, 5); $i++) { 
        //         $ret = \Psgod\Models\Ask::addNewAsk($user->uid, rand_string(mt_rand(6, 15), 4), $upload);
        //         echo $ret->ask_id;
        //     }
        // }
        // $user = new \Psgod\Models\User();
        // $ret = $user->addNewUser('chenanye', 'chenanye', '安业', '18718574429', 'me@gatesanye.com');
        // dump($ret->uid);
        $auth = <<<STR
{ 
"openid":"OPENID",
"nickname":"NICKNAME",
"sex":1,
"province":"PROVINCE",
"city":"CITY",
"country":"COUNTRY",
"headimgurl": "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
"privilege":[
"PRIVILEGE1", 
"PRIVILEGE2"
],
"unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"

}
STR;
        $user = User::addAuthUser(18718574429, 'chenanye', 110, 110, '暗夜', 
            'http://7u2nwa.com1.z0.glb.clouddn.com/avatar.jpg', $auth);

        if ($user) {
            dump($user->toArray());
        } else {
            echo '保存失败';
        }
    }

    public function testAction(){
        $this->noview();
        echo "1";
        echo $this->security->hash('123');
        // dump(set_date(time()));
        // $password = $this->security->hash('123');
        // dump($password);
        // dump(set_date(time()));
        // dump($this->security->checkHash('123',$password));
        // dump(set_date(time()));
    }

}
