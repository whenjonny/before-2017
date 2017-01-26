<?php 
/**
 * 一、头像裁剪：Detect API
 * 1、识别出头像占图片的比例，小于一定百分比的直接舍弃
 * 2、识别出两个头像的距离，取最小的
 * 3、以两个头像的中心为原点，N倍于人脸的宽度为半径，切出一个正方形后期可能要用到Face Analyze API分析人脸姿势
 */ 

// 获取图片
$api_key = 'U_ZC7-jw5BlIa-dZtdAOeXiLUD1muA2L';
$api_secret = 'zdTCNqLrZyNG7ggaqOK04wTCa4JaoAyQ';
$detect_url = 'https://api-cn.faceplusplus.com/facepp/v3/detect';

$data = array(
    'image_file' => '@'.$imgName,
    'api_key' => $api_key,
    'api_secret' => $api_secret
);


$faces = array();
$directory = "src";

$rate = 0.5;
$scale = 1.5;


$filenames = scandir("./$directory");
echo "开始裁剪 \n";

foreach($filenames as $file) {
    $arr = explode(".", $file);
    $ext = strtolower($arr[1]);
    if(sizeof($arr) != 2 || ($ext != 'jpeg' && $ext != 'jpg')) 
        continue;

    $filename = $file;
    $file = "$directory/$file";

    $data['image_file'] = "@$file";

    // 1. 获取头像数据
    while(true) {
        $result = post($detect_url, $data);
        //print_r($data);
        if(!$result) {
            continue;
        }
        //print_r($result);

        if(!isset($result->error_message)) {
            break;
        }
        echo $result->error_message. "\n";
        sleep(1);
    }
    //print_r($result);
    // 2. 必须有两张人脸
    if(sizeof($result->faces) != 2)  {
        echo "一张人脸: $file \n";
        continue;
    }
    // 3. 计算人脸距离
    $face1 = $result->faces[0]->face_rectangle;
    $face2 = $result->faces[1]->face_rectangle;
    $face_data = face($face1, $face2);

    if($face1->left > $face2->left) {
        $distance = ($face1->left - $face2->left - $face2->width);
    }
    else {
        $distance = ($face2->left - $face1->left - $face1->width);
    }

    $distance = sqrt(($face1->left-$face2->left)*($face1->left-$face2->left) + ($face1->top-$face2->top)*($face1->top-$face2->top));

    $img = getimagesize($file);
    $faces[$distance] = array_merge($face_data, array(
        'rate' => ($face_data['size']*$face_data['size'])/($img[0]*$img[1]),
        'path' => $file,
        'name' => $filename,
        'x' => $face_data['x'],
        'y' => $face_data['y'],
        'size' => $face_data['size']
    ));
}

$r = current($faces);
foreach($faces as $face) {
    if($face['rate'] > $limit_rate) {
        continue;
    }
    
    $r = $face;
}

$r['size'] = $r['size'] * $scale;
$r['x'] = $r['x'] - $r['size']/2;
$r['y'] = $r['y'] - $r['size']/2;

if(crop("./src/".$r['name'], "./dist/".$r['name'], $r)) {
    echo "裁剪成功\n";
} else {
    echo "裁剪失败\n";
}


// 裁剪函数
function crop($fromImg, $toImg, $data) {
    $x = $data['x'];
    $y = $data['y'];
    $width  = $data['size'];
    $height = $data['size'];

    $im = imagecreatefromjpeg($fromImg);

    $size = min(imagesx($im), imagesy($im));
    $im2 = imagecrop($im, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
    if ($im2 !== FALSE) {
        imagejpeg($im2, $toImg);
        return true;
    }
    //print_r(['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);
    return false;
}

// 提交获取头像矩阵
function post($url, $post_array) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array); 
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response);
}

function face($face1, $face2) { 
    $x = [ $face1->left, $face2->left, $face1->left + $face1->width, $face2->left + $face2->width ];
    $y = [ $face1->top, $face2->top, $face1->top + $face1->height, $face2->top + $face2->height ];
    sort($x);
    sort($y);

    $x1 = $x[0]; //最小点
    $y1 = $y[0];
    $x2 = $x[3]; //最大点
    $y2 = $y[3];

    // 4. 获取裁剪正方形的大小
    $width  = $x2 - $x1;
    $height = $y2 - $y1;
    $size = ($width > $height)? $width: $height;

    // 5. 计算矩形中点
    $center_x = ($x2+$x1)/2;
    $center_y = ($y2+$y1)/2;

    return ['x'=>$center_x, 'y'=>$center_y, 'size'=>$size];
}
