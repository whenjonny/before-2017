<?php
namespace Psgod\Models;

class Upload extends ModelBase
{
    public function getSource()
    {
        return 'uploads';
    }

    public static function newUpload($filename, $savename, $url, $size = array(), $type = 'qiniu')
    {
        $upload = new self();
        $upload->filename = $filename;
        $upload->savename = $savename;
        $upload->pathname = $url;
        $array = explode('.', $filename);
        $upload->ext      = end($array);
        //todo:
        $upload->uid      = 0;
        $upload->ip       = get_client_ip();
        $upload->type     = $type;
        $upload->ratio    = isset($size['ratio'])?$size['ratio']: 0.75;
        $upload->scale    = isset($size['scale'])?$size['scale']: 1;
        $upload->size     = isset($size['size'])?$size['size']: 0;

        $upload->create_time = time();
        $upload->update_time = time();

        return $upload->save_and_return($upload);
    }

    public function resize($width = 480){

        return self::upload_resize($this->ratio, $this->scale, $this->savename, $width);
    }

    public static function upload_resize($ratio, $scale, $savename, $width) {
        if(!isset($scale) || $scale == 0){
            $scale = 1;
        }
        if(!isset($ratio) || $ratio == 0){
            $ratio = 1.333;
        }
        if(!isset($savename) || $savename == ''){
            $savename = '';
        }
        $width = $width*$scale;

        $temp = array();
        $temp['image_width']    = $width;
        $temp['image_height']   = intval($width*$ratio);
        $temp['image_url']      = get_cloudcdn_url($savename, $width);

        return $temp;
    }
}
