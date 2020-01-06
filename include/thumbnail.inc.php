<?php
//thumbnails
if(basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
        die("This file is not to be called directly.");

function fastimagecopyresampled(&$dst_image, &$src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
	// Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable.
	if (empty($src_image) || empty($dst_image)) {
		return false;
	}

	if ($quality <= 1) {
		$temp = imagecreatetruecolor($dst_w + 1, $dst_h + 1);

		imagecopyresized($temp, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w + 1, $dst_h + 1, $src_w, $src_h);
		imagecopyresized($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
		imagedestroy($temp);
	} elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
		$tmp_w = $dst_w * $quality;
		$tmp_h = $dst_h * $quality;
		$temp = imagecreatetruecolor($tmp_w + 1, $tmp_h + 1);

		imagecopyresized($temp, $src_image, $dst_x * $quality, $dst_y * $quality, $src_x, $src_y, $tmp_w + 1, $tmp_h + 1, $src_w, $src_h);
		imagecopyresampled($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
		imagedestroy($temp);
	} else {
		imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}

	return true;
}

function gd_thumb($m_w,$m_h,$im_in){
        $in_w=imageSX($im_in);
        $in_h=imageSY($im_in);

	if($in_w>$m_w||$in_h>$m_h) {
		$key_w = $m_w / $in_w;
		$key_h = $m_h / $in_h;
		($key_w < $key_h) ? $keys = $key_w : $keys = $key_h;
		$width = ceil($in_w * $keys) +1;
		$height = ceil($in_h * $keys) +1;
	}else{
                $width=$in_w;
                $height=$in_h;
        }

        $im_out=imagecreatetruecolor($width,$height);
        fastimagecopyresampled($im_out,$im_in,0,0,0,0,$width,$height,$in_w,$in_h);
        return $im_out;
}

function thumb($path,$tim,$ext,$append) {
	$fname = $path.$tim.$append.$ext;
        $im_in=false;
        if(!file_exists($fname)) return false;
        $ret=0;
        switch(strtolower($ext)){
                case ".webm":
                        if(!FFMPEG) return false;
                        $ret=1;
                        $out=[];
                        $webmname=THUMB_DIR.$tim.".jpg";
                        exec(FFMPEG." -y -strict -2 -ss 0 -i ".$fname." -v quiet -an -vframes 1 -f mjpeg ".$webmname,$out,$ret);
                        $checkret=true;
                        $fname=$webmname;$ext=".jpg";
                        break;
                case ".mp3":
                        if(!FFMPEG) return false;
                        $ret=1;
                        $out=[];
                        $mp3name=THUMB_DIR.$tim.".jpg";
                        exec(FFMPEG." -y -strict -2 -ss 0 -i ".$fname." -v quiet -an -filter_complex \"showwavespic=colors=#00FF00|black\" -vframes 1 ".$mp3name,$out,$ret);
                        $checkret=true;
                        $fname=$mp3name;$ext=".jpg";
                        break;
                case ".txt":
                        if(!FFMPEG) return false;
                        $ret=1;
                        $out=[];
                        $txtname=THUMB_DIR.$tim.".jpg";
                        exec(FFMPEG." -y -strict -2 -i ".$fname." -v quiet -vframes 1 ".$txtname,$out,$ret);
                        $checkret=true;
                        $fname=$txtname;$ext=".jpg";
                        break;
                default:
                        break;
        }
        switch($ret){
                case 0://Success
                        break;
                case 127://Command not found
                        error(lang("Error: ffmpeg is not installed."));
                        break;
                case 1:
                default:
                        error(lang("Error: Unkown ffmpeg error."));
                        break;
        }
        switch(strtolower($ext)){
                case ".jpg":
                case ".jpeg":
                case ".jfif":
                        if(function_exists("imagecreatefromjpeg")) $im_in=imagecreatefromjpeg($fname);
                        break;
                case ".gif":
                case ".giff":
                        if(function_exists("imagecreatefromgif")) $im_in=imagecreatefromgif($fname);
                        break;
                case ".png":
                        if(function_exists("imagecreatefrompng")) $im_in=imagecreatefrompng($fname);
                        break;
                case ".bmp":
                        if(function_exists("imagecreatefrombmp")) $im_in=imagecreatefrombmp($fname);
                        break;
                case ".wbmp":
                        if(function_exists("imagecreatefromwbmp")) $im_in=imagecreatefromwbmp($fname);
                        break;
                case ".webp":
                        if(function_exists("imagecreatefromwebp")) $im_in=imagecreatefromwebp($fname);
                        break;
                case ".xbm":
                        if(function_exists("imagecreatefromxbm")) $im_in=imagecreatefromxbm($fname);
                        break;
                case ".xpm":
                        if(function_exists("imagecreatefromxpm")) $im_in=imagecreatefromxpm($fname);
                        break;
                default:
                        return false; //Not supported
                        break;
        }
        if(!$im_in) return false;
        
	// Resizing
        $res_thumb=gd_thumb(MAX_W,MAX_H,$im_in);
        $cat_thumb=gd_thumb(100,100,$im_in);
        if(!($res_thumb||$cat_thumb)) return false;
        
	imagejpeg($res_thumb,THUMB_DIR.$tim.$append."s.jpg",60);
	chmod(THUMB_DIR.$tim.$append."s.jpg",0666);

	imagejpeg($cat_thumb,THUMB_DIR.$tim.$append."c.jpg",60);
	chmod(THUMB_DIR.$tim.$append."c.jpg",0666);
	// Created image is destroyed
	imagedestroy($im_in);
	imagedestroy($res_thumb);
	imagedestroy($cat_thumb);
        return true;
}
