<?php
//Small script to return a random image from current folder

error_reporting(E_ALL);

$mime="null/null";
while(explode("/",$mime)[0]!="image"){
        $banners=array_diff(scandir('.'),array('.','..'));
        $selected=$banners[array_rand($banners)];
        if(is_dir($selected))continue;
        $mime=mime_content_type($selected);
}

if(error_get_last())exit;

header("content-type:".$mime);
header("content-disposition:inline;filename=".$selected);
header("expires:0");
header("last-modified:".gmdate("D, d M Y H:i:s")." GMT");
header("cache-control:no-store, no-cache, must-revalidate");
header("pragma:no-cache");

readfile($selected);
