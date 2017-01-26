<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/common.css">
<style>
.picture-hot {
    height: 800px;
}
</style>
<div class="picture-hot">
<?php
    $width = 600;
    $height = 800;
    $ratio      = $model['ratio']; 
    $model_id   = $model['id'];
    $image_url  = get_cloudcdn_thumb_url($model['savename']);
    $labels_arr = $model['labels'];
    $height_ratio = $width*$ratio;

    if($height_ratio > $height){
        //上下留白
        $width = $height / $ratio;
    }
    else {
        //左右留白
        $height = $height_ratio;
    }

    $div_arr = array();
    $div_arr[] = '<div style="position: absolute;width:'.$width.'px;height:'.
        $height.'px;top:50%;left:50%;margin-left:-' . $width / 2 . 
        'px;margin-top:-' . $height / 2 . 'px;" >'.
        '<a href="#" data="'.$model_id.'">'.
        '<img src="'.$image_url.'" alt="">'.
        '</a>';
    foreach($labels_arr as $label){
        $x = $width * $label['x'];
        $y = $height * $label['y'];
        //$direction = $label['direaction'];
        $div = '<div class="label-re" style="left:'.$x.'px;top:'.$y.'px;">'.
                '<div class="triangle"></div><div class="breathe"></div>'.
                '<div class="label-result"></div>'.
                '<input readonly class="label-font" readonly value="'.$label['content'].'">'.
            '</div>';
        $div_arr[] = $div;
    }
    $div_arr []= '</div>';

    echo implode("", $div_arr);
?>
</div>
<script>
$(function(){
    $(".picture-hot").click(function(){
        return false;
    });
});
</script>

