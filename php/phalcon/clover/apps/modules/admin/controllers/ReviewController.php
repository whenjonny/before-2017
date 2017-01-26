<?php
namespace Psgod\Admin\Controllers;

use Psgod\Models\User;
use Psgod\Models\UserRole;
use Psgod\Models\Usermeta;
use Psgod\Models\Role;
use Psgod\Models\Review;
use Psgod\Models\Upload;
use Psgod\Models\ActionLog;

class ReviewController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();

        $users = UserRole::get_users_in(array(
            UserRole::ROLE_WORK,
            UserRole::ROLE_HELP
        ));
        $work_uids  = array();
        $help_uids  = array();
        foreach($users as $user){
            if($user->role_id == UserRole::ROLE_WORK){
                $work_uids[] = $user->uid;
            }
            else {
                $help_uids[] = $user->uid;
            }
        }
        $this->view->helps = $help_uids;
        $this->view->works = $work_uids;
        $this->view->users = $users;
    }

    public function indexAction()
    {

    }

    public function waitAction() {

    }

    public function passAction() {

    }

    public function rejectAction() {

    }

    public function releaseAction() {

    }
    public function batchAction()
    {

    }

    /**
     * 列举需要审核的批量发布
     */
    public function list_reviewsAction()
    {
        $cond = array();

        $uid = $this->post('uid','int');
        if( $uid ){
            $cond['parttime_uid'] = $uid;
        }

        $username = $this->post('username', 'string');
        $nickname = $this->post('nickname', 'string');

        $review = new Review;
        // 检索条件
        //$cond['Psgod\Models\Review.type']  = $this->get("type", "int", Review::TYPE_ASK);
        $cond[get_class($review).'.status']  = $this->get("status", "int", Review::STATUS_NORMAL);

        if( $username ){
            $cond[get_class(new User).'.username'] = array(
                $username,
                "LIKE",
                "AND"
            );
        }

        if( $nickname ){
            $cond[get_class(new User).'.nickname'] = array(
                $nickname,
                "LIKE",
                "AND"
            );
        }

        $join = array();
        $join['Upload'] = array(
            'upload_id', 'id'
        );

        $join['User'] = array( 'parttime_uid', 'uid' );

        // 用于遍历修改数据
        $data  = $this->page($review, $cond, $join);

        $data['replies'] = array();

        foreach($data['data'] as $key => $row){
            $row_id = $row->id;
            $parttimer  = User::findFirst("uid=".$row->parttime_uid);
            if($parttimer){


                $row->parttime_name = "用户名：".$parttimer->username.
                    "<br />昵称：".$parttimer->nickname;
            }
            else {
                $row->parttime_name = "";
            }

            $row->image_view = "";
            if($row->type == Review::TYPE_ASK) {
                $row->image_view = "<div>".$this->format_image($row->savename). '<div>Help:'.$row->labels.'<div></div>';
            }
            else {
                $row->image_view = "<div>".$this->format_image($row->savename).'<div>Work:'.$row->labels.'</div></div>';
            }

            $row->time = "创建：".date("m-d H:i", $row->create_time)."<br>";
            $row->time .= "修改：".date("m-d H:i", $row->update_time)."<br>";
            $row->time .= "<span style='color: red'>发布：".date("m-d H:i", $row->release_time)."</span><br>";
            //$row->time .= "预发布ID：".$row->id;

            switch($cond['Psgod\Models\Review.status']){
            case 0:
            default:
                $row->oper = '
                    <div class="btn-group" >
                        <button class="btn green btn-xs" type="button" data-toggle="dropdown">pass</button>
                        <ul class="dropdown-menu hold-on-click dropdown-radiobuttons" role="menu">
                        <li><label><input type="radio" class="" name="score" value="1">1分</label></li>
                        <li><label><input type="radio" class="" name="score" value="2">2分</label></li>
                        <li><label><input type="radio" class="" name="score" value="3">3分</label></li>
                        <li><label><input type="radio" class="" name="score" value="4">4分</label></li>
                        <li><label><input type="radio" class="" name="score" value="5">5分</label></li>
                        <button class="submit-score btn green btn-xs" type="button" data="'.$row_id.'">确认</button>
                        </ul>
                    </div>
                    <button class="deny btn red btn-xs" type="button" data="'.$row_id.'">deny</button>';
                break;
            case 1:
                $row->oper = "<a class='del' style='color:red' data='$row_id'>删除</a> ";
                break;
            case 2:
                break;
            }
        }

        sort($data['data']);

        // 输出json
        return $this->output_table($data);
    }

    public function set_statusAction(){
        $this->noview();

        $review_id = $this->post("review_id", "int");
        $status    = $this->post("status", "int");
        $data      = $this->post("data", "string", 0);

        if(!isset($review_id) or !isset($status)){
		    return ajax_return(0, '请选择具体的求助信息');
        }

        $review = Review::findFirst("id=$review_id");
        $old = ActionLog::clone_obj( $review );
        if(!$review){
		    return ajax_return(0, '请选择具体的求助信息');
        }
        // 设置状态为正常，等待定时器触发
        $res = Review::update_status($review, $status, $data);
        if( $res ){
            if( $status == Review::STATUS_DELETED ){
                ActionLog::log(ActionLog::TYPE_DELETE_REVIEW, $old, $res );
            }
            //其他状态呢？
        }

        return ajax_return(1, 'okay');
    }

    public function set_batch_asksAction(){
        $this->noview();
        $this->_uid = 1;
        $data   = $this->post("data");
        $debug = array();

        $current_key = null;
        $ask_id      = null;
        $review      = null;
        foreach($data as $key=>$row){
            if ($current_key == $row['key']) {
                $type = Review::TYPE_REPLY;
                $review_id  = $ask_id;
            }
            else {
                $type = Review::TYPE_ASK;
                $review_id  = 0;
                $ask_id     = 0;
            }

            $upload = json_decode($row['upload']);
            $upload->savename = $upload->name;

            // key相同，则表示已经有求p，接着是回复
            //$parttime_uid = 0; //todo: session uid
            $parttime_uid = $row['username'];
            $uid = $this->_uid;
            $labels     = $row['label'];
            $row['hour']    = isset($row['hour']) && is_numeric($row['hour'])?$row['hour']: 0;
            $row['min']     = isset($row['min']) && is_numeric($row['min'])?$row['min']: 0;
            $release_time = $row['hour']*3600+$row['min']*60+time();
            if($row['hour'] == 0 && $row['min'] == 0){
                $release_time = time();
            }

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

    protected function _upload_error(){
        if(empty($_FILES)){
            return "请选择上传文件";
        }
        switch($_FILES['file']['error']) {
            case 1:
                return "文件大小超出了服务器的空间大小";
            case 2:
                return "要上传的文件大小超出浏览器限制";
            case 3:
                return "文件仅部分被上传";
            case 4:
                return "没有找到要上传的文件";
            case 5:
                return "服务器临时文件夹丢失";
            case 6:
                return "文件写入到临时文件夹出错";
            default:
                return "";
        }
    }

    public function uploadAction()
    {
        if ($_FILES["file"]["error"] > 0) {
            echo $this->_upload_error();
            pr($_FILES["file"]["error"]);
            //return ajax_return(0, $_FILES["file"]["error"]);
        }
        if(!is_dev()) {
            $type = $_FILES["file"]["type"];
            if($type != "application/octet-stream" and $type != "application/zip"){
                pr("zip only");
            }
        }
        $tmp = APPS_DIR . "tmp/zips/";

        $file_path = $tmp.md5(time().$_FILES["file"]["name"]).".zip";
        move_uploaded_file($_FILES["file"]["tmp_name"], $file_path);

        $uploads = array();
        $zip = zip_open($file_path);
        if ($zip)
        {
            while ($zip_entry = zip_read($zip))
            {
                if (zip_entry_open($zip, $zip_entry))
                {
                    $file_name  = zip_entry_name($zip_entry);
                    $encode     = mb_detect_encoding($file_name, "auto");
                    if($encode == 'UTF-8')
                    {
                    }
                    else
                    {
                        $file_name = iconv('gbk', 'UTF-8', $file_name);
                    }
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
                        ActionLog::log(ActionLog::TYPE_UPLOAD_FILE, array(), $upload);
                        $uploads[] = $upload;
                    }
                    zip_entry_close($zip_entry);
                }
            }
        }
        zip_close($zip);

        $this->view->uploads = $uploads;
    }
}
