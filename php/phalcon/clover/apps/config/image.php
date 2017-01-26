<?php
//需要软连到大硬盘
//需要777权限
return array(
    'public_dir'    => __ROOT__ . 'public' . __DS__,
    'upload_dir'    => __ROOT__ . 'public/images' . __DS__,
    'preview_dir'   => '/images/',
    'allow_ext'     => array(
        'png',
        'jpg',
        'jpeg',
        'gif',
        'ico',
        'x-png',
        'bmp',
        'pjpeg'
    )
);
