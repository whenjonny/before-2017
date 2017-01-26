<?php
namespace Psgod\Traits;

/**
 * 文件上传
 */
trait ImageUpload
{
    /**
     * 上传图片到七牛并且返回 json string
     *
     * @return json string
     */
    public function uploadAction()
    {
        $ret = $this->_upload_cloudCDN();
        call_user_func_array('ajax_return', $ret);
    }

    /**
     * 上传图片到服务器本地并且预览
     *
     * @return json string
     */
    public function previewAction()
    {
        $this->noview();

        $config     = read_config("image");
        $upload_dir = $config->upload_dir . date("Ym")."/";
        $preview_dir= $config->preview_dir . date("Ym")."/";
        $allow_ext  = (array)$config->allow_ext;

        if ($this->request->hasFiles() == true) {
            foreach ($this->request->getUploadedFiles() as $file) {
                if(!DEV){
                    $ext = $file->getExtension();
                    if(!in_array($ext, $allow_ext)){
                        return ajax_return(0, '上传失败，文件类型错误');
                    }
                }

                //get file name
                $save_name = $this->cloudCDN->generate_filename_by_file($file->getName());
                $size   = $this->_save_file($file, $upload_dir, $save_name);

                $upload = \Psgod\Models\Upload::newUpload($file->getName(), $save_name, $preview_dir, $size);
                if ($upload) {
                    ajax_return(1, 'okay', array(
                        'url'=>$preview_dir . $save_name,
                        'id'=>$upload->id,
                        'name'=>$file->getName(),
                        'ratio'=>$size['ratio']
                    ));
                } else {
                    ajax_return(0, '上传成功但保存失败', array('url'=>$preview_dir . $save_name));
                }
            }
        } else {
            ajax_return(0, $this->_upload_error());
        }
    }

    /**
     * 切割图片
     *
     * @return json string
     */
    public function cropAction()
    {
        $this->noview();

        /**
         * 切割图片
         */
        $bounds = $this->post("bounds", "float");
        $scale  = $this->post("scale", "float");
        $upload_id  = $this->post("upload_id", "int");

        $jpeg_quality = 90;

        $config = read_config("image");
        $public_dir     = $config->public_dir;
        $preview_dir    = $config->preview_dir;

        $upload = \Psgod\Models\Upload::findFirst("id=" . $upload_id);
        $src = $public_dir.$upload->pathname.$upload->savename;

        $size = getimagesize($src);
        $type = $size['mime'];
        // 比例参数
        $k = $size[0]/$bounds[0];
        $dst_w  = $scale['w']*$k;
        $dst_h  = $scale['h']*$k;

        if($dst_w != 0 && $dst_h != 0){
            $src_x  = $scale['x']*$k;
            $src_y  = $scale['y']*$k;
            $src_w  = $size[0];
            $src_h  = $size[1];

            switch($type){
            case "image/png":
                $img_r = imagecreatefrompng($src);
                break;
            case "image/jpg":
            case "image/jpeg":
                $img_r = imagecreatefromjpeg($src);
                break;
            case "image/gif":
                $img_r = imagecreatefromgif($src);
                break;
            }
            $dst_r = ImageCreateTrueColor($dst_w, $dst_h );

            imagecopyresampled($dst_r, $img_r, 0, 0,
                $src_x, $src_y, $dst_w, $dst_h, $dst_w, $dst_h);

            imagejpeg($dst_r, $src, $jpeg_quality);
        }
        //$save_name = $this->cloudCDN->generate_filename_by_file($upload->filename);
        $save_name = $upload->savename;
        $ret = $this->cloudCDN->upload($src, $save_name);
        if ($ret) {
            if($dst_w == 0){
                //$upload->ratio = $dst_h/$dst_w;
                $upload->ratio = $size[1]/$size[0];
            }
            else if($dst_w != 0){
                $upload->ratio = $dst_h/$dst_w;
            }

            $upload->update_time = time();
            $upload->type        = 'qiniu';
            $upload->save();
            ajax_return(1, 'okay', array(
                'url'=>get_cloudcdn_url($ret),
                'id'=>$upload->id,
                'name'=>$upload->filename,
                'ratio'=>$upload->ratio
            ));
        } else {
            ajax_return(0, '文件上传到CDN出错');
        }
    }

    /**
     * 检测文件上传错误码
     */
    protected function _upload_error(){
        if(empty($_FILES)){
            return "请选择上传文件";
        }
        switch($_FILES['Filedata']['error']) {
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

    /**
     * 上传文件到七牛
     *
     * @return array
     */
    protected function _upload_cloudCDN()
    {
        $this->noview();
        // Check if the user has uploaded files
        if ($this->request->hasFiles() == true) {
            // Print the real file names and sizes
            foreach ($this->request->getUploadedFiles() as $file) {
                $config     = read_config("image");
                /*
                 * todo
                if(!DEV){
                    $allow_ext  = (array)$config->allow_ext;
                    $ext = strtolower( $file->getExtension() );
                    if(!in_array($ext, $allow_ext)){
                        return ajax_return(0, '上传失败，文件类型错误');
                    }
                }
                 */
                //Print file details
                //Move the file into the application
                $upload_dir = $config->upload_dir . date("Ym")."/";
                $save_name  = $this->cloudCDN->generate_filename_by_file($file->getName());
                $ret        = $this->cloudCDN->upload($file->getTempName(), $save_name);

                if ($ret) {
                    $this->debug_log->log("上传文件成功！{$ret}");

                    //保存本地
                    $size = $this->_save_file($file, $upload_dir, $save_name);
                    $upload = \Psgod\Models\Upload::newUpload($file->getName(), $save_name, $ret, $size);
                    if ($upload) {
                        $ret = get_cloudcdn_url($ret);  // 填补成完整链接

                        return array(1, 'okay', array(
                            'url'=>$ret,
                            'id'=>$upload->id,
                            'name'=>$file->getName(),
                            'ratio'=>$size['ratio']
                        ));
                    } else {
                        return array(0, '上传成功但保存失败', array('url'=>$ret));
                    }
                } else {
                    return array(0, '上传到七牛失败', array());
                }
            }
        } else {
            $this->debug_log->log('木有接收到文件。');
            return array(0, $this->_upload_error(), array());
        }
    }

    protected function _save_file($file, $upload_dir, $save_name){
        $width  = $this->get("width");

        //需要创建目录
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $size = getimagesize($file->getTempName());

        $arr = array();
        $arr['ratio']  = $this->post("ratio", "float", $size[1]/$size[0]);
        $arr['scale']  = $this->post("scale", "float", is_null($width)?1:$width/$size[0]);
        $arr['size']   = $size[1]*$size[0];

        move_uploaded_file($file->getTempName(), $upload_dir.$save_name);

        return $arr;
    }
}
