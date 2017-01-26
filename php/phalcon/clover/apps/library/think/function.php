<?php


function U($url='',$arr=array()) {
	unset($arr['_url']);

	$args = array();
    foreach ($arr as $k => $v) {
        $args[] = $k."=".$v;
    }
	$url .= '?'.implode('&', $args);
    return $url;
}
