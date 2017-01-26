<?php
namespace Psgod\Main\Controllers;

use Psgod\Models\Upload;

class ImageController extends ControllerBase
{
    use \Psgod\Traits\ImageUpload;   // 混入文件上传 trait    
}
