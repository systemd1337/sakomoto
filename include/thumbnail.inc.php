<?php
//thumbnails
if(basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
        die("This file is not to be called directly.");

function thumb($path,$tim,$ext,$append) {
	$fname = $path.$tim.$append.$ext;
	$thumb_dir = THUMB_DIR; // Thumbnail directory
	$width = MAX_W; // Output width
	$height = MAX_H; // Output height

        if($ext==".webm"){
                if(!FFMPEG)return;
                $ret=1;
                $out=[];
                $webmname=$thumb_dir.$tim.".jpg";
                exec(FFMPEG." -y -strict -2 -ss 0 -i ".$fname." -v quiet -an -vframes 1 -f mjpeg ".$webmname,$out,$ret);
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
                $fname=$webmname;
        }
        //Then file is an image
	if (!function_exists("ImageCreate")||!function_exists("ImageCreateFromJPEG")) {
		return;
	}
	// width, height, and type are acquired
	$size = GetImageSize($fname);
	switch ($size[2]) {
	case 1:
		if (function_exists("ImageCreateFromGIF")) {
			$im_in = @ImageCreateFromGif ($fname);
			if ($im_in) {
				break;
			}
		}
		if (!file_exists($path.$tim.'.png')) {
			return;
		}
		$im_in = @ImageCreateFromPNG($path.$tim.'.png');
		unlink($path.$tim.'.png');
		if (!$im_in) {
			return;
		}
		break;
	case 2: $im_in = @ImageCreateFromJPEG($fname);
		if (!$im_in) {
			return;
		}
		break;
	case 3:
		if (!function_exists("ImageCreateFromPNG")) {
			return;
		}
		$im_in = @ImageCreateFromPNG($fname);
		if (!$im_in) {
			return;
		}
		break;
	default : return;
	}
	// Resizing
	if ($size[0] > $width || $size[1] > $height) {
		$key_w = $width / $size[0];
		$key_h = $height / $size[1];
		($key_w < $key_h) ? $keys = $key_w : $keys = $key_h;
		$out_w = ceil($size[0] * $keys) +1;
		$out_h = ceil($size[1] * $keys) +1;
	} else {
		$out_w = $size[0];
		$out_h = $size[1];
	}
	if($size[0]>100||$size[1]>100) {
		$key_w = 100 / $size[0];
		$key_h = 100 / $size[1];
		($key_w < $key_h) ? $keys = $key_w : $keys = $key_h;
		$c_out_w = ceil($size[0] * $keys) +1;
		$c_out_h = ceil($size[1] * $keys) +1;
	}else{
                $c_out_w=$size[0];
                $c_out_h=$size[1];
        }
	// the thumbnail is created
	if (function_exists("ImageCreateTrueColor")&&get_gd_ver()=="2") {
		$im_out = ImageCreateTrueColor($out_w, $out_h);
		$c_im_out = ImageCreateTrueColor($c_out_w, $c_out_h);
	} else {
                $im_out = ImageCreate($out_w, $out_h);
                $c_im_out = ImageCreate($c_out_w, $c_out_h);
        }
	// change background color
	$backing = imagecolorallocate($im_out,...THUMBBACK);
	$c_backing = imagecolorallocate($c_im_out,...THUMBBACK);
	imagefill($im_out, 0, 0, $backing);
	imagefill($c_im_out, 0, 0, $c_backing);
	// copy resized original
	ImageCopyResized($im_out, $im_in, 0, 0, 0, 0, $out_w, $out_h, $size[0], $size[1]);
	ImageCopyResized($c_im_out, $im_in, 0, 0, 0, 0, $c_out_w, $c_out_h, $size[0], $size[1]);
	// thumbnail saved
	ImageJPEG($im_out, $thumb_dir.$tim.$append.'s.jpg',60);
	chmod($thumb_dir.$tim.$append.'s.jpg',0666);
	ImageJPEG($c_im_out, $thumb_dir.$tim.$append.'c.jpg',60);
	chmod($thumb_dir.$tim.$append.'c.jpg',0666);
	// created image is destroyed
	ImageDestroy($im_in);
	ImageDestroy($im_out);
	ImageDestroy($c_im_out);
}
