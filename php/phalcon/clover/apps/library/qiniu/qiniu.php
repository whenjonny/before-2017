<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "io.php";
require_once __DIR__ . DIRECTORY_SEPARATOR . "rs.php";

/**
 * 七牛云存储接口
 *
 * 妈蛋从来还没有看见过这么狗血的第三方接口，木有OO，include 的文件还因功能而异。
 * 于是自己封闭一个。
 *
 * @author gatesanye <me@gatesanye.com>
 * 
 */
class CloudCDN
{
    private $access_key;

    private $secret_key;

    public $domain;

    public $bucket;

    public function __construct($ak, $sk, $bucket='', $domain='') 
    {
        $this->access_key = $ak;
        $this->secret_key = $sk;
        $this->bucket     = $bucket;
        $this->domain     = $domain;

        Qiniu_SetKeys($this->access_key, $this->secret_key);
    }

    /**
     * 上传文件到七牛服务器
     * 
     * @param  string $localfile 本地文件完整路径
     * @param  string $savename  保留的文件名
     * @return false | 文件URL
     */
    public function upload($localfile, $savename='')
    {
        if (file_exists($localfile)) {
            $savename = !empty($savename) ? $savename : $this->generate_filename_by_file($localfile);

            $putPolicy = new Qiniu_RS_PutPolicy($this->bucket);
            $upToken = $putPolicy->Token(null);
            $putExtra = new Qiniu_PutExtra();
            $putExtra->Crc32 = 1;
            list($ret, $err) = Qiniu_PutFile($upToken, $savename, $localfile, $putExtra);

            if ($ret) {
                return $savename;
                //return "http://{$this->domain}/{$savename}";
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 根据文件生成一个带同后缀的随机文件名
     * 
     * @param  string $file 文件名
     * @return string
     */
    public function generate_filename_by_file($filename)
    {
        $ext = $this->get_ext($filename);
/*
        $tmp = explode('.', basename($filename));
        if(sizeof($tmp) <= 1){
            $ext = "jpg";
        }
        else {
            $ext = end($tmp);
        }
 */

        return $this->gen_name() . '.' . $ext;
    }

    /**
     * 根据后缀名生成随机文件名
     * 
     * @param  string $ext 后缀名
     * @return string
     */
    public function generate_filename_by_ext($ext)
    {
        $tmp = explode('.', $ext);
        $real_ext = end($tmp);

        return $this->gen_name() . '.' . $real_ext;
    }

    /**
     * 生成随机名
     * @param  string $prefix 前缀
     * @return string
     */
    private function gen_name($prefix='')
    {
        return date('Ymd-His') . uniqid($prefix);
    }

    /**
     * 获取后缀
     * @param string $filename
     * @return ext
     */
    private function get_ext($file_name) {
        $tmp = explode(".", $file_name);
        if(sizeof($tmp) <= 1){
            $ext = "jpg";
        }
        else {
            $ext = end($tmp);
        }
        return $ext;
    }

    /**
     * 根据文件名生成文件URL
     * 
     * @param  string  $filename 文件名
     * @param  integer $width    宽度
     * @return string
     */
    public static function file_url($filename, $width=null)
    {
        $qiniu_di = get_di('cloudCDN');
        if(strpos($filename, $qiniu_di->domain) !==false){
            //兼容旧的，存了完整路径的
            $url = $filename;
        }
        else {
            $url = sprintf('http://%s/%s', $qiniu_di->domain, $filename);
        }

        if ($width!==null && strpos($url, 'imageView') == false) {
            $url .= "?imageView2/2/w/{$width}";
        }

        return $url;
    }
}
