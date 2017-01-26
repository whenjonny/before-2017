<?php
namespace Psgod\Android\Controllers;

use Psgod\Models\Upload;

class ImageController extends ControllerBase
{

    public $_allow = array(
        'upload'
    );

    public function downloadAction() {
        $upload_id  = $this->get("upload_id", "int", 1);
        $width      = $this->get("width", "int", 320);

        if (!$upload_id) {
            return ajax_return (0, "请选择需要下载的照片");
        }

        $upload = Upload::findFirst($upload_id);
        if (!$upload) {
            return ajax_return (0, "请选择需要下载的照片");
        }
        //todo: 记录用户下载图片的数据
        $data = array();
        $data['url']    = get_cloudcdn_url($upload->savename);
        $data['ratio']  = $upload->ratio;
        $data['width']  = intval($width);
        $data['height'] = intval($width*$data['ratio']);

        ajax_return(1, 'okay', $data);
    }

    use \Psgod\Traits\ImageUpload;   // 混入文件上传 trait
}
