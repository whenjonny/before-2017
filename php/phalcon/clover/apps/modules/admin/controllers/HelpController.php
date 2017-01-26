<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\User;
use Psgod\Models\UserRole;
use Psgod\Models\UserScore;
use Psgod\Models\Label;
use Psgod\Models\Usermeta;
use Psgod\Models\Ask;
use Psgod\Models\ActionLog;
use Psgod\Models\Reply;
use Psgod\Models\Upload;
use Psgod\Models\Review;
use Psgod\Models\Download;

class HelpController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();

    }

    private function get_own_users()
    {
        $role = UserRole::find("role_id=".UserRole::ROLE_WORK." or role_id=".UserRole::ROLE_HELP);
        $uids = array();
        foreach($role as $r){
            $uids[] = $r->uid;
        }
        $users = User::find("uid in (".implode(",", $uids).")");
        $this->view->users = $users;
    }

    public function indexAction() {
        $this->get_own_users();
    }

    public function waitAction() {
        $this->get_own_users();
    }

    public function passAction() {
        $this->get_own_users();
    }

    public function rejectAction() {
    }

    public function releaseAction() {
    }
    public function batchAction() {
    }

    public function uploadAction()
    {
        if ($_FILES["file"]["error"] > 0) {
            //pr($_FILES["file"]["error"]);
            //return ajax_return(0, $_FILES["file"]["error"]);
        }
        $type = $_FILES["file"]["type"];
        if($type != "application/octet-stream"){
            //pr("zip only");
            //return ajax_return(0, "zip file only");
        }
        $tmp = APPS_DIR . "tmp/zips/";

        $file_path = $tmp.md5(time().$_FILES["file"]["name"]).".zip";
        move_uploaded_file($_FILES["file"]["tmp_name"], $file_path);

        $uploads = array();
        //$file_name = "/var/www/clover/apps/tmp/zips/c78c0935c2386fe67cb49ebf48b3514f.zip";
        $zip = zip_open($file_path);
        if ($zip)
        {
            while ($zip_entry = zip_read($zip))
            {
                if (zip_entry_open($zip, $zip_entry))
                {
                    $file_name = iconv('gbk', 'UTF-8', zip_entry_name($zip_entry));
                    $contents = "";
                    while($row = zip_entry_read($zip_entry)){
                        $contents .= $row;
                    }
                    //echo "Name: " . zip_entry_name($zip_entry) . "<br />";
                    //get file name
                    if($contents == "" || sizeof(explode(".", $file_name)) == 1){
                        continue;
                    }
                    //pr($file_name);
                    $savename = $this->cloudCDN->generate_filename_by_file($file_name);

                    $config     = read_config("image");
                    $upload_dir = $config->upload_dir . date("Ym")."/";
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $path = $upload_dir.$savename;
                    file_put_contents($path, $contents);
                    $size = getimagesize($path);
                    $arr = array();
                    $arr['ratio']  = $size[1]/$size[0];
                    $arr['scale']  = $this->client_width/$size[0];
                    $arr['size']   = $size[1]*$size[0];

                    $ret = $this->cloudCDN->upload($path, $savename);
                    if ($ret) {
                        $upload = \Psgod\Models\Upload::newUpload(
                            $file_name,
                            $savename,
                            $ret,
                            $arr
                        );
                        if( $upload ){
                            ActionLog::log(ActionLog::TYPE_UPLOAD_FILE, array(), $upload);
                        }
                        $uploads[] = $upload;
                    }
                    zip_entry_close($zip_entry);
                }
            }
        }
        zip_close($zip);

        $this->view->uploads = $uploads;
    }

    public function list_usersAction() {

        $user = new User;
        // 检索条件
        $cond = array();
        $cond['uid']        = $this->post("uid", "int");
        $cond['username']   = array(
            $this->post("username", "string"),
            "LIKE",
            "AND"
        );
        // $cond['type'] = $this->get("type", "int");

        // 用于遍历修改数据
        $data  = $this->page($user, $cond);
        foreach($data['data'] as $row){
            $row->reg_time = date('Y-m-d H:i:s',$row->create_time);
            $row->sex = get_sex_name($row->sex);
            $row->oper = "<a class='edit'>编辑</a>";
        }
        // 输出json
        return $this->output_table($data);
    }

    public function list_worksAction(){
        $replies = new Reply;
        $del_by = $this->post('del_by');

        if( $del_by ){
            $users = User::find(array( 'columns'=>' GROUP_CONCAT(uid) as uids', 'conditions'=> 'username LIKE \''.$del_by.'%\''));
            $del_by = ($users->toArray()[0]['uids']);
            if( !$del_by ){
                $del_by = null;
            }
        }
        // 检索条件
        $cond = array();
        $cond[get_class($replies).'.status'] = $this->get("status", "int", Reply::STATUS_NORMAL);
        $cond[get_class(new User).'.uid'] = $this->post("uid");
        $cond[get_class(new Reply).'.id'] = $this->post("id");
        $cond[get_class(new Reply).'.del_by'] = array( $del_by, 'IN' );

        $cond[get_class(new User).'.nickname']   = array(
            $this->post("nickname", "string"),
            "LIKE",
            "AND"
        );
        $cond[get_class(new User).'.username']   = array(
            $this->post("username", "string"),
            "LIKE",
            "AND"
        );


        $join = array();
        $join['User'] = 'uid';

        $orderBy = $this->post('sort','string','id DESC');
        if( stristr($orderBy, 'username') || stristr($orderBy, 'nickname') ){
            $orderBy = array(get_class(new User).'.'.$orderBy);
        }

        $data  = $this->page($replies, $cond, $join, $orderBy);

        foreach($data['data'] as $row){
            $row_id = $row->id;
            $row->avatar = "<img width=50 src='".$row->avatar."' />";
            $row->sex = get_sex_name($row -> sex);
            //$row->content = time_in_ago($row->create_time);

            $row->deleteor = '无';
            if( $row->del_by ){
                $deleteor = User::findUserByUID($row->del_by);
                $row->deleteor = $deleteor->username;
            }

            $upload = \Psgod\Models\Upload::findFirst($row->upload_id);
            $upload = $upload->resize('99999999');
            $row->image_url = $upload['image_url'];
            $row->thumb_url = '<div class="wait-image-height">'.$this->format_image($row->image_url, array(
                'type'=>Label::TYPE_REPLY,
                'model_id'=>$row->id
            )).'</div>';

            $row->download_times=count(Download::find('target_id='.$row->id.' AND status='.Download::STATUS_NORMAL));
            $row->reply_count=count(Reply::find('ask_id='.$row->id.' AND status='.Reply::STATUS_NORMAL));
            $row->status = ($row -> status) ? "已处理":"未处理";
            $row->create_time = date('Y-m-d H:i:s', $row->create_time);

            $pc_host = $this->config['host']['pc'];
            $row->oper = "<a class='del' style='color:red' type='".Label::TYPE_REPLY."' data='$row_id'>删除</a>
                <a target='_blank' href='http://$pc_host/ask/show/$row->ask_id'>查看原图</a>";

            $row->recover= "<a class='recover' style='color:green' type='".Label::TYPE_REPLY."' data='$row_id'>恢复</a> ";
        }
        return $this->output_table($data);
    }

    public function list_helpsAction(){
        $asks = new Ask;
        $del_by = $this->post('del_by');

        if( $del_by ){
            $users = User::find(array( 'columns'=>' GROUP_CONCAT(uid) as uids', 'conditions'=> 'username LIKE \''.$del_by.'%\''));
            $del_by = ($users->toArray()[0]['uids']);
            if( !$del_by ){
                $del_by = null;
            }
        }

        // 检索条件
        $cond = array();
        $cond[get_class($asks).'.status'] = $this->get("status", "int", Ask::STATUS_NORMAL);
        $cond[get_class(new User).'.uid'] = $this->post("uid");
        $cond[get_class(new Ask).'.id'] = $this->post("id");
        $cond[get_class(new Ask).'.del_by'] = array( $del_by, 'IN' );
        $cond[get_class(new User).'.nickname']   = array(
            $this->post("nickname", "string"),
            "LIKE",
            "AND"
        );
        $cond[get_class(new User).'.username']   = array(
            $this->post("username", "string"),
            "LIKE",
            "AND"
        );

        $join = array();
        $join['User'] = 'uid';

        $orderBy = $this->post('sort','string','id DESC');
        if( stristr($orderBy, 'username') || stristr($orderBy, 'nickname') ){
            $orderBy = array(get_class(new User).'.'.$orderBy);
        }

        $data  = $this->page($asks, $cond, $join, $orderBy);

        foreach($data['data'] as $row){
            $row_id = $row->id;
            $row->avatar = "<img width=50 src='".$row->avatar."' />";
            $row->sex = get_sex_name($row -> sex);


            $row->content = time_in_ago($row->create_time);

            $row->download_times=count(Download::find('target_id='.$row->id));
            $row->status = ($row -> status) ? "已处理":"未处理";
            $row->create_time = date('Y-m-d H:i:s', $row->create_time);

            $row->deleteor = '无';
            if( $row->del_by ){
                $deleteor = User::findUserByUID($row->del_by);
                $row->deleteor = $deleteor->username;
            }

            $upload = \Psgod\Models\Upload::findFirst($row->upload_id);
            $upload = $upload->resize('99999999');
            $row->image_url = $upload['image_url'];
            $row->thumb_url = '<div class="wait-image-height">'.$this->format_image($row->image_url, array(
                'type'=>Label::TYPE_REPLY,
                'model_id'=>$row->id
            )).'</div>';

            $pc_host = $this->config['host']['pc'];
            $row->oper = "<a class='del' style='color:red' type='".Label::TYPE_ASK."' data='$row_id'>删除</a>
                <a target='_blank' href='http://$pc_host/ask/show/$row_id'>查看原图</a>";

            $row->recover= "<a class='recover' style='color:green' type='".Label::TYPE_ASK."' data='$row_id'>恢复</a> ";
        }
        return $this->output_table($data);
    }

    public function set_statusAction(){
        $this->noview();

        $id = $this->post("id", "int");
        $type   = $this->post("type", "int");
        $status = $this->post("status", "int", Ask::STATUS_DELETED);

        if(!$id or !$type){
		    return ajax_return(0, '请选择具体的内容');
        }

        if($type == Label::TYPE_ASK){
            $model = Ask::findFirst("id=$id");
            $old = ActionLog::clone_obj($model);

            if(!$model){
                return ajax_return(0, '请选择具体的求助信息');
            }

            $res = Ask::update_status($model, $status, '', $this->_uid);
            if( $status == Ask::STATUS_DELETED ){
                ActionLog::log(ActionLog::TYPE_DELETE_ASK, $old, $res);
            }
            else{
                ActionLog::log(ActionLog::TYPE_RECOVER_ASK, $old, $res);
            }
        }
        else {
            $model = Reply::findFirst("id=$id");
            $old = ActionLog::clone_obj($model);
            if(!$model){
                return ajax_return(0, '请选择具体的作品信息');
            }
            $res = Reply::update_status($model, $status, '', $this->_uid);
            if( $status == Reply::STATUS_DELETED ){
                ActionLog::log(ActionLog::TYPE_DELETE_REPLY, $old, $res);
            }
            else{
                ActionLog::log(ActionLog::TYPE_RECOVER_REPLY, $old, $res);
            }

            //对应Ask的reply_count-1
            $ask = Ask::findFirst("id=$model->ask_id");
            if(!$ask){
                return ajax_return(0, '对应求助不存在');
            }
            if($status == Ask::STATUS_DELETED)
                $ask->reply_count -=1;
            else if($status == Ask::STATUS_NORMAL)
                $ask->reply_count +=1;
            $ask->save_and_return($ask);
        }
        return ajax_return(1, 'okay');
    }



    public function set_asksAction(){
        $this->noview();
        $uids       = $this->post("username");
        $uploads    = $this->post("upload");
        $descs      = $this->post("label");
        $hours      = $this->post("hour");
        $mins       = $this->post("min");

        $upload_objs = array();
        foreach ($uploads as $u) {
            $upload = json_decode($u);
            $upload->savename = $upload->name;
            $upload_objs[] = $upload;
        }
        $result = Ask::addNewAsk($uids[0], $descs[0], $upload_objs[0], set_date($hours[0]*3600+$mins[0]*60+time()), Ask::STATUS_READY);
        if($result){
            ActionLog::log(ActionLog::TYPE_POST_ASK, array(), $result);
            $lbl = Label::addNewLabel(
                $descs[0],
                mt_rand(0, 3)/10,
                mt_rand(0, 3)/10,
                $uids[0],
                0,
                $upload_objs[0]->id,
                $result->id,
                Label::TYPE_ASK
            );
        }

        for($i=1; $i<sizeof($uids); $i++){
            $rr = Reply::addNewReply($uids[$i], $descs[$i], $result->ask_id,  $upload_objs[$i], set_date($hours[$i]*3600+$mins[$i]*60+time()), Ask::STATUS_READY);
            if($rr){
                ActionLog::log(ActionLog::TYPE_POST_REPLY, array(), $rr);
                $lbl = Label::addNewLabel(
                    $descs[$i],
                    mt_rand(0, 3)/10,
                    mt_rand(0, 3)/10,
                    $uids[$i],
                    0,
                    $upload_obj[$i]->id,
                    $rr->id,
                    Label::TYPE_REPLY
                );
            }
        }
        ajax_return(1, 'okay');
    }

    public function set_batch_asksAction(){
        $this->noview();
        $data   = $this->post("data");
        $debug = array();

        $current_key = null;
        $ask_id      = null;
        $review      = null;
        foreach($data as $key=>$row){
            if ($current_key == $row['key']) {
                $type = 1;
                $review_id  = $ask_id;
            }
            else {
                $type = 0;
                $review_id  = 0;
                $ask_id     = 0;
            }

            $upload = json_decode($row['upload']);
            $upload->savename = $upload->name;

            // key相同，则表示已经有求p，接着是回复
            $uid    = $this->_uid;
            $parttime_uid   = $row['username'];
            $labels         = $row['label'];
            $release_time = time() + ($row['hour']*3600+$row['min']*60+time());

            $review = Review::addNewReview($type, $parttime_uid, $uid, $review_id, $labels, $upload, $release_time);

            // 当current key不同，即重新开始计算新的求P的时候
            if ($current_key != $row['key']) {
                $ask_id = $review->id;
            }
            $current_key = $row['key'];
        }
        //pr($debug);

        ajax_return(1, 'okay');
    }

    /**
     * [testAction 同步reviews的表数据到ask\reply]
     * @return [type] [description]
     */
    public function testAction(){
        $beg_time = time();
        $this->debug_log->log("开始扫描预发布表:");
        $review = Review::get_review_list(time())->toArray();     // 获取预发布review列表

        $ask_count   = 0;
        $reply_count = 0;
        foreach ($review as $v) {
            $upload_obj = Upload::findFirst("id=".$v['upload_id']);
            $review     = Review::findFirst("id = {$v['id']}");

            if ($review->status != Review::STATUS_NORMAL){
                continue;
            }

            if ($v['type'] == Review::TYPE_ASK){        // ask表
                // ask发布成功 更新review状态
                $result = Ask::addNewAsk($v['parttime_uid'], $v['labels'], $upload_obj);
                //todo: 增加标签
                Review::setReviewAskId($review->id, $result->id);
                $review->status = Review::STATUS_RELEASE;
                if($result && $v['labels'] != ''){
                    $lbl = Label::addNewLabel(
                        $v['labels'],
                        mt_rand(0, 3)/10,
                        mt_rand(0, 3)/10,
                        $v['parttime_uid'],
                        0,
                        $upload_obj->id,
                        $result->id,
                        $v['type']
                    );
                }
                $ask_count ++;
            }
            else if($v['ask_id'] > 0){          // reply表
                $result = Reply::addNewReply($v['parttime_uid'], $v['labels'], $v['ask_id'], $upload_obj);
                $review->status = Review::STATUS_RELEASE;
                if($result && $v['labels'] != ''){
                    $lbl = Label::addNewLabel(
                        $v['labels'],
                        mt_rand(0, 3)/10,
                        mt_rand(0, 3)/10,
                        $v['parttime_uid'],
                        0,
                        $upload_obj->id,
                        $result->id,
                        $v['type']
                    );
                }
                $reply_count ++;
            }

            $review->update_time = time();
            $review->save();
        }
        $this->noview();
        echo 'Done!';
        echo "<br>";
        echo 'Ask'.$ask_count;
        echo "<br>";
        echo 'Reply'.$reply_count;
        $this->debug_log->log("结束扫描预发布表,ask:$ask_count,reply:$reply_count,time:".time()-$beg_time);
    }
}
